<?php
// app/Policies/InstitutionPolicy.php

namespace App\Policies;

use App\Models\Institution;
use App\Models\User;

class InstitutionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('links.manage');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('links.manage');
    }

    public function update(User $user, Institution $institution): bool
    {
        return $user->checkPermissionTo('links.manage');
    }

    // Institusi yang masih punya tautan tidak boleh dihapus.
    public function delete(User $user, Institution $institution): bool
    {
        return $user->checkPermissionTo('links.manage') && ! $institution->links()->exists();
    }
}
