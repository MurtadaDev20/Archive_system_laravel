<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DocumentTransfer;
use App\Models\File;
use App\Models\Status;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DocumentSearchService
{
    /** حالات قديمة مخفية من الفلاتر */
    private const HIDDEN_STATUS_SLUGS = ['pending_review', 'under_review'];

    public function search(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->scopedQuery($filters);

        $query->with([
            'folder:id,folder_name,user_id,dep_id',
            'department:id,dep_name',
            'user:id,name',
            'status:id,slug,label_ar,name,sort_order',
            'category:id,name',
            'documentType:id,name',
        ]);

        $this->applyFilters($query, $filters);

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function scopedQuery(array $filters): Builder
    {
        $query = File::query()->whereNull('deleted_at');
        $this->applyScope($query, $filters);

        return $query;
    }

    public function incomingTransferFileIds(User $user): array
    {
        $inbox = app(DocumentInboxService::class);
        $deptIds = $inbox->managedDepartmentIds($user);

        if (empty($deptIds) && ! $user->hasRole('Admin')) {
            return [];
        }

        $cacheKey = 'archive.incoming_files.'.$user->id;

        return Cache::remember($cacheKey, 30, function () use ($user, $deptIds) {
            $query = File::query()
                ->whereHas('transfers', fn ($tq) => $tq
                    ->whereIn('status', [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED]));

            if (! $user->hasRole('Admin')) {
                $query->whereHas('transfers', fn ($tq) => $tq
                    ->whereIn('to_department_id', $deptIds)
                    ->whereIn('status', [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED]));
            }

            return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
        });
    }

    public function filterDepartments(User $user): Collection
    {
        $cacheKey = 'archive.filter_depts.'.$user->id;

        return Cache::remember($cacheKey, 120, function () use ($user) {
            if ($user->hasRole('Admin')) {
                return Department::orderBy('dep_name')->get(['id', 'dep_name']);
            }

            $scope = app(DepartmentScopeService::class);
            $deptIds = $scope->accessDepartmentIds($user);

            if (empty($deptIds)) {
                return collect();
            }

            return Department::whereIn('id', $deptIds)->orderBy('dep_name')->get(['id', 'dep_name']);
        });
    }

    public function filterStatuses(User $user): Collection
    {
        $cacheKey = 'archive.filter_statuses.'.$user->id;

        return Cache::remember($cacheKey, 120, function () use ($user) {
            $statusIds = $this->scopedQuery(['user' => $user])
                ->distinct()
                ->pluck('status_id')
                ->filter();

            if ($statusIds->isEmpty()) {
                return collect();
            }

            return Status::query()
                ->whereIn('id', $statusIds)
                ->whereNotIn('slug', self::HIDDEN_STATUS_SLUGS)
                ->orderBy('sort_order')
                ->get(['id', 'slug', 'label_ar', 'name', 'sort_order']);
        });
    }

    public static function clearUserFilterCache(int $userId): void
    {
        Cache::forget('archive.filter_depts.'.$userId);
        Cache::forget('archive.filter_statuses.'.$userId);
        Cache::forget('archive.incoming_files.'.$userId);
    }

    public static function clearTeamFilterCache(array $userIds): void
    {
        foreach ($userIds as $id) {
            self::clearUserFilterCache((int) $id);
        }
    }

    private function applyScope(Builder $query, array $filters): void
    {
        if (empty($filters['user'])) {
            return;
        }

        $user = $filters['user'];

        if ($user->hasRole('Admin')) {
            return;
        }

        $scope = app(DepartmentScopeService::class);
        $accessDeptIds = $scope->accessDepartmentIds($user);
        $managedDeptIds = $scope->managedDepartmentIds($user);

        $query->where(function ($q) use ($user, $accessDeptIds, $managedDeptIds) {
            $q->where('user_id', $user->id)
                ->orWhere('owner_id', $user->id);

            if (! empty($accessDeptIds)) {
                $q->orWhereIn('dep_id', $accessDeptIds);
            }

            if (! empty($managedDeptIds)) {
                $q->orWhereHas('transfers', fn ($tq) => $tq
                    ->whereIn('to_department_id', $managedDeptIds)
                    ->whereIn('status', [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED]));
            }
        });

        if (! empty($filters['inbox']) && $filters['inbox'] === 'transfers' && ! empty($managedDeptIds)) {
            $query->whereHas('transfers', fn ($tq) => $tq
                ->whereIn('to_department_id', $managedDeptIds)
                ->whereIn('status', [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED]));
        }

        if (! empty($filters['inbox']) && $filters['inbox'] === 'approvals') {
            $statusIds = app(DocumentWorkflowService::class)->managerActionStatusIds();
            $managedIds = $scope->managedDepartmentIds($user);

            if (empty($managedIds)) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereIn('status_id', $statusIds)
                ->whereIn('dep_id', $managedIds);
        }
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['document_number'])) {
            $query->where('document_number', 'like', '%'.$filters['document_number'].'%');
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $like = '%'.$term.'%';

            $query->where(function ($q) use ($like, $term) {
                $q->where('file_name', 'like', $like)
                    ->orWhere('document_number', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('ocr_text', 'like', $like)
                    ->orWhereHas('tags', fn ($tq) => $tq->where('name', 'like', $like))
                    ->orWhereHas('folder', fn ($fq) => $fq->where('folder_name', 'like', $like))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $like));

                if ($this->canUseFulltextSearch($term)) {
                    $q->orWhereRaw(
                        'MATCH(file_name, description, ocr_text) AGAINST (? IN BOOLEAN MODE)',
                        [$this->fulltextQuery($term)]
                    );
                }
            });
        }

        if (! empty($filters['ocr_status'])) {
            $query->where('ocr_status', $filters['ocr_status']);
        }

        if (! empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (! empty($filters['dep_id'])) {
            $query->where('dep_id', $filters['dep_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['document_type_id'])) {
            $query->where('document_type_id', $filters['document_type_id']);
        }

        if (! empty($filters['tag_id'])) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag_id']));
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if (! empty($filters['folder_id'])) {
            $query->where('folder_id', $filters['folder_id']);
        }
    }

    private function canUseFulltextSearch(string $term): bool
    {
        return \Illuminate\Support\Facades\DB::getDriverName() === 'mysql'
            && mb_strlen($term) >= 3;
    }

    private function fulltextQuery(string $term): string
    {
        $parts = preg_split('/\s+/u', $term) ?: [];

        return collect($parts)
            ->filter(fn ($p) => mb_strlen($p) >= 2)
            ->map(fn ($p) => '+'.preg_replace('/[^\p{L}\p{N}]/u', '', $p))
            ->implode(' ');
    }
}
