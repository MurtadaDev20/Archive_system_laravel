@extends('layouts.master')

@section('title', __('archive.dashboard') . ' — ' . __('archive.app_name'))

@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.dashboard'),
        'subtitle' => __('archive.dashboard_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => route('dashboard')],
            ['label' => __('archive.dashboard')],
        ],
    ])
@endsection

@section('content')
    <div class="quick-actions mb-4">
        <a href="{{ route('manageFile') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-files me-1"></i>{{ __('archive.manage_documents') }}</a>
        <a href="{{ route('departments') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-building me-1"></i>{{ __('archive.departments') }}</a>
        <a href="{{ route('taxonomy') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-sliders me-1"></i>{{ __('archive.manage_taxonomy') }}</a>
    </div>

    {{-- KPI Row 1: Documents --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <x-stat-card :label="__('archive.documents')" :value="$stats['files']" icon="bi-file-earmark-text" variant="primary" :footer="__('archive.total_files')" />
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <x-stat-card :label="__('archive.new_documents')" :value="$stats['newThisMonth']" icon="bi-file-earmark-plus" variant="accent" :footer="__('archive.this_month')" />
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <x-stat-card :label="__('archive.archived_documents')" :value="$stats['archived']" icon="bi-archive" variant="secondary" :footer="__('archive.status_archived')" />
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <x-stat-card :label="__('archive.expired_documents')" :value="$stats['expired']" icon="bi-calendar-x" variant="danger" :footer="__('archive.status_expired')" />
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <x-stat-card :label="__('archive.pending_approval')" :value="$stats['pending']" icon="bi-hourglass-split" variant="warning" :footer="__('archive.awaiting_review')" />
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <x-stat-card :label="__('archive.approved')" :value="$stats['approved']" icon="bi-check-circle" variant="accent" :footer="__('archive.successfully_archived')" />
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="archive-card h-100">
                <div class="archive-card-header">
                    <h5><i class="bi bi-pie-chart me-2"></i>{{ __('archive.chart_by_department') }}</h5>
                </div>
                <div class="archive-card-body">
                    <canvas id="chartByDepartment" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="archive-card h-100">
                <div class="archive-card-header">
                    <h5><i class="bi bi-bar-chart me-2"></i>{{ __('archive.chart_by_month') }}</h5>
                </div>
                <div class="archive-card-body">
                    <canvas id="chartByMonth" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="archive-card h-100">
                <div class="archive-card-header">
                    <h5><i class="bi bi-graph-up-arrow me-2"></i>{{ __('archive.chart_storage_growth') }}</h5>
                </div>
                <div class="archive-card-body">
                    <canvas id="chartStorageGrowth" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Storage + Activity --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="archive-card h-100">
                <div class="archive-card-header">
                    <h5><i class="bi bi-hdd me-2"></i>{{ __('archive.storage_usage') }}</h5>
                </div>
                <div class="archive-card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-archive-muted">{{ __('archive.used') }}</span>
                        <strong>{{ $stats['usedSpaceGb'] }} GB / {{ $stats['totalSpaceGb'] }} GB</strong>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['usedPercent'] }}%"></div>
                    </div>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="dept-stat">
                                <div class="value">{{ $stats['totalSpaceGb'] }}</div>
                                <div class="label">{{ __('archive.total_gb') }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="dept-stat">
                                <div class="value text-success">{{ $stats['freeSpaceGb'] }}</div>
                                <div class="label">{{ __('archive.free_gb') }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="dept-stat">
                                <div class="value">{{ $stats['usedPercent'] }}%</div>
                                <div class="label">{{ __('archive.used_percent') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="archive-card h-100">
                <div class="archive-card-header">
                    <h5><i class="bi bi-clock-history me-2"></i>{{ __('archive.recent_activity') }}</h5>
                    <span class="badge text-bg-light border">{{ __('archive.audit_log') }}</span>
                </div>
                <div class="archive-card-body">
                    @if($recentActivity->isEmpty())
                        <x-empty-state icon="bi-activity" :title="__('archive.no_activity')" :message="__('archive.no_activity_desc')" />
                    @else
                        <ul class="activity-timeline">
                            @foreach($recentActivity as $log)
                                <li>
                                    <div class="activity-icon"><i class="bi bi-dot"></i></div>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ archive_audit_description($log) }}</div>
                                        <div class="activity-meta">
                                            {{ $log->user?->name ?? __('archive.system') }}
                                            &middot; {{ $log->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Widgets --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="archive-card h-100">
                <div class="archive-card-header">
                    <h5><i class="bi bi-file-earmark me-2"></i>{{ __('archive.recent_documents') }}</h5>
                    <a href="{{ route('manageFile') }}" class="btn btn-sm btn-outline-secondary">{{ __('archive.view_all') }}</a>
                </div>
                <div class="archive-card-body p-0">
                    @if($recentDocuments->isEmpty())
                        <div class="p-4"><x-empty-state icon="bi-file-earmark" :title="__('archive.no_documents')" /></div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentDocuments as $doc)
                                <a href="{{ route('document.show', $doc) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ $doc->file_name }}</div>
                                        <small class="text-archive-muted">{{ $doc->document_number ?? $doc->code }} &middot; {{ $doc->user?->name }}</small>
                                    </div>
                                    <span class="badge text-bg-light border">{{ archive_status_label($doc->status_id) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="archive-card h-100">
                <div class="archive-card-header">
                    <h5><i class="bi bi-check2-square me-2"></i>{{ __('archive.pending_approvals') }}</h5>
                </div>
                <div class="archive-card-body p-0">
                    @if($pendingApprovals->isEmpty())
                        <div class="p-4 text-center text-archive-muted"><i class="bi bi-check-circle fs-3 d-block mb-2"></i>{{ __('archive.no_pending') }}</div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($pendingApprovals as $doc)
                                <a href="{{ route('document.show', $doc) }}" class="list-group-item list-group-item-action">
                                    <div class="fw-semibold">{{ $doc->file_name }}</div>
                                    <small class="text-archive-muted">{{ $doc->folder?->folder_name }} &middot; {{ $doc->created_at->diffForHumans() }}</small>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="archive-card">
                <div class="archive-card-header">
                    <h5><i class="bi bi-exclamation-triangle me-2 text-warning"></i>{{ __('archive.expiring_soon') }}</h5>
                </div>
                <div class="archive-card-body p-0">
                    @if($expiringDocuments->isEmpty())
                        <div class="p-4 text-center text-archive-muted">{{ __('archive.no_expiring') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table archive-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('archive.document') }}</th>
                                        <th>{{ __('archive.department') }}</th>
                                        <th>{{ __('archive.expiry_date') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringDocuments as $doc)
                                        <tr>
                                            <td>{{ $doc->file_name }}</td>
                                            <td>{{ $doc->department?->dep_name ?? '—' }}</td>
                                            <td>{{ $doc->expiry_date?->format('Y-m-d') }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('document.show', $doc) }}" class="btn btn-sm btn-outline-secondary">{{ __('archive.preview') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deptLabels = @json($byDepartment->pluck('label'));
    const deptValues = @json($byDepartment->pluck('value'));
    const monthLabels = @json($byMonth->pluck('month'));
    const monthValues = @json($byMonth->pluck('total'));
    const storageLabels = @json($storageGrowth->pluck('month')->values());
    const storageValues = @json($storageGrowthMb);

    const accent = getComputedStyle(document.documentElement).getPropertyValue('--archive-accent').trim() || '#198754';

    if (document.getElementById('chartByDepartment')) {
        new Chart(document.getElementById('chartByDepartment'), {
            type: 'doughnut',
            data: {
                labels: deptLabels,
                datasets: [{ data: deptValues, backgroundColor: ['#198754','#0d6efd','#ffc107','#dc3545','#6c757d','#20c997'] }]
            },
            options: { plugins: { legend: { position: 'bottom', rtl: true } }, maintainAspectRatio: false }
        });
    }

    if (document.getElementById('chartByMonth')) {
        new Chart(document.getElementById('chartByMonth'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{ label: '{{ __('archive.documents') }}', data: monthValues, backgroundColor: accent }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } }, maintainAspectRatio: false }
        });
    }

    if (document.getElementById('chartStorageGrowth')) {
        new Chart(document.getElementById('chartStorageGrowth'), {
            type: 'line',
            data: {
                labels: storageLabels,
                datasets: [{ label: 'MB', data: storageValues, borderColor: accent, tension: 0.3, fill: false }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } }, maintainAspectRatio: false }
        });
    }
});
</script>
@endsection
