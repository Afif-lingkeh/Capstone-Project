<?php
// app/Http/Controllers/Admin/OfficialLinkController.php

namespace App\Http\Controllers\Admin;

use App\Enums\LinkCheckResult;
use App\Enums\LinkStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfficialLinkRequest;
use App\Http\Requests\Admin\RejectLinkRequest;
use App\Http\Requests\Admin\VerifyLinkRequest;
use App\Models\Institution;
use App\Models\OfficialLink;
use App\Services\LinkChecker\LinkCheckerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OfficialLinkController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', OfficialLink::class);

        $status = LinkStatus::tryFrom((string) $request->query('status'));

        $links = OfficialLink::query()
            ->with('institution.country')
            ->when($status, fn (Builder $q, LinkStatus $status) => $q->where('status', $status))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn (Builder $q) => $q
                    ->where('label', 'like', $term)
                    ->orWhere('url', 'like', $term)
                    ->orWhereHas('institution', fn (Builder $q) => $q->where('name', 'like', $term)));
            })
            // Yang butuh tindakan admin tampil paling atas.
            ->orderByRaw("case status when 'needs_review' then 0 when 'pending' then 1 when 'verified' then 2 else 3 end")
            ->orderBy('label')
            ->paginate(20)
            ->withQueryString();

        $statusCounts = OfficialLink::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.links.index', [
            'links' => $links,
            'statuses' => LinkStatus::cases(),
            'currentStatus' => $status,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', OfficialLink::class);

        $link = new OfficialLink(['institution_id' => $request->integer('institution') ?: null]);

        return view('admin.links.create', $this->formData($link));
    }

    public function store(OfficialLinkRequest $request): RedirectResponse
    {
        $link = new OfficialLink($request->validated());
        $link->forceFill([
            'status' => LinkStatus::Pending,
            'created_by' => $request->user()->id,
        ])->save();

        return redirect()->route('admin.links.edit', $link)
            ->with('success', 'Tautan disimpan dengan status "Menunggu Verifikasi". Tautan belum tampil ke santri sampai diverifikasi.');
    }

    public function edit(OfficialLink $link): View
    {
        Gate::authorize('update', $link);

        $link->load([
            'institution.country',
            'verifier',
            'checkLogs' => fn ($q) => $q->latest('checked_at')->limit(10),
        ]);

        return view('admin.links.edit', $this->formData($link));
    }

    public function update(OfficialLinkRequest $request, OfficialLink $link): RedirectResponse
    {
        $wasVerified = $link->status === LinkStatus::Verified;

        // Jika URL berubah, model otomatis mengembalikan status ke "Menunggu Verifikasi".
        $link->update($request->validated());

        $message = $wasVerified && $link->status === LinkStatus::Pending
            ? 'URL berubah, sehingga verifikasi sebelumnya dibatalkan. Silakan verifikasi ulang.'
            : 'Tautan diperbarui.';

        return redirect()->route('admin.links.edit', $link)->with('success', $message);
    }

    public function destroy(OfficialLink $link): RedirectResponse
    {
        Gate::authorize('delete', $link);

        $link->delete();

        return redirect()->route('admin.links.index')->with('success', "Tautan {$link->url} dihapus.");
    }

    public function verify(VerifyLinkRequest $request, OfficialLink $link): RedirectResponse
    {
        $link->forceFill([
            'status' => LinkStatus::Verified,
            'verification_source' => $request->validated('verification_source'),
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'review_note' => null,
        ])->save();

        return redirect()->route('admin.links.edit', $link)
            ->with('success', 'Tautan terverifikasi dan sekarang tampil di direktori santri.');
    }

    public function reject(RejectLinkRequest $request, OfficialLink $link): RedirectResponse
    {
        $link->forceFill([
            'status' => LinkStatus::Rejected,
            'review_note' => $request->validated('review_note'),
            'verification_source' => null,
            'verified_by' => null,
            'verified_at' => null,
        ])->save();

        return redirect()->route('admin.links.edit', $link)->with('success', 'Tautan ditolak dan tidak ditampilkan ke santri.');
    }

    public function check(OfficialLink $link, LinkCheckerService $checker): RedirectResponse
    {
        Gate::authorize('check', $link);

        $log = $checker->check($link);

        $message = "Hasil pengecekan: {$log->result->label()}"
            .($log->http_status ? " (HTTP {$log->http_status})" : '')
            .'.';

        if ($log->result === LinkCheckResult::DomainChanged) {
            $message .= " Situs dialihkan ke {$log->final_url}.";
        }

        if ($link->status === LinkStatus::NeedsReview) {
            $message .= ' Tautan ditandai "Perlu Ditinjau Ulang" dan disembunyikan dari santri.';
        }

        return redirect()->route('admin.links.edit', $link)->with('success', $message);
    }

    private function formData(OfficialLink $link): array
    {
        return [
            'link' => $link,
            'institutions' => Institution::with('country')->orderBy('name')->get(),
            'labelSuggestions' => ['Situs Utama', 'Penerimaan Mahasiswa Baru', 'Beasiswa', 'Program Internasional', 'Pendaftaran Sekolah Kedinasan'],
        ];
    }
}
