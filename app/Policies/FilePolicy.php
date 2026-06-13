<?php

namespace App\Policies;

use App\Models\File;
use App\Models\Status;
use App\Models\User;
use App\Services\DocumentInboxService;

class FilePolicy
{
    public function create(User $user): bool
    {
        return ! $user->hasRole('Admin');
    }

    public function view(User $user, File $file): bool
    {
        return $this->canAccess($user, $file);
    }

    public function download(User $user, File $file): bool
    {
        return $this->canAccess($user, $file);
    }

    public function delete(User $user, File $file): bool
    {
        return $user->id === $file->user_id && (int) $file->status_id !== Status::idForSlug('approved');
    }

    public function approve(User $user, File $file): bool
    {
        return $file->folder && $user->id === $file->folder->user_id;
    }

    public function reject(User $user, File $file): bool
    {
        return $file->folder && $user->id === $file->folder->user_id;
    }

    private function canAccess(User $user, File $file): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->id === $file->user_id) {
            return true;
        }

        $folder = $file->folder;

        if ($folder && (
            $user->id === $folder->user_id
            || (int) $user->manager_id === $folder->user_id
        )) {
            return true;
        }

        return app(DocumentInboxService::class)->hasIncomingTransfer($user, $file);
    }
}
