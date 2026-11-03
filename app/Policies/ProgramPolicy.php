<?php
// app/Policies/ProgramPolicy.php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    // Santri: daftar & detail program yang sudah dipublikasikan.
    public function browse(User $user): bool
    {
        return $user->checkPermissionTo('santri-features.use');
    }

    public function view(User $user, Program $program): bool
    {
        return $user->checkPermissionTo('programs.manage')
            || ($program->isPublished() && $user->checkPermissionTo('santri-features.use'));
    }

    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('programs.manage');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('programs.manage');
    }

    public function update(User $user, Program $program): bool
    {
        return $user->checkPermissionTo('programs.manage');
    }

    // Program yang sudah punya peminat tidak dihapus (rekapnya tetap diperlukan); cukup ditutup.
    public function delete(User $user, Program $program): bool
    {
        return $user->checkPermissionTo('programs.manage') && ! $program->interests()->exists();
    }

    public function viewInterests(User $user, Program $program): bool
    {
        return $user->checkPermissionTo('programs.view-interests');
    }

    public function expressInterest(User $user, Program $program): bool
    {
        return $program->isPublished() && $user->checkPermissionTo('santri-features.use');
    }
}
