<?php
// app/Policies/PartnerPolicy.php

namespace App\Policies;

use App\Models\Partner;
use App\Models\User;

class PartnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('programs.manage');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('programs.manage');
    }

    public function update(User $user, Partner $partner): bool
    {
        return $user->checkPermissionTo('programs.manage');
    }

    // Mitra yang masih punya program tidak boleh dihapus.
    public function delete(User $user, Partner $partner): bool
    {
        return $user->checkPermissionTo('programs.manage') && ! $partner->programs()->exists();
    }
}
