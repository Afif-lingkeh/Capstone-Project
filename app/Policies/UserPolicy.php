<?php
// app/Policies/UserPolicy.php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->checkPermissionTo('users.manage');
    }

    public function create(User $actor): bool
    {
        return $actor->checkPermissionTo('users.manage');
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->checkPermissionTo('users.manage');
    }

    // Mencegah Super Admin mengunci dirinya sendiri.
    public function toggleStatus(User $actor, User $target): bool
    {
        return $actor->checkPermissionTo('users.manage') && $actor->isNot($target);
    }
}
