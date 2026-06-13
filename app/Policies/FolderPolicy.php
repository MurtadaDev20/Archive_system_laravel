<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Admin']);
    }

    public function update(User $user, Folder $folder): bool
    {
        return $user->hasRole('Admin')
            || $user->id === $folder->user_id
            || (int) $user->manager_id === $folder->user_id;
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $this->update($user, $folder);
    }

    public function upload(User $user, Folder $folder): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->id === $folder->user_id
            || (int) $user->manager_id === $folder->user_id;
    }
}
