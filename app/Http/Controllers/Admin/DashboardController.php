<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Enums\LinkCheckResult;
use App\Enums\LinkStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\ConsultationReport;
use App\Models\Institution;
use App\Models\LinkCheckLog;
use App\Models\OfficialLink;
use App\Models\ProgramInterest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Ringkasan admin (desain "04 Admin · Ringkasan"): angka utama, daftar tugas hari ini, hasil pengecekan otomatis.
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $linkCount = fn (LinkStatus $s) => OfficialLink::where('status', $s)->count();
        $withoutConsent = User::role(Role::Santri->value)->where('is_active', true)
            ->whereDoesntHave('guardianConsents', fn ($q) => $q->whereNull('revoked_at'))->count();

        $stats = [
            'verified' => $linkCount(LinkStatus::Verified),
            'pending' => $linkCount(LinkStatus::Pending),
            'needs_review' => $linkCount(LinkStatus::NeedsReview),
            'without_consent' => $withoutConsent,
        ];

        // Hanya tugas yang ada isinya dan boleh dikerjakan peran ini yang ditampilkan.
        $tasks = collect([
            ['links.verify', $stats['pending'], "{$stats['pending']} tautan menunggu verifikasi", 'Cocokkan dengan sumber resmi (PDDikti, situs kementerian) sebelum ditampilkan ke santri.', 'Verifikasi', route('admin.links.index', ['status' => 'pending']), 'link'],
            ['links.manage', $stats['needs_review'], "{$stats['needs_review']} tautan perlu ditinjau ulang", 'Domainnya berubah atau tidak ditemukan saat pengecekan otomatis. Santri tidak melihatnya sampai ditinjau.', 'Tinjau', route('admin.links.index', ['status' => 'needs_review']), 'triangle-alert'],
            ['consents.manage', $withoutConsent, "{$withoutConsent} santri belum ada izin orang tua", 'Konseling dan Asisten AI masih terkunci untuk mereka.', 'Catat', route('admin.consents.index', ['status' => 'belum']), 'shield'],
            ['links.manage', $noLinks = Institution::whereDoesntHave('links')->count(), "{$noLinks} institusi tanpa tautan", 'Tambahkan minimal satu tautan situs utama agar tampil di direktori.', 'Tambah', route('admin.links.create'), 'building-2'],
            ['alumni.verify', $alumni = AlumniProfile::where('verification_status', 'pending')->count(), "{$alumni} alumni menunggu verifikasi", 'Cocokkan nama dan tahun lulus dengan arsip pesantren.', 'Periksa', route('admin.alumni.index'), 'user-check'],
            ['consultations.moderate', $reports = ConsultationReport::where('status', 'open')->count(), "{$reports} laporan konsultasi terbuka", 'Tinjau percakapan yang dilaporkan santri atau alumni.', 'Tinjau', route('admin.consultations.index'), 'flag'],
            ['programs.view-interests', $interests = ProgramInterest::whereNull('followed_up_at')->count(), "{$interests} peminat program belum dihubungi", 'Tindak lanjuti santri yang menyatakan berminat pada program luar negeri.', 'Lihat', route('admin.programs.index'), 'briefcase-business'],
        ])->filter(fn ($t) => $t[1] > 0 && $user->checkPermissionTo($t[0]))->values();

        $checks = LinkCheckLog::where('checked_at', '>=', now()->subDay())->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'tasks' => $tasks,
            'checks' => [
                'total' => $checks->count(),
                'last' => $checks->max('checked_at'),
                'by' => collect(LinkCheckResult::cases())->mapWithKeys(fn ($r) => [$r->label() => $checks->where('result', $r)->count()]),
            ],
            'recentLinks' => OfficialLink::with(['institution', 'verifier'])->latest('updated_at')->limit(5)->get(),
        ]);
    }
}
