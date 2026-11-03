<?php
// app/Http/Controllers/Public/OfficialLinkDirectoryController.php

namespace App\Http\Controllers\Public;

use App\Enums\InstitutionType;
use App\Enums\LinkStatus;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficialLinkDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $type = InstitutionType::tryFrom((string) $request->query('type'));

        // Aturan terpenting modul ini: hanya tautan berstatus "verified" yang keluar dari sini.
        $verifiedOnly = fn ($q) => $q->where('status', LinkStatus::Verified);

        $institutions = Institution::query()
            ->whereHas('links', $verifiedOnly)
            ->with([
                'country',
                'links' => fn ($q) => $verifiedOnly($q)->orderBy('label'),
            ])
            ->when($type, fn (Builder $q, InstitutionType $type) => $q->where('type', $type))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn (Builder $q) => $q->where('name', 'like', $term)->orWhere('city', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $typeCounts = Institution::query()
            ->whereHas('links', $verifiedOnly)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('links.index', [
            'institutions' => $institutions,
            'types' => InstitutionType::cases(),
            'currentType' => $type,
            'typeCounts' => $typeCounts,
        ]);
    }
}
