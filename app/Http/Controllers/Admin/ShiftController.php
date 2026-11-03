<?php
// app/Http/Controllers/Admin/ShiftController.php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\CounselorShift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Jadwal piket konselor: penerima pertama notifikasi krisis.
class ShiftController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:counselor-shifts.manage')];
    }

    public function index(): View
    {
        return view('admin.shifts.index', [
            'shifts' => CounselorShift::where('ends_at', '>', now())->with('counselor')->orderBy('starts_at')->get()
                ->groupBy(fn (CounselorShift $s) => $s->starts_at->toDateString()),
            'onDuty' => CounselorShift::onDuty()->with('counselor')->get(),
            'counselors' => User::role(Role::Counselor->value)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'counselor_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'repeat_days' => ['required', 'integer', 'min:0', 'max:30'],
        ]);
        abort_unless(User::find($data['counselor_id'])?->hasRole(Role::Counselor->value), 422);

        for ($day = 0; $day <= $data['repeat_days']; $day++) {
            $start = Carbon::parse($data['date'].' '.$data['start_time'])->addDays($day);
            $end = Carbon::parse($data['date'].' '.$data['end_time'])->addDays($day);
            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay(); // piket malam melewati tengah malam
            }
            CounselorShift::create(['counselor_id' => $data['counselor_id'], 'starts_at' => $start, 'ends_at' => $end]);
        }

        return back()->with('success', ($data['repeat_days'] + 1).' jadwal piket ditambahkan.');
    }

    public function destroy(CounselorShift $shift): RedirectResponse
    {
        $shift->delete();

        return back()->with('success', 'Jadwal piket dihapus.');
    }
}
