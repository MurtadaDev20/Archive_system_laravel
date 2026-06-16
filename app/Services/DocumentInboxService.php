<?php

namespace App\Services;

use App\Models\DocumentTransfer;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DocumentInboxService
{
    public function departmentIdsFor(User $user): array
    {
        return app(DepartmentScopeService::class)->accessDepartmentIds($user);
    }

    public function managedDepartmentIds(User $user): array
    {
        return app(DepartmentScopeService::class)->managedDepartmentIds($user);
    }

    public function sidebarCounts(User $user): array
    {
        return Cache::remember('archive.sidebar.'.$user->id, 30, fn () => [
            'documents' => $this->pendingApprovalCount($user, false) + $this->pendingTransferCount($user, false),
            'transfers' => $this->pendingTransferCount($user, false),
            'approvals' => $this->pendingApprovalCount($user, false),
        ]);
    }

    public static function clearSidebarCache(int $userId): void
    {
        Cache::forget('archive.sidebar.'.$userId);
        Cache::forget('archive.dept_ids.'.$userId);
        DocumentSearchService::clearUserFilterCache($userId);
    }

    public function pendingApprovalCount(User $user, bool $useCache = true): int
    {
        if ($useCache) {
            return $this->sidebarCounts($user)['approvals'];
        }

        $statusIds = app(DocumentWorkflowService::class)->managerActionStatusIds();
        $scope = app(DepartmentScopeService::class);

        if ($user->hasRole('Admin')) {
            return File::whereIn('status_id', $statusIds)->count();
        }

        $managedIds = $scope->managedDepartmentIds($user);

        if (empty($managedIds)) {
            return 0;
        }

        return File::whereIn('status_id', $statusIds)
            ->whereIn('dep_id', $managedIds)
            ->count();
    }

    public function pendingTransferCount(User $user, bool $useCache = true): int
    {
        if ($useCache) {
            return $this->sidebarCounts($user)['transfers'];
        }

        $deptIds = $this->managedDepartmentIds($user);

        if (empty($deptIds) && ! $user->hasRole('Admin')) {
            return 0;
        }

        $query = DocumentTransfer::query()
            ->whereIn('status', [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED]);

        if (! $user->hasRole('Admin')) {
            $query->whereIn('to_department_id', $deptIds);
        }

        return $query->count();
    }

    public function sidebarDocumentsBadge(User $user): int
    {
        return $this->sidebarCounts($user)['documents'];
    }

    public function canRespondToTransfer(User $user, DocumentTransfer $transfer): bool
    {
        if (! in_array($transfer->status, [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED], true)) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        return in_array((int) $transfer->to_department_id, $this->managedDepartmentIds($user), true);
    }

    public function hasIncomingTransfer(User $user, File $file, ?array $incomingIds = null): bool
    {
        if ($incomingIds !== null) {
            return in_array((int) $file->id, $incomingIds, true);
        }

        $deptIds = $this->managedDepartmentIds($user);

        if (empty($deptIds) && ! $user->hasRole('Admin')) {
            return false;
        }

        $query = $file->transfers()
            ->whereIn('status', [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED]);

        if (! $user->hasRole('Admin')) {
            $query->whereIn('to_department_id', $deptIds);
        }

        return $query->exists();
    }
}
