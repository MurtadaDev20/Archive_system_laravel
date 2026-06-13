@extends('layouts.master')

@section('title', __('archive.workspace_dashboard') . ' — ' . __('archive.app_name'))

@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.workspace_dashboard'),
        'subtitle' => $isManager ? __('archive.manager_dashboard_subtitle') : __('archive.employee_dashboard_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => route('workspace')],
            ['label' => __('archive.workspace_dashboard')],
        ],
    ])
@endsection

@section('content')
    <div class="quick-actions mb-4">
        @can('create', \App\Models\File::class)
            <a href="{{ route('addFile') }}" class="btn btn-archive-accent btn-sm"><i class="bi bi-cloud-upload me-1"></i>{{ __('archive.upload_document') }}</a>
        @endcan
        <a href="{{ route('manageFile') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-files me-1"></i>{{ __('archive.manage_documents') }}</a>
        @if($pendingTransferCount > 0)
            <a href="{{ route('manageFile') }}?inbox=transfers" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-arrow-left-right me-1"></i>{{ __('archive.incoming_transfers') }}
                <span class="badge bg-danger">{{ $pendingTransferCount }}</span>
            </a>
        @endif
        <a href="{{ route('folders') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-folder2-open me-1"></i>{{ __('archive.browse_folders') }}</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-stat-card :label="__('archive.my_documents')" :value="$stats['myDocuments']" icon="bi-file-earmark-text" variant="primary" :footer="__('archive.total_files')" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-stat-card :label="__('archive.new_documents')" :value="$stats['newThisMonth']" icon="bi-file-earmark-plus" variant="accent" :footer="__('archive.this_month')" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-stat-card :label="__('archive.pending_approval')" :value="$stats['pending']" icon="bi-hourglass-split" variant="warning" :footer="__('archive.awaiting_review')" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-stat-card :label="__('archive.approved')" :value="$stats['approved']" icon="bi-check-circle" variant="accent" :footer="__('archive.successfully_archived')" />
        </div>
    </div>

    @if($isManager)
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <x-stat-card :label="__('archive.folders')" :value="$stats['folders']" icon="bi-folder2" variant="secondary" :footer="__('archive.document_containers')" />
            </div>
            <div class="col-md-4">
                <x-stat-card :label="__('archive.employees')" :value="$stats['employees']" icon="bi-people" variant="primary" :footer="__('archive.team_members')" />
            </div>
            <div class="col-md-4">
                <x-stat-card :label="__('archive.rejected')" :value="$stats['rejected']" icon="bi-x-circle" variant="danger" :footer="__('archive.returned_documents')" />
            </div>
        </div>
    @endif

    @if($pendingTransferCount > 0)
        <div class="archive-card mb-4 border-danger border-2">
            <div class="archive-card-header">
                <h5><i class="bi bi-arrow-left-right me-2 text-danger"></i>{{ __('archive.incoming_transfers') }}</h5>
                <a href="{{ route('manageFile') }}?inbox=transfers" class="btn btn-sm btn-outline-danger">{{ __('archive.view_all') }}</a>
            </div>
            <div class="archive-card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($incomingTransfers as $doc)
                        <a href="{{ route('document.show', $doc) }}" class="list-group-item list-group-item-action">
                            <div class="fw-semibold">{{ $doc->file_name }}</div>
                            <small class="text-archive-muted">{{ __('archive.from') }} {{ $doc->transfers->first()?->fromDepartment?->dep_name }} · {{ $doc->created_at->diffForHumans() }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="archive-card h-100">
                <div class="archive-card-header">
                    <h5><i class="bi bi-file-earmark me-2"></i>{{ __('archive.recent_documents') }}</h5>
                    <a href="{{ route('manageFile') }}" class="btn btn-sm btn-outline-secondary">{{ __('archive.view_all') }}</a>
                </div>
                <div class="archive-card-body p-0">
                    @if($recentDocuments->isEmpty())
                        <div class="p-4"><x-empty-state icon="bi-file-earmark" :title="__('archive.no_documents')" :message="__('archive.no_documents_desc')" /></div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentDocuments as $doc)
                                <a href="{{ route('document.show', $doc) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ $doc->file_name }}</div>
                                        <small class="text-archive-muted">{{ $doc->folder?->folder_name }} · {{ $doc->created_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge text-bg-light border">{{ archive_status_label($doc->status_id) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($isManager)
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
                                        <small class="text-archive-muted">{{ $doc->user?->name }} · {{ $doc->created_at->diffForHumans() }}</small>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="col-lg-6">
                <div class="archive-card h-100">
                    <div class="archive-card-header">
                        <h5><i class="bi bi-exclamation-triangle me-2 text-warning"></i>{{ __('archive.expiring_soon') }}</h5>
                    </div>
                    <div class="archive-card-body p-0">
                        @if($expiringDocuments->isEmpty())
                            <div class="p-4 text-center text-archive-muted">{{ __('archive.no_expiring') }}</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($expiringDocuments as $doc)
                                    <a href="{{ route('document.show', $doc) }}" class="list-group-item list-group-item-action">
                                        <div class="fw-semibold">{{ $doc->file_name }}</div>
                                        <small class="text-archive-muted">{{ __('archive.expiry_date') }}: {{ $doc->expiry_date?->format('Y-m-d') }}</small>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
