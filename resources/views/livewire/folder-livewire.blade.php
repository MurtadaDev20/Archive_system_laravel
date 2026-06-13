<div>
    @if(Auth::user()->hasRole('Manager'))
        <div class="archive-card mb-4">
            <div class="archive-card-header">
                <h5><i class="bi bi-folder-plus me-2"></i>{{ $editMode ? __('archive.edit_folder') : __('archive.create_folder') }}</h5>
            </div>
            <div class="archive-card-body">
                <form wire:submit.prevent="{{ $editMode ? 'updateFolder' : 'addFolder' }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">{{ __('archive.folder_name') }}</label>
                            <input wire:model="folder" type="text" class="form-control">
                            @error('folder') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            @if($editMode)
                                <button type="submit" class="btn btn-archive-accent">{{ __('archive.update') }}</button>
                                <button type="button" wire:click="cancelUpdate" class="btn btn-outline-secondary">{{ __('archive.cancel') }}</button>
                            @else
                                <button type="submit" class="btn btn-archive-accent w-100"><i class="bi bi-plus-lg me-1"></i>{{ __('archive.create') }}</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($folders->isEmpty())
        <x-empty-state icon="bi-folder-x" :title="__('archive.no_folders')" :message="__('archive.no_folders_desc')" />
    @else
        <div class="row g-3">
            @foreach($folders as $folder)
                @if (Auth::user()->id == $folder->user_id || Auth::user()->hasRole('Admin') || Auth::user()->manager_id == $folder->user_id)
                    <div class="col-md-6 col-xl-4" wire:key="folder-{{ $folder->id }}">
                        <div class="folder-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="folder-card-icon"><i class="bi bi-folder2"></i></div>
                                <a href="{{ route('manageFileShow', $folder->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>{{ __('archive.view_files') }}
                                </a>
                            </div>
                            <h6 class="fw-bold mb-1">{{ $folder->folder_name }}</h6>
                            <p class="small text-archive-muted mb-3">{{ __('archive.created_by') }}: {{ $folder->user->name }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="badge text-bg-light border">
                                    <i class="bi bi-file-earmark me-1"></i>{{ $folder->files->count() }} {{ __('archive.files_count_label') }}
                                </span>
                                @if (Auth::user()->id == $folder->user_id)
                                    <div class="d-flex gap-1">
                                        <button wire:click="editFolder({{ $folder->id }})" class="btn btn-light btn-icon btn-sm" title="{{ __('archive.edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-icon btn-sm" data-bs-toggle="modal" data-bs-target="#deleteFolder{{ $folder->id }}" title="{{ __('archive.delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="modal fade" id="deleteFolder{{ $folder->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('archive.delete_folder') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">{{ __('archive.delete_folder_confirm') }} <strong>{{ $folder->folder_name }}</strong>? {{ __('archive.cannot_undo') }}</div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('archive.cancel') }}</button>
                                                    <button wire:click="deleteFolder({{ $folder->id }})" type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('archive.delete') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="mt-4">{{ $folders->links() }}</div>
    @endif
</div>
