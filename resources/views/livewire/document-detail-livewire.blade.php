<div>
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="archive-card">
                <div class="archive-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1">{{ $document->file_name }}</h5>
                        <small class="text-archive-muted">{{ __('archive.document_number') }}: {{ $document->document_number }} · {{ __('archive.code') }}: {{ $document->code }}</small>
                    </div>
                    <span class="badge text-bg-{{ $document->status?->color ?? 'secondary' }}">{{ $document->statusLabel() }}</span>
                </div>
                <div class="archive-card-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item"><button class="nav-link {{ $activeTab === 'info' ? 'active' : '' }}" wire:click="$set('activeTab','info')">{{ __('archive.tab_info') }}</button></li>
                        <li class="nav-item"><button class="nav-link {{ $activeTab === 'preview' ? 'active' : '' }}" wire:click="$set('activeTab','preview')">{{ __('archive.preview') }}</button></li>
                        <li class="nav-item"><button class="nav-link {{ $activeTab === 'versions' ? 'active' : '' }}" wire:click="$set('activeTab','versions')">{{ __('archive.tab_versions') }}</button></li>
                        <li class="nav-item"><button class="nav-link {{ $activeTab === 'workflow' ? 'active' : '' }}" wire:click="$set('activeTab','workflow')">{{ __('archive.tab_workflow') }}</button></li>
                        <li class="nav-item"><button class="nav-link {{ $activeTab === 'transfers' ? 'active' : '' }}" wire:click="$set('activeTab','transfers')">{{ __('archive.tab_transfers') }}</button></li>
                        <li class="nav-item"><button class="nav-link {{ $activeTab === 'comments' ? 'active' : '' }}" wire:click="$set('activeTab','comments')">{{ __('archive.tab_comments') }}</button></li>
                        <li class="nav-item"><button class="nav-link {{ $activeTab === 'audit' ? 'active' : '' }}" wire:click="$set('activeTab','audit')">{{ __('archive.audit_log') }}</button></li>
                        <li class="nav-item"><button class="nav-link {{ $activeTab === 'qr' ? 'active' : '' }}" wire:click="$set('activeTab','qr')"><i class="bi bi-qr-code me-1"></i>{{ __('archive.tab_qr') }}</button></li>
                    </ul>

                    @if($activeTab === 'info')
                        <div class="row g-3">
                            <div class="col-md-6"><strong>{{ __('archive.department') }}:</strong> {{ $document->department?->dep_name ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ __('archive.category') }}:</strong> {{ $document->category?->name ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ __('archive.document_type') }}:</strong> {{ $document->documentType?->name ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ __('archive.folder') }}:</strong> {{ $document->folder?->folder_name ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ __('archive.uploaded_by') }}:</strong> {{ $document->user?->name }}</div>
                            <div class="col-md-6"><strong>{{ __('archive.owner') }}:</strong> {{ $document->owner?->name ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ __('archive.approved_by') }}:</strong> {{ $document->approver?->name ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ __('archive.expiry_date') }}:</strong> {{ $document->expiry_date?->format('Y-m-d') ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ __('archive.archive_date') }}:</strong> {{ $document->archive_date?->format('Y-m-d') ?? '—' }}</div>
                            <div class="col-12"><strong>{{ __('archive.description') }}:</strong><p class="mb-0 mt-1">{{ $document->description ?: '—' }}</p></div>
                            <div class="col-12"><strong>{{ __('archive.tags') }}:</strong>
                                @forelse($document->tags as $tag)
                                    <span class="badge text-bg-light border me-1">{{ $tag->name }}</span>
                                @empty — @endforelse
                            </div>
                            <div class="col-12"><strong>{{ __('archive.notes') }}:</strong><p class="mb-0 mt-1">{{ $document->notes ?: '—' }}</p></div>
                        </div>
                    @elseif($activeTab === 'preview')
                        <iframe class="file-preview-frame w-100" style="min-height:520px;border:1px solid var(--archive-border);border-radius:.75rem;" src="{{ route('streamFile', $document) }}"></iframe>
                    @elseif($activeTab === 'versions')
                        <div class="table-responsive">
                            <table class="table archive-table">
                                <thead><tr><th>#</th><th>{{ __('archive.file_name') }}</th><th>{{ __('archive.uploaded_by') }}</th><th>{{ __('archive.date') }}</th><th></th></tr></thead>
                                <tbody>
                                    @foreach($document->versions as $version)
                                        <tr>
                                            <td>v{{ $version->version_number }}</td>
                                            <td>{{ $version->original_name }}</td>
                                            <td>{{ $version->uploader?->name ?? '—' }}</td>
                                            <td>{{ $version->created_at->format('Y-m-d H:i') }}</td>
                                            <td>@if($version->version_number === $document->current_version)<span class="badge text-bg-success">{{ __('archive.current') }}</span>@endif</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($activeTab === 'workflow')
                        <x-workflow-stepper :document="$document" :workflow="$workflow" />

                        @if(count($workflowActions))
                            <div class="border rounded p-3 mb-4 bg-light">
                                <h6 class="fw-semibold mb-2">{{ __('archive.workflow_available_actions') }}</h6>
                                <textarea wire:model="workflowComment" class="form-control form-control-sm mb-2" rows="2"
                                    placeholder="{{ __('archive.workflow_comment_placeholder') }}"></textarea>
                                @error('workflowComment') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($workflowActions as $action)
                                        <button type="button"
                                            wire:click="workflowAction('{{ $action['key'] }}')"
                                            class="btn btn-{{ $action['variant'] }} btn-sm">
                                            <i class="bi {{ $action['icon'] }} me-1"></i>{{ $action['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-archive-muted small mb-4">{{ __('archive.workflow_no_actions') }}</div>
                        @endif

                        <h6 class="fw-semibold mb-3">{{ __('archive.workflow_history') }}</h6>
                        @if($document->workflowLogs->isEmpty())
                            <x-empty-state icon="bi-diagram-3" :title="__('archive.workflow_no_history')" :message="__('archive.workflow_no_history_desc')" />
                        @else
                            <ul class="activity-timeline">
                                @foreach($document->workflowLogs as $log)
                                    <li>
                                        <div class="activity-content">
                                            <div class="activity-title">{{ $log->fromStatus?->label() ?? '—' }} → {{ $log->toStatus?->label() }}</div>
                                            <div class="activity-meta">{{ $log->user?->name ?? __('archive.system') }} · {{ $log->created_at->diffForHumans() }}</div>
                                            @if($log->comment)<p class="small mb-0">{{ $log->comment }}</p>@endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-semibold mb-2">{{ __('archive.workflow_status_guide') }}</h6>
                            <div class="row g-2 small">
                                @foreach($workflow->pipelineSlugs() as $slug)
                                    <div class="col-md-6">
                                        <div class="border rounded p-2 h-100">
                                            <strong>{{ archive_status_label(\App\Models\Status::idForSlug($slug)) }}</strong>
                                            <div class="text-archive-muted">{{ $workflow->descriptionFor($slug) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col-md-6">
                                    <div class="border rounded p-2 h-100 border-danger-subtle">
                                        <strong>{{ __('archive.rejected') }}</strong>
                                        <div class="text-archive-muted">{{ $workflow->descriptionFor('rejected') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($activeTab === 'transfers')
                        <div class="alert alert-light border small mb-3">
                            <i class="bi bi-info-circle me-1"></i>{{ __('archive.transfer_help') }}
                        </div>

                        @if(Auth::id() === $document->user_id || Auth::id() === $document->owner_id)
                            <form wire:submit.prevent="sendTransfer" class="row g-2 mb-4">
                                <div class="col-md-5">
                                    <select wire:model="toDepartmentId" class="form-select form-select-sm">
                                        <option value="">{{ __('archive.select_department') }}</option>
                                        @foreach($departments as $dept)
                                            @if($dept->id != $document->dep_id)
                                                <option value="{{ $dept->id }}">{{ $dept->dep_name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('toDepartmentId') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-5">
                                    <input wire:model="transferComment" class="form-control form-control-sm" placeholder="{{ __('archive.transfer_comment') }}">
                                    @error('transferComment') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-archive-accent btn-sm w-100">{{ __('archive.send_transfer') }}</button>
                                </div>
                            </form>
                        @endif

                        @forelse($document->transfers as $transfer)
                            <div class="border rounded p-3 mb-3 {{ $inbox->canRespondToTransfer(Auth::user(), $transfer) ? 'border-danger border-2 bg-danger-subtle' : '' }}">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div>
                                        <div class="fw-semibold">
                                            <i class="bi bi-arrow-left-right me-1"></i>
                                            {{ $transfer->fromDepartment?->dep_name }} → {{ $transfer->toDepartment?->dep_name }}
                                        </div>
                                        <small class="text-archive-muted">
                                            {{ __('archive.sent_by') }}: {{ $transfer->fromUser?->name ?? '—' }}
                                            · {{ $transfer->sent_at?->format('Y-m-d H:i') }}
                                        </small>
                                    </div>
                                    <span class="badge {{ in_array($transfer->status, ['accepted']) ? 'text-bg-success' : (in_array($transfer->status, ['rejected']) ? 'text-bg-danger' : 'text-bg-warning') }}">
                                        {{ archive_transfer_status_label($transfer->status) }}
                                    </span>
                                </div>

                                @if($transfer->comment)
                                    <div class="small mb-2"><strong>{{ __('archive.transfer_comment') }}:</strong> {{ $transfer->comment }}</div>
                                @endif

                                @if($transfer->response_comment)
                                    <div class="small mb-2 p-2 rounded bg-light border">
                                        <strong>{{ $transfer->status === 'rejected' ? __('archive.reject_reason') : __('archive.accept_note') }}:</strong>
                                        {{ $transfer->response_comment }}
                                    </div>
                                @endif

                                @if($transfer->status === 'accepted')
                                    <div class="small text-success"><i class="bi bi-check-circle me-1"></i>{{ __('archive.transfer_accept_result') }}</div>
                                @elseif($transfer->status === 'rejected')
                                    <div class="small text-danger"><i class="bi bi-x-circle me-1"></i>{{ __('archive.transfer_reject_result') }}</div>
                                @endif

                                @can('respond', $transfer)
                                    <div class="mt-3 pt-3 border-top">
                                        <label class="form-label small fw-semibold">{{ __('archive.response_note') }}</label>
                                        <textarea wire:model="transferResponseComment" class="form-control form-control-sm mb-2" rows="2"
                                            placeholder="{{ __('archive.response_note_placeholder') }}"></textarea>
                                        @error('transferResponseComment') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                                        <div class="d-flex gap-2">
                                            <button wire:click="respondTransfer({{ $transfer->id }}, 'accept')" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-lg me-1"></i>{{ __('archive.accept_transfer') }}
                                            </button>
                                            <button wire:click="respondTransfer({{ $transfer->id }}, 'reject')" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-x-lg me-1"></i>{{ __('archive.reject_transfer') }}
                                            </button>
                                        </div>
                                    </div>
                                @endcan
                            </div>
                        @empty
                            <x-empty-state icon="bi-arrow-left-right" :title="__('archive.no_transfers')" :message="__('archive.no_transfers_desc')" />
                        @endforelse
                    @elseif($activeTab === 'comments')
                        <form wire:submit.prevent="addComment" class="mb-3">
                            <textarea wire:model="commentBody" class="form-control mb-2" rows="3" placeholder="{{ __('archive.add_comment') }}"></textarea>
                            <button type="submit" class="btn btn-archive-accent btn-sm">{{ __('archive.add_comment') }}</button>
                        </form>
                        @foreach($document->comments as $comment)
                            <div class="border-bottom py-2">
                                <strong>{{ $comment->user?->name }}</strong>
                                <small class="text-archive-muted"> · {{ $comment->created_at->diffForHumans() }}</small>
                                <p class="mb-0">{{ $comment->body }}</p>
                            </div>
                        @endforeach
                    @elseif($activeTab === 'audit')
                        <ul class="activity-timeline">
                            @foreach($auditLogs as $log)
                                <li>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ archive_audit_description($log) }}</div>
                                        <div class="activity-meta">{{ $log->user?->name ?? __('archive.system') }} · {{ $log->created_at->format('Y-m-d H:i') }} · {{ $log->ip_address }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @elseif($activeTab === 'qr')
                        <div class="alert alert-light border small mb-3">
                            <i class="bi bi-info-circle me-1"></i>{{ __('archive.qr_tab_help') }}
                        </div>
                        <div class="row g-4">
                            <div class="col-md-5 text-center">
                                @if($document->qrCodeUrl())
                                    <img src="{{ $document->qrCodeUrl() }}" alt="{{ __('archive.qr_code') }}" class="img-fluid border rounded p-3 bg-white shadow-sm" style="max-width:240px">
                                    <div class="small text-archive-muted mt-2">{{ __('archive.qr_scan_hint') }}</div>
                                @else
                                    <x-empty-state icon="bi-qr-code" :title="__('archive.qr_not_found')" :message="__('archive.qr_generate_hint')" />
                                @endif
                            </div>
                            <div class="col-md-7">
                                <h6 class="fw-semibold mb-3">{{ __('archive.qr_label_details') }}</h6>
                                <dl class="row small mb-4">
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.document_number') }}</dt>
                                    <dd class="col-sm-8 fw-semibold">{{ $qrLabel['document_number'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.file_name') }}</dt>
                                    <dd class="col-sm-8">{{ $qrLabel['title'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.department') }}</dt>
                                    <dd class="col-sm-8">{{ $qrLabel['department'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.category') }}</dt>
                                    <dd class="col-sm-8">{{ $qrLabel['category'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.document_type') }}</dt>
                                    <dd class="col-sm-8">{{ $qrLabel['document_type'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.folder') }}</dt>
                                    <dd class="col-sm-8">{{ $qrLabel['folder'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.archive_date') }}</dt>
                                    <dd class="col-sm-8">{{ $qrLabel['archive_date'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.status') }}</dt>
                                    <dd class="col-sm-8">{{ $qrLabel['status'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.uploaded_by') }}</dt>
                                    <dd class="col-sm-8">{{ $qrLabel['uploaded_by'] }}</dd>
                                    <dt class="col-sm-4 text-archive-muted">{{ __('archive.qr_link') }}</dt>
                                    <dd class="col-sm-8"><a href="{{ $qrLabel['url'] }}" class="text-break">{{ $qrLabel['url'] }}</a></dd>
                                </dl>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ $document->qrPrintUrl('label') }}" target="_blank" class="btn btn-archive-accent btn-sm">
                                        <i class="bi bi-printer me-1"></i>{{ __('archive.print_label') }}
                                    </a>
                                    <a href="{{ $document->qrPrintUrl('a4') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-file-earmark me-1"></i>{{ __('archive.print_a4_label') }}
                                    </a>
                                    @if($document->qrDownloadUrl())
                                        <a href="{{ $document->qrDownloadUrl() }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-download me-1"></i>{{ __('archive.download_qr') }}
                                        </a>
                                    @endif
                                    <button wire:click="regenerateQr" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>{{ __('archive.regenerate_qr') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="archive-card mb-3">
                <div class="archive-card-header"><h6 class="mb-0">{{ __('archive.quick_actions') }}</h6></div>
                <div class="archive-card-body d-grid gap-2">
                    <a href="{{ route('streamFile', $document) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>{{ __('archive.download') }}</a>
                    <a href="{{ route('manageFile') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right me-1"></i>{{ __('archive.back') }}</a>
                </div>
            </div>
            @if($document->qrCodeUrl())
                <div class="archive-card">
                    <div class="archive-card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-qr-code me-1"></i>{{ __('archive.qr_code') }}</h6>
                        <button type="button" class="btn btn-link btn-sm p-0" wire:click="$set('activeTab','qr')">{{ __('archive.view_all') }}</button>
                    </div>
                    <div class="archive-card-body text-center">
                        <img src="{{ $document->qrCodeUrl() }}" alt="{{ __('archive.qr_code') }}" class="img-fluid border rounded p-2 bg-white" style="max-width:160px">
                        <div class="fw-semibold small mt-2">{{ $document->document_number }}</div>
                        <div class="small text-archive-muted text-truncate">{{ $document->file_name }}</div>
                        <div class="d-grid gap-1 mt-3">
                            <a href="{{ $document->qrPrintUrl('label') }}" target="_blank" class="btn btn-archive-accent btn-sm">
                                <i class="bi bi-printer me-1"></i>{{ __('archive.print_label') }}
                            </a>
                            <a href="{{ $document->qrPrintUrl('a4') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                {{ __('archive.print_a4_label') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
