<?php
// app/Services/CrisisResponder.php

namespace App\Services;

use App\Enums\Role;
use App\Models\CounselorShift;
use App\Models\CrisisAlert;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

// Deteksi tanda krisis + notifikasi. Keputusan Fase 1 no. 6: konselor piket (in-app + email);
// bila tidak ada yang piket, semua konselor aktif diberi tahu.
class CrisisResponder
{
    public function detect(string $text): bool
    {
        $normalized = Str::of($text)->lower()->squish()->toString();

        foreach (config('peduli.crisis_keywords', []) as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }

    public function raise(User $santri, string $source, ?int $sourceId = null): CrisisAlert
    {
        $alert = CrisisAlert::create([
            'santri_id' => $santri->id,
            'source' => $source,
            'source_id' => $sourceId,
            'status' => 'open',
        ]);

        // Isi notifikasi sengaja tidak memuat cerita santri.
        Notification::send($this->recipients(), new InAppNotification(
            title: 'Peringatan krisis: '.$santri->name,
            body: 'Ada tanda krisis dari '.($source === 'chat' ? 'Asisten AI' : 'permintaan konseling').'. Mohon segera ditindaklanjuti.',
            url: route('counselor.alerts.index'),
            icon: 'siren',
            tone: 'danger',
            mail: true,
        ));

        return $alert;
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    public function recipients()
    {
        $onDuty = User::whereIn('id', CounselorShift::onDuty()->pluck('counselor_id'))->where('is_active', true)->get();

        return $onDuty->isNotEmpty()
            ? $onDuty
            : User::role(Role::Counselor->value)->where('is_active', true)->get();
    }

    /** Nama konselor yang sedang piket (untuk ditampilkan ke santri). */
    public function onDutyNames(): array
    {
        return User::whereIn('id', CounselorShift::onDuty()->pluck('counselor_id'))->pluck('name')->all();
    }
}
