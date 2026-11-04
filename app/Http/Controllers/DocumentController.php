<?php
// app/Http/Controllers/DocumentController.php

namespace App\Http\Controllers;

use App\Enums\DocumentCategory;
use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// "Berkas Saya" untuk santri + unduh berkas (dipakai santri, admin, dan pembimbing sesuai DocumentPolicy).
class DocumentController extends Controller
{
    /** Jenis file yang diterima (diperiksa dari isi file, bukan hanya nama). */
    public const MIMES = 'pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx';

    public const MAX_KB = 5120;

    public const MAX_FILES = 40;

    public function index(Request $request): View
    {
        $documents = $request->user()->documents()->with(['uploader', 'reviewer'])->latest()->get();

        return view('documents.index', [
            'documents' => $documents->groupBy(fn (Document $d) => $d->category->value),
            'total' => $documents->count(),
            'categories' => DocumentCategory::cases(),
            'needsFix' => $documents->where('status', DocumentStatus::NeedsFix)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->documents()->count() >= self::MAX_FILES) {
            return back()->with('warning', 'Batas '.self::MAX_FILES.' berkas tercapai. Hapus berkas lama yang sudah tidak diperlukan terlebih dahulu.');
        }

        $data = $request->validate(self::rules());
        $document = self::storeFile($request->file('file'), $user, $user, $data);

        Notification::send(
            User::permission('documents.manage')->where('is_active', true)->get(),
            new InAppNotification(title: 'Berkas baru dari '.$user->name, body: $document->category->label().': '.$document->title, url: route('admin.documents.index', ['status' => 'pending']), icon: 'paperclip'),
        );

        return back()->with('success', 'Berkas “'.$document->title.'” tersimpan. Admin pesantren akan memeriksanya.');
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);
        abort_unless(Storage::disk(Document::DISK)->exists($document->path), 404);

        // Gambar & PDF dibuka di tab browser; jenis lain diunduh.
        $inline = $request->boolean('lihat') && ($document->isImage() || $document->isPdf());

        return Storage::disk(Document::DISK)->{$inline ? 'response' : 'download'}($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('delete', $document);
        $document->delete();

        return back()->with('success', 'Berkas dihapus.');
    }

    /** Aturan validasi unggahan (dipakai juga oleh admin). */
    public static function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:'.self::MIMES, 'max:'.self::MAX_KB],
            'category' => ['required', Rule::enum(DocumentCategory::class)],
            'title' => ['required', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public static function storeFile(UploadedFile $file, User $owner, User $uploader, array $data): Document
    {
        // Nama file di disk acak (bukan nama asli) agar tidak bisa ditebak dan aman dari karakter aneh.
        $path = $file->store('documents/'.$owner->id, Document::DISK);

        return $owner->documents()->create([
            'uploaded_by' => $uploader->id,
            'category' => $data['category'],
            'title' => $data['title'],
            'note' => $data['note'] ?? null,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'status' => DocumentStatus::Pending,
        ]);
    }
}
