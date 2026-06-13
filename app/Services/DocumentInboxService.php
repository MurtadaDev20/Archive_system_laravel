<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DocumentTransfer;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DocumentInboxService
{
    public function departmentIdsFor(User $user): array
    {
        return Cache::remember('archive.dept_ids.'.$user->id, 300, function () use ($user) {
            $ids = Department::where('manager_id', $user->id)->pluck('id')->all();

            $ids = array_merge(
                $ids,
                Folder::where('user_id', $user->id)->pluck('dep_id')->all()
            );

            if ($user->manager_id) {
                $ids = array_merge(
                    $ids,
                    Folder::where('user_id', $user->manager_id)->pluck('dep_id')->all()
                );
            }

            return array_values(array_unique(array_filter($ids)));
        });
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

        if ($user->hasRole('Admin')) {
            return File::whereIn('status_id', $statusIds)->count();
        }

        if (! $user->hasRole('Manager')) {
            return 0;
        }

        return File::whereIn('status_id', $statusIds)
            ->whereHas('folder', fn ($q) => $q->where('user_id', $user->id))
            ->count();
    }

    public function pendingTransferCount(User $user, bool $useCache = true): int
    {
        if ($useCache) {
            return $this->sidebarCounts($user)['transfers'];
        }

        $deptIds = $this->departmentIdsFor($user);

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

        return in_array((int) $transfer->to_department_id, $this->departmentIdsFor($user), true);
    }

    public function hasIncomingTransfer(User $user, File $file, ?array $incomingIds = null): bool
    {
        if ($incomingIds !== null) {
            return in_array((int) $file->id, $incomingIds, true);
        }

        $deptIds = $this->departmentIdsFor($user);

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
