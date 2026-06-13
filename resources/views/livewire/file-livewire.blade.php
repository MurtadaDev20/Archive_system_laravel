<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="archive-card position-relative" wire:loading.class="opacity-75">
            <div wire:loading.flex wire:target="save,attachedFiles" class="livewire-loading-overlay">
                <div class="spinner-border text-success" role="status"><span class="visually-hidden">{{ __('archive.uploading') }}</span></div>
            </div>
            <div class="archive-card-header">
                <h5><i class="bi bi-cloud-upload me-2"></i>{{ __('archive.upload_new') }}</h5>
            </div>
            <div class="archive-card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                {{ __('archive.document_title') }}
                                @if(empty($attachedFiles) || count($attachedFiles) <= 1)
                                    <span class="text-danger">*</span>
                                @else
                                    <span class="text-archive-muted small">({{ __('archive.optional_batch_prefix') }})</span>
                                @endif
                            </label>
                            <input wire:model="fileName" type="text" class="form-control" placeholder="{{ __('archive.document_title_placeholder') }}">
                            @error('fileName') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('archive.target_folder') }} <span class="text-danger">*</span></label>
                            <select wire:model="selectFolder" class="form-select">
                                <option value="">{{ __('archive.select_folder') }}</option>
                                @foreach ($folders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->folder_name }}</option>
                                @endforeach
                            </select>
                            @error('selectFolder') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('archive.category') }} <span class="text-danger">*</span></label>
                            <select wire:model="categoryId" class="form-select" required>
                                <option value="">{{ __('archive.select_category') }}</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('categoryId') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('archive.document_type') }} <span class="text-danger">*</span></label>
                            <select wire:model="documentTypeId" class="form-select" required>
                                <option value="">{{ __('archive.select_type') }}</option>
                                @foreach($documentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('documentTypeId') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('archive.expiry_date') }}</label>
                            <input wire:model="expiryDate" type="date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('archive.description') }}</label>
                            <textarea wire:model="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('archive.tags') }}</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($tags as $tag)
                                    <label class="form-check form-check-inline">
                                        <input type="checkbox" wire:model="tagIds" value="{{ $tag->id }}" class="form-check-input">
                                        <span class="form-check-label">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('archive.file_attachment') }} <span class="text-danger">*</span></label>
                        <input wire:model="attachedFiles" type="file" class="form-control" multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <div class="form-text">{{ __('archive.multiple_files_hint') }}</div>
                        @error('attachedFiles') <div class="text-danger small">{{ $message }}</div> @enderror
                        @error('attachedFiles.*') <div class="text-danger small">{{ $message }}</div> @enderror

                        @if(is_array($attachedFiles) && count($attachedFiles) > 0)
                            <div class="mt-3 border rounded p-3 bg-light">
                                <div class="fw-semibold small mb-2">
                                    <i class="bi bi-files me-1"></i>
                                    {{ __('archive.selected_files', ['count' => count($attachedFiles)]) }}
                                </div>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($attachedFiles as $i => $f)
                                        <li class="d-flex align-items-center gap-2 py-1 border-bottom">
                                            <i class="bi bi-file-earmark text-success"></i>
                                            <span>{{ $f->getClientOriginalName() }}</span>
                                            <span class="text-archive-muted">({{ number_format($f->getSize() / 1024, 1) }} KB)</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{ __('archive.notes') }}</label>
                        <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-archive-accent" wire:loading.attr="disabled">
                            <i class="bi bi-cloud-upload me-1"></i>
                            @if(is_array($attachedFiles) && count($attachedFiles) > 1)
                                {{ __('archive.upload_documents_count', ['count' => count($attachedFiles)]) }}
                            @else
                                {{ __('archive.upload_document') }}
                            @endif
                        </button>
                        <a href="{{ route('manageFile') }}" class="btn btn-outline-secondary">{{ __('archive.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
