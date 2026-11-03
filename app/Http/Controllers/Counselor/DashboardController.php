<?php
// app/Http/Controllers/Counselor/DashboardController.php

namespace App\Http\Controllers\Counselor;

use App\Enums\CounselingStatus;
use App\Http\Controllers\Controller;
use App\Models\CaseHandover;
use App\Models\CounselingCase;
use App\Models\CounselingRequest;
use App\Models\CounselingSession;
use App\Models\CounselorShift;
use App\Models\CrisisAlert;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $me = $request->user();

        return view('counselor.dashboard', [
            'stats' => [
                'alerts' => CrisisAlert::where('status', 'open')->count(),
                'pending' => CounselingRequest::where('status', CounselingStatus::Pending)->count(),
                'cases' => CounselingCase::where('counselor_id', $me->id)->where('status', CounselingStatus::Open)->count(),
                'handovers' => CaseHandover::where('to_counselor_id', $me->id)->where('status', CounselingStatus::Pending)->count(),
            ],
            'nextSessions' => CounselingSession::query()
                ->whereHas('case', fn ($q) => $q->where('counselor_id', $me->id)->where('status', CounselingStatus::Open))
                ->where('next_session_at', '>', now())
                ->with('case.santri')
                ->orderBy('next_session_at')
                ->limit(5)
                ->get(),
            'onDuty' => CounselorShift::onDuty()->where('counselor_id', $me->id)->first(),
        ]);
    }
}
