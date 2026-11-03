<?php
// app/Http/Controllers/Admin/AuditLogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

// Log audit (Super Admin): metadata akses saja, tanpa isi catatan konseling.
class AuditLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:audit-log.view')];
    }

    public function index(Request $request): View
    {
        return view('admin.audit.index', [
            'logs' => AuditLog::with(['user', 'santri'])
                ->when($request->filled('aksi'), fn (Builder $q) => $q->where('action', $request->string('aksi')))
                ->when($request->filled('q'), function (Builder $q) use ($request) {
                    $term = '%'.$request->string('q')->trim().'%';
                    $q->where(fn (Builder $q) => $q->whereHas('user', fn ($q) => $q->where('name', 'like', $term))->orWhereHas('santri', fn ($q) => $q->where('name', 'like', $term)));
                })
                ->latest('created_at')
                ->paginate(30)
                ->withQueryString(),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
