<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\DocumentTransfer;
use App\Models\DocumentVersion;
use App\Models\File;
use App\Models\Folder;
use App\Models\Status;
use App\Models\User;
use App\Services\DepartmentScopeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->hasRole('Admin')) {
            return $this->adminDashboard();
        }

        return redirect()->route('workspace');
    }

    protected function adminDashboard()
    {
        $storagePath = storage_path('app');
        $totalSpace = disk_total_space($storagePath);
        $freeSpace = disk_free_space($storagePath);
        $usedSpace = $totalSpace - $freeSpace;

        $approvedId = Status::idForSlug('approved');
        $managerActionIds = app(\App\Services\DocumentWorkflowService::class)->managerActionStatusIds();
        $archivedId = Status::idForSlug('archived');
        $expiredId = Status::idForSlug('expired');
        $rejectedId = Status::idForSlug('rejected');

        $stats = Cache::remember('dashboard.stats', 120, fn () => [
            'users' => \App\Models\User::count(),
            'departments' => Department::count(),
            'folders' => \App\Models\Folder::count(),
            'files' => File::count(),
            'newThisMonth' => File::whereMonth('created_at', now()->month)->count(),
            'pending' => File::whereIn('status_id', $managerActionIds)->count(),
            'approved' => File::where('status_id', $approvedId)->count(),
            'rejected' => File::where('status_id', $rejectedId)->count(),
            'archived' => File::where('status_id', $archivedId)->count(),
            'expired' => File::where('status_id', $expiredId)->count()
                + File::whereNotNull('expiry_date')->whereDate('expiry_date', '<', now())->count(),
            'totalSpaceGb' => round($totalSpace / (1024 ** 3), 1),
            'freeSpaceGb' => round($freeSpace / (1024 ** 3), 1),
            'usedSpaceGb' => round($usedSpace / (1024 ** 3), 1),
            'usedPercent' => $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100) : 0,
        ]);

        $byDepartment = Cache::remember('dashboard.by_department', 120, fn () => File::query()
            ->select('dep_id', DB::raw('count(*) as total'))
            ->groupBy('dep_id')
            ->with('department:id,dep_name')
            ->get()
            ->map(fn ($r) => ['label' => $r->department?->dep_name ?? '—', 'value' => $r->total]));

        $byMonth = Cache::remember('dashboard.by_month', 120, fn () => File::query()
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get());

        $storageGrowth = Cache::remember('dashboard.storage_growth', 120, fn () => DocumentVersion::query()
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('sum(size) as total'))
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get());

        return view('layouts.dashboard', [
            'stats' => $stats,
            'byDepartment' => $byDepartment,
            'byMonth' => $byMonth,
            'storageGrowth' => $storageGrowth,
            'storageGrowthMb' => $storageGrowth->map(fn ($r) => round(($r->total ?? 0) / (1024 * 1024), 2))->values(),
            'recentActivity' => AuditLog::with('user')->latest()->limit(12)->get(),
            'recentDocuments' => File::with(['user', 'status', 'department'])
                ->latest()->limit(8)->get(),
            'pendingApprovals' => File::with(['user', 'folder'])
                ->whereIn('status_id', $managerActionIds)->latest()->limit(8)->get(),
            'expiringDocuments' => File::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->whereDate('expiry_date', '>=', now())
                ->with(['user', 'department'])
                ->orderBy('expiry_date')
                ->limit(8)
                ->get(),
            'statuses' => Status::orderBy('sort_order')->get(),
        ]);
    }

    public function workspace()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return redirect()->route('dashboard');
        }

        $managerActionIds = app(\App\Services\DocumentWorkflowService::class)->managerActionStatusIds();
        $approvedId = Status::idForSlug('approved');
        $rejectedId = Status::idForSlug('rejected');
        $scopeQuery = $this->scopedFilesQuery($user);
        $deptScope = app(DepartmentScopeService::class);
        $managedIds = $deptScope->managedDepartmentIds($user);
        $accessIds = $deptScope->accessDepartmentIds($user);
        $hasManagedDepartments = ! empty($managedIds);
        $isManager = $hasManagedDepartments || $user->hasRole('Manager');

        $stats = [
            'myDocuments' => (clone $scopeQuery)->count(),
            'pending' => $hasManagedDepartments
                ? File::whereIn('status_id', $managerActionIds)
                    ->whereIn('dep_id', $managedIds)
                    ->count()
                : (clone $scopeQuery)->whereIn('status_id', $managerActionIds)->count(),
            'approved' => (clone $scopeQuery)->where('status_id', $approvedId)->count(),
            'rejected' => (clone $scopeQuery)->where('status_id', $rejectedId)->count(),
            'newThisMonth' => (clone $scopeQuery)->whereMonth('created_at', now()->month)->count(),
            'folders' => ! empty($accessIds)
                ? Folder::whereIn('dep_id', $accessIds)->count()
                : 0,
            'employees' => $hasManagedDepartments
                ? User::whereIn('department_id', $managedIds)->count()
                : 0,
        ];

        $recentDocuments = (clone $scopeQuery)
            ->with(['status', 'folder', 'department'])
            ->latest()
            ->limit(8)
            ->get();

        $pendingApprovals = collect();
        if ($hasManagedDepartments) {
            $pendingApprovals = File::with(['user', 'folder', 'status', 'department'])
                ->whereIn('status_id', $managerActionIds)
                ->whereIn('dep_id', $managedIds)
                ->latest()
                ->limit(8)
                ->get();
        }

        $expiringDocuments = (clone $scopeQuery)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->limit(6)
            ->get();

        $inbox = app(\App\Services\DocumentInboxService::class);
        $transferDeptIds = $inbox->managedDepartmentIds($user);
        $incomingTransfers = collect();
        if (! empty($transferDeptIds)) {
            $incomingTransfers = File::whereHas('transfers', fn ($q) => $q
                ->whereIn('to_department_id', $transferDeptIds)
                ->whereIn('status', [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED]))
                ->with(['user', 'transfers.fromDepartment', 'transfers.toDepartment'])
                ->latest()
                ->limit(6)
                ->get();
        }

        return view('layouts.staff-dashboard', [
            'stats' => $stats,
            'isManager' => $isManager,
            'recentDocuments' => $recentDocuments,
            'pendingApprovals' => $pendingApprovals,
            'expiringDocuments' => $expiringDocuments,
            'incomingTransfers' => $incomingTransfers,
            'pendingTransferCount' => $inbox->pendingTransferCount($user),
        ]);
    }

    protected function scopedFilesQuery(User $user)
    {
        $scope = app(DepartmentScopeService::class);
        $accessIds = $scope->accessDepartmentIds($user);

        return File::query()->where(function ($q) use ($user, $accessIds) {
            $q->where('user_id', $user->id);

            if (! empty($accessIds)) {
                $q->orWhereIn('dep_id', $accessIds);
            }
        });
    }
}
