<?php
// app/Http/Controllers/Advisor/StudentController.php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PathwayScorer;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Pembimbing hanya melihat hasil tes santri binaannya sendiri.
class StudentController extends Controller
{
    public function __construct(private PathwayScorer $scorer) {}

    public function index(Request $request): View
    {
        $students = $request->user()->advisees()->with('santriProfile')->orderBy('name')->get()
            ->map(fn (User $santri) => ['santri' => $santri, 'profile' => $this->scorer->profileFor($santri)]);

        return view('advisor.students.index', ['students' => $students]);
    }

    public function show(Request $request, User $student): View
    {
        abort_unless(
            $request->user()->checkPermissionTo('advisees.view-results')
            && $request->user()->advisees()->whereKey($student->id)->exists(),
            403,
        );

        $profile = $this->scorer->profileFor($student);

        return view('advisor.students.show', [
            'student' => $student->load('santriProfile'),
            'profile' => $profile,
            'headline' => $profile ? $this->scorer->headline($profile) : null,
            'attempts' => $student->testAttempts()->whereNotNull('submitted_at')->with('test')->latest('submitted_at')->get(),
            'bookings' => $student->guidanceBookings()->whereHas('slot', fn ($q) => $q->where('advisor_id', $request->user()->id))->with('slot')->latest()->get(),
        ]);
    }
}
