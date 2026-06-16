<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;
use App\Services\DepartmentScopeService;

class FolderPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Manager', 'Admin'])
            || ! empty(app(DepartmentScopeService::class)->managedDepartmentIds($user));
    }

    public function update(User $user, Folder $folder): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return app(DepartmentScopeService::class)->canManageDepartment($user, (int) $folder->dep_id);
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

        $scope = app(DepartmentScopeService::class);

        if ($scope->canManageDepartment($user, (int) $folder->dep_id)) {
            return true;
        }

        return $user->department_id && (int) $user->department_id === (int) $folder->dep_id;
    }
}
