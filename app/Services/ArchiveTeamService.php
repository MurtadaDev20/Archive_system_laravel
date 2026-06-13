<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;

class ArchiveTeamService
{
    public function managerIdFor(User $user): ?int
    {
        if ($user->hasRole('Manager')) {
            return (int) $user->id;
        }

        return $user->manager_id ? (int) $user->manager_id : null;
    }

    public function managerIdForFile(File $file): ?int
    {
        $file->loadMissing(['folder', 'user', 'owner']);

        if ($file->folder?->user_id) {
            $folderOwner = User::find($file->folder->user_id);
            if ($folderOwner?->hasRole('Manager')) {
                return (int) $folderOwner->id;
            }
        }

        if ($file->user) {
            $fromUploader = $this->managerIdFor($file->user);
            if ($fromUploader) {
                return $fromUploader;
            }
        }

        if ($file->owner) {
            return $this->managerIdFor($file->owner);
        }

        return null;
    }

    public function employeeIds(int $managerId): array
    {
        return User::where('manager_id', $managerId)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function memberIds(int $managerId): array
    {
        return array_values(array_unique(array_merge([(int) $managerId], $this->employeeIds($managerId))));
    }

    public function isMember(int $managerId, int $userId): bool
    {
        if ((int) $userId === (int) $managerId) {
            return true;
        }

        return User::where('id', $userId)->where('manager_id', $managerId)->exists();
    }

    /** موظفو الفريق المرتبطون بالوثيقة (بدون المدير) */
    public function documentStakeholderIds(File $file): array
    {
        $teamManagerId = $this->managerIdForFile($file);
        if (! $teamManagerId) {
            return [];
        }

        $candidates = array_unique(array_filter([
            (int) $file->user_id,
            (int) $file->owner_id,
        ]));

        return array_values(array_filter(
            $candidates,
            fn (int $id) => $id > 0 && $id !== $teamManagerId && $this->isMember($teamManagerId, $id)
        ));
    }
}
