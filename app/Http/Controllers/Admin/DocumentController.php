<?php
// app/Http/Controllers/Admin/DocumentController.php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentCategory;
use App\Enums\DocumentStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DocumentController as SharedDocumentController;
use App\Models\Document;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Berkas santri dari sisi admin: lihat semua, unggahkan untuk santri, dan tandai sudah diperiksa / perlu diperbaiki.
class DocumentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:documents.manage')];
    }

    public function index(Request $request): View
    {
        $status = DocumentStatus::tryFrom((string) $request->query('status'));

        return view('admin.documents.index', [
            'documents' => Document::query()
                ->with(['owner.santriProfile', 'uploader'])
                ->when($status, fn (Builder $q, DocumentStatus $s) => $q->where('status', $s))
                ->when(DocumentCategory::tryFrom((string) $request->query('kategori')), fn (Builder $q, DocumentCategory $c) => $q->where('category', $c))
                ->when($request->integer('santri'), fn (Builder $q, int $id) => $q->where('owner_id', $id))
                ->when($request->filled('q'), function (Builder $q) use ($request) {
                    $term = '%'.$request->string('q')->trim().'%';
                    $q->where(fn (Builder $q) => $q->where('title', 'like', $term)
                        ->orWhereHas('owner', fn (Builder $q) => $q->where('name', 'like', $term)->orWhere('username', 'like', $term)));
                })
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'currentStatus' => $status,
            'counts' => Document::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'categories' => DocumentCategory::cases(),
            'santri' => User::role(Role::Santri->value)->where('is_active', true)->with('santriProfile')->orderBy('name')->get(['id', 'name', 'username']),
        ]);
    }

    // Admin mengunggahkan berkas untuk santri (mis. surat keterangan dari pesantren).
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            ...SharedDocumentController::rules(),
            'owner_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);
        $owner = User::findOrFail($data['owner_id']);
        abort_unless($owner->hasRole(Role::Santri->value), 422);

        $document = SharedDocumentController::storeFile($request->file('file'), $owner, $request->user(), $data);
        $document->update(['status' => DocumentStatus::Reviewed, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        $owner->notify(new InAppNotification(
            title: 'Berkas baru dari pesantren',
            body: $document->title,
            url: route('documents.index'),
            icon: 'paperclip',
        ));

        return back()->with('success', 'Berkas diunggah untuk '.$owner->name.'.');
    }

    public function review(Request $request, Document $document): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([DocumentStatus::Reviewed->value, DocumentStatus::NeedsFix->value])],
            'review_note' => ['nullable', 'required_if:status,needs_fix', 'string', 'max:500'],
        ], ['review_note.required_if' => 'Tuliskan apa yang perlu diperbaiki agar santri tahu.']);

        $document->update([...$data, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        $document->owner->notify(new InAppNotification(
            title: $document->status === DocumentStatus::Reviewed ? 'Berkas sudah diperiksa' : 'Berkas perlu diperbaiki',
            body: $document->title.($document->review_note ? ': '.$document->review_note : ''),
            url: route('documents.index'),
            icon: 'paperclip',
            tone: $document->status === DocumentStatus::NeedsFix ? 'danger' : 'blue',
        ));

        return back()->with('success', 'Status berkas diperbarui.');
    }
}
