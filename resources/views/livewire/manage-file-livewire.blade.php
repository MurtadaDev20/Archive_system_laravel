<div class="position-relative" wire:loading.class="opacity-75">
    <div wire:loading.flex class="livewire-loading-overlay">
        <div class="spinner-border text-success" role="status"><span class="visually-hidden">{{ __('archive.loading') }}</span></div>
    </div>

    <div class="archive-card mb-3">
        <div class="archive-card-body py-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="small text-archive-muted me-1">{{ __('archive.quick_filters') }}:</span>
                <button type="button" wire:click="$set('inboxFilter', '')"
                    class="btn btn-sm {{ $inboxFilter === '' ? 'btn-archive-accent' : 'btn-outline-secondary' }}">
                    {{ __('archive.all') }}
                </button>
                @if(($sidebarCounts['transfers'] ?? 0) > 0 || $inboxFilter === 'transfers')
                    <button type="button" wire:click="$set('inboxFilter', 'transfers')"
                        class="btn btn-sm {{ $inboxFilter === 'transfers' ? 'btn-archive-accent' : 'btn-outline-danger' }}">
                        <i class="bi bi-arrow-left-right me-1"></i>{{ __('archive.incoming_transfers') }}
                        <span class="badge bg-danger ms-1">{{ $sidebarCounts['transfers'] ?? 0 }}</span>
                    </button>
                @endif
                @if(Auth::user()->hasRole('Manager') && (($sidebarCounts['approvals'] ?? 0) > 0 || $inboxFilter === 'approvals'))
                    <button type="button" wire:click="$set('inboxFilter', 'approvals')"
                        class="btn btn-sm {{ $inboxFilter === 'approvals' ? 'btn-archive-accent' : 'btn-outline-warning' }}">
                        <i class="bi bi-hourglass-split me-1"></i>{{ __('archive.pending_approvals') }}
                        <span class="badge bg-warning text-dark ms-1">{{ $sidebarCounts['approvals'] ?? 0 }}</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="archive-card mb-4">
        <div class="archive-card-header">
            <h5><i class="bi bi-funnel me-2"></i>{{ __('archive.advanced_search') }}</h5>
            @can('create', \App\Models\File::class)
                <a href="{{ route('addFile') }}" class="btn btn-archive-accent btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>{{ __('archive.upload_document') }}
                </a>
            @endcan
        </div>
        <div class="archive-card-body">
            <div class="search-panel">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('archive.search') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input wire:model.live.debounce.400ms="searchByName" type="search" class="form-control" placeholder="{{ __('archive.search_edms_placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('archive.status') }}</label>
                        <select wire:model.live="statusFilter" class="form-select form-select-sm">
                            <option value="">{{ __('archive.all_statuses') }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('archive.department') }}</label>
                        <select wire:model.live="departmentFilter" class="form-select form-select-sm">
                            <option value="">{{ __('archive.all_departments') }}</option>
                            @foreach($departments as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->dep_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('archive.category') }}</label>
                        <select wire:model.live="categoryFilter" class="form-select form-select-sm">
                            <option value="">{{ __('archive.all_categories') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('archive.document_type') }}</label>
                        <select wire:model.live="documentTypeFilter" class="form-select form-select-sm">
                            <option value="">{{ __('archive.all_types') }}</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">{{ __('archive.tags') }}</label>
                        <select wire:model.live="tagFilter" class="form-select form-select-sm">
                            <option value="">{{ __('archive.all') }}</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('archive.from_date') }}</label>
                        <input wire:model.live.debounce.400ms="from" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('archive.to_date') }}</label>
                        <input wire:model.live.debounce.400ms="to" type="date" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="archive-card">
        <div class="archive-card-header">
            <h5><i class="bi bi-table me-2"></i>{{ __('archive.documents_list') }}</h5>
            <span class="badge text-bg-light border">{{ $files->total() }} {{ __('archive.total') }}</span>
        </div>
        <div class="archive-card-body p-0">
            @if($files->isEmpty())
                <x-empty-state
                    icon="bi-file-earmark-x"
                    :title="__('archive.no_documents')"
                    :message="__('archive.no_documents_desc')"
                    :actionLabel="Auth::user()->can('create', \App\Models\File::class) ? __('archive.upload_document') : null"
                    :actionUrl="Auth::user()->can('create', \App\Models\File::class) ? route('addFile') : null"
                />
            @else
                <div class="table-responsive">
                    <table class="table archive-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('archive.document') }}</th>
                                <th>{{ __('archive.document_number') }}</th>
                                <th>{{ __('archive.folder') }}</th>
                                <th>{{ __('archive.uploaded_by') }}</th>
                                <th>{{ __('archive.date') }}</th>
                                <th>{{ __('archive.status') }}</th>
                                <th class="text-end">{{ __('archive.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($files as $index => $file)
                                    <tr wire:key="file-row-{{ $file->id }}">
                                        <td>{{ $files->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $file->file_name }}</div>
                                            @if(in_array($file->id, $incomingIds, true))
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                                                    <i class="bi bi-arrow-left-right me-1"></i>{{ __('archive.incoming_transfer') }}
                                                </span>
                                            @endif
                                            @if($file->category)
                                                <small class="text-archive-muted">{{ $file->category->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-archive-muted" onclick="navigator.clipboard.writeText('{{ $file->document_number ?? $file->code }}'); toastr?.info('{{ __('archive.code_copied') }}');">
                                                <small><i class="bi bi-clipboard me-1"></i>{{ $file->document_number ?? $file->code }}</small>
                                            </button>
                                        </td>
                                        <td>{{ $file->folder->folder_name }}</td>
                                        <td>{{ $file->user->name }}</td>
                                        <td>
                                            <div>{{ $file->created_at->format('Y-m-d') }}</div>
                                            <small class="text-archive-muted">{{ $file->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill badge-status-{{ $file->status?->slug ?? 'draft' }}">{{ archive_status_label($file->status_id) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                                <a href="{{ route('document.show', $file->id) }}" class="btn btn-light btn-icon btn-sm" title="{{ __('archive.preview') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('document.qr.print', ['file' => $file->id, 'size' => 'label', 'print' => 1]) }}" target="_blank" class="btn btn-light btn-icon btn-sm" title="{{ __('archive.print_label') }}">
                                                    <i class="bi bi-qr-code"></i>
                                                </a>
                                                <button wire:click="downloadFile({{ $file->id }})" class="btn btn-light btn-icon btn-sm" title="{{ __('archive.download') }}">
                                                    <i class="bi bi-download"></i>
                                                </button>

                                                @foreach($actionMap[$file->id] ?? [] as $action)
                                                    <button wire:click="workflowFile({{ $file->id }}, '{{ $action['key'] }}')" class="btn btn-{{ $action['variant'] === 'danger' ? 'outline-danger' : ($action['variant'] === 'success' ? 'success' : 'archive-accent') }} btn-icon btn-sm" title="{{ $action['label'] }}">
                                                        <i class="bi {{ $action['icon'] }}"></i>
                                                    </button>
                                                @endforeach

                                                @if (Auth::user()->id == $file->user_id && ! in_array($file->status?->slug, ['approved', 'archived']))
                                                    <button class="btn btn-outline-danger btn-icon btn-sm" data-bs-toggle="modal" data-bs-target="#deleteFile{{ $file->id }}" title="{{ __('archive.delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <div class="modal fade" id="deleteFile{{ $file->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ __('archive.delete_document') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">{{ __('archive.delete_document_confirm') }} <strong>{{ $file->file_name }}</strong>?</div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('archive.cancel') }}</button>
                                                                    <button wire:click="deleteFile({{ $file->id }})" type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('archive.delete') }}</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">{{ $files->links() }}</div>
            @endif
        </div>
    </div>
</div>
