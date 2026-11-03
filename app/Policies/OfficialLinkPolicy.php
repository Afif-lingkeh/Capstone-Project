<?php
// app/Policies/OfficialLinkPolicy.php

namespace App\Policies;

use App\Models\OfficialLink;
use App\Models\User;

class OfficialLinkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('links.manage');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('links.manage');
    }

    public function update(User $user, OfficialLink $link): bool
    {
        return $user->checkPermissionTo('links.manage');
    }

    public function delete(User $user, OfficialLink $link): bool
    {
        return $user->checkPermissionTo('links.manage');
    }

    public function check(User $user, OfficialLink $link): bool
    {
        return $user->checkPermissionTo('links.manage');
    }

    // Dipisah dari "manage" agar ke depan bisa ada peran yang boleh menambah tautan
    // tetapi tidak boleh memverifikasinya sendiri.
    public function verify(User $user, OfficialLink $link): bool
    {
        return $user->checkPermissionTo('links.verify');
    }

    public function reject(User $user, OfficialLink $link): bool
    {
        return $user->checkPermissionTo('links.verify');
    }
}
