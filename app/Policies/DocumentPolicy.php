<?php
// app/Policies/DocumentPolicy.php

namespace App\Policies;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;

// Berkas santri dapat dibuka oleh: santri pemiliknya, admin (documents.manage),
// dan pembimbing akademik bila santri itu binaannya. Konselor & alumni tidak.
class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $document->owner_id === $user->id
            || $user->checkPermissionTo('documents.manage')
            || ($user->checkPermissionTo('advisees.view-results') && $user->advisees()->whereKey($document->owner_id)->exists());
    }

    public function manage(User $user): bool
    {
        return $user->checkPermissionTo('documents.manage');
    }

    // Santri hanya bisa menghapus berkas unggahannya sendiri yang belum ditandai "sudah diperiksa".
    public function delete(User $user, Document $document): bool
    {
        return $user->checkPermissionTo('documents.manage')
            || ($document->owner_id === $user->id && $document->uploaded_by === $user->id && $document->status !== DocumentStatus::Reviewed);
    }
}
