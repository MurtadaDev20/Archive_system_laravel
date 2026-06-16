<?php

namespace App\Services;

use App\Models\Department;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DepartmentScopeService
{
    public function managedDepartmentIds(User $user): array
    {
        return Cache::remember('dept.managed.'.$user->id, 300, fn () => Department::query()
            ->where('manager_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all());
    }

    /** أقسام يرى فيها المستخدم الوثائق والمجلدات */
    public function accessDepartmentIds(User $user): array
    {
        return Cache::remember('dept.access.'.$user->id, 300, function () use ($user) {
            $ids = $this->managedDepartmentIds($user);

            if ($user->department_id) {
                $ids[] = (int) $user->department_id;
            }

            return array_values(array_unique(array_filter($ids)));
        });
    }

    public function canManageDepartment(User $user, int $departmentId): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return in_array($departmentId, $this->managedDepartmentIds($user), true);
    }

    public function canApproveFile(User $user, File $file): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if (! $file->dep_id) {
            return false;
        }

        return $this->canManageDepartment($user, (int) $file->dep_id);
    }

    public function departmentManagerId(?int $departmentId): ?int
    {
        if (! $departmentId) {
            return null;
        }

        $managerId = Department::where('id', $departmentId)->value('manager_id');

        return $managerId ? (int) $managerId : null;
    }

    public function departmentMemberIds(int $departmentId): array
    {
        return User::query()
            ->where('department_id', $departmentId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function teamManagerIdFor(User $user): ?int
    {
        $managed = $this->managedDepartmentIds($user);
        if (! empty($managed)) {
            return (int) $user->id;
        }

        if ($user->department_id) {
            return $this->departmentManagerId((int) $user->department_id);
        }

        return null;
    }

    public function managerIdForFile(File $file): ?int
    {
        if ($file->dep_id) {
            return $this->departmentManagerId((int) $file->dep_id);
        }

        return $file->folder?->dep_id
            ? $this->departmentManagerId((int) $file->folder->dep_id)
            : null;
    }

    public function teamMemberIds(int $managerId): array
    {
        $departmentIds = Department::where('manager_id', $managerId)->pluck('id');

        if ($departmentIds->isEmpty()) {
            return [(int) $managerId];
        }

        $memberIds = User::whereIn('department_id', $departmentIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge([(int) $managerId], $memberIds)));
    }

    public function isDepartmentMember(int $departmentId, int $userId): bool
    {
        return User::where('id', $userId)
            ->where('department_id', $departmentId)
            ->exists();
    }

    public static function clearUserCache(int $userId): void
    {
        Cache::forget('dept.managed.'.$userId);
        Cache::forget('dept.access.'.$userId);
        DocumentInboxService::clearSidebarCache($userId);
    }

    public static function clearDepartmentCache(int $departmentId): void
    {
        $managerId = Department::where('id', $departmentId)->value('manager_id');
        $userIds = User::where('department_id', $departmentId)->pluck('id');

        if ($managerId) {
            self::clearUserCache((int) $managerId);
        }

        foreach ($userIds as $id) {
            self::clearUserCache((int) $id);
        }
    }
}
