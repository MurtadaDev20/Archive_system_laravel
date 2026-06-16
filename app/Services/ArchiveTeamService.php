<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;

class ArchiveTeamService
{
    public function managerIdFor(User $user): ?int
    {
        return app(DepartmentScopeService::class)->teamManagerIdFor($user);
    }

    public function managerIdForFile(File $file): ?int
    {
        return app(DepartmentScopeService::class)->managerIdForFile($file);
    }

    public function employeeIds(int $managerId): array
    {
        return array_values(array_filter(
            app(DepartmentScopeService::class)->teamMemberIds($managerId),
            fn (int $id) => $id !== $managerId
        ));
    }

    public function memberIds(int $managerId): array
    {
        return app(DepartmentScopeService::class)->teamMemberIds($managerId);
    }

    public function isMember(int $managerId, int $userId): bool
    {
        if ((int) $userId === (int) $managerId) {
            return true;
        }

        return in_array((int) $userId, $this->memberIds($managerId), true);
    }

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
