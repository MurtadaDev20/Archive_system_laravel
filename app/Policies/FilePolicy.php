<?php

namespace App\Policies;

use App\Models\File;
use App\Models\Status;
use App\Models\User;
use App\Services\DepartmentScopeService;
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
        return app(DepartmentScopeService::class)->canApproveFile($user, $file);
    }

    public function reject(User $user, File $file): bool
    {
        return app(DepartmentScopeService::class)->canApproveFile($user, $file);
    }

    public function update(User $user, File $file): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->id === $file->user_id || $user->id === $file->owner_id) {
            return true;
        }

        return app(DepartmentScopeService::class)->canApproveFile($user, $file);
    }

    private function canAccess(User $user, File $file): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->id === $file->user_id || $user->id === $file->owner_id) {
            return true;
        }

        $scope = app(DepartmentScopeService::class);
        $accessIds = $scope->accessDepartmentIds($user);

        if ($file->dep_id && in_array((int) $file->dep_id, $accessIds, true)) {
            return true;
        }

        return app(DocumentInboxService::class)->hasIncomingTransfer($user, $file);
    }
}
