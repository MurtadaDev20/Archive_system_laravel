<?php

namespace App\Policies;

use App\Models\User;
use App\Services\DepartmentScopeService;

class UserPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Manager']);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasRole('Admin')) {
            return $user->id !== $model->id;
        }

        if ($user->id === $model->id) {
            return true;
        }

        if (! $user->hasRole('Manager') || ! $model->department_id) {
            return false;
        }

        return app(DepartmentScopeService::class)->canManageDepartment($user, (int) $model->department_id);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('Admin') && $user->id !== $model->id;
    }

    public function createEmployee(User $user): bool
    {
        return ! empty(app(DepartmentScopeService::class)->managedDepartmentIds($user));
    }
}
