<?php
// app/Http/Controllers/Santri/DashboardController.php

namespace App\Http\Controllers\Santri;

use App\Enums\LinkStatus;
use App\Enums\ProgramStatus;
use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\Article;
use App\Models\GuidanceSlot;
use App\Models\OfficialLink;
use App\Models\PotentialTest;
use App\Models\Program;
use App\Services\PathwayScorer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PathwayScorer $scorer): View
    {
        $user = $request->user();
        $inProgress = $user->testAttempts()->whereNull('submitted_at')->withCount('answers')->with(['test' => fn ($q) => $q->withCount('questions')])->latest('updated_at')->first();
        $nextTest = $inProgress ? null : PotentialTest::active()->whereDoesntHave('attempts', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('submitted_at'))->first();

        return view('santri.dashboard', [
            'profile' => $user->santriProfile,
            'hasConsent' => $user->hasGuardianConsent(),
            'inProgress' => $inProgress,
            'nextTest' => $nextTest,
            'pathwayProfile' => $scorer->profileFor($user),
            'bookings' => $user->guidanceBookings()->with('slot.advisor')->latest()->limit(6)->get()->sortByDesc(fn ($b) => $b->isUpcoming())->take(2),
            'freeSlots' => GuidanceSlot::available()->whereBetween('starts_at', [now(), now()->endOfWeek()])->count(),
            'newLinks' => OfficialLink::where('status', LinkStatus::Verified)->with('institution')->latest('verified_at')->limit(3)->get(),
            'counts' => [
                'programs' => Program::published()->where('status', ProgramStatus::Open)->count(),
                'latestArticle' => Article::published()->latest('published_at')->value('title'),
                'alumni' => AlumniProfile::verified()->count(),
            ],
        ]);
    }
}
