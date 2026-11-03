<?php
// app/Policies/CounselingCasePolicy.php

namespace App\Policies;

use App\Models\CounselingCase;
use App\Models\User;

// Hanya konselor yang SAAT INI memegang kasus yang boleh membaca/menulis catatannya.
// Setelah alih tangan diterima, konselor lama otomatis kehilangan akses (keputusan Fase 1 no. 5).
// Admin (termasuk Super Admin) tidak punya counseling.handle sehingga tidak pernah lolos.
class CounselingCasePolicy
{
    public function view(User $user, CounselingCase $case): bool
    {
        return $user->checkPermissionTo('counseling.handle') && $case->counselor_id === $user->id;
    }

    public function update(User $user, CounselingCase $case): bool
    {
        return $this->view($user, $case) && $case->isOpen();
    }
}
