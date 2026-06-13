<?php

namespace App\Policies;

use App\Models\DocumentType;
use App\Models\User;

class DocumentTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, DocumentType $documentType): bool
    {
        return $user->hasRole('Admin');
    }

    public function delete(User $user, DocumentType $documentType): bool
    {
        return $user->hasRole('Admin');
    }
}
