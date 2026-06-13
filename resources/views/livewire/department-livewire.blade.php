<div>
    <div class="archive-card mb-4">
        <div class="archive-card-header">
            <h5><i class="bi bi-building-add me-2"></i>{{ $editMode ? __('archive.edit_department') : __('archive.create_department') }}</h5>
        </div>
        <div class="archive-card-body">
            <form wire:submit.prevent="{{ $editMode ? 'updateDepartment' : 'addDepartment' }}">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">{{ __('archive.department_name') }}</label>
                        <input wire:model="department" type="text" class="form-control">
                        @error('department') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">{{ __('archive.department_manager') }}</label>
                        <select wire:model="selectedManager" class="form-select">
                            <option value="">{{ __('archive.select_manager') }}</option>
                            @foreach ($managerSelect as $manager)
                                @if ($manager->role_id == 3)
                                    <option value="{{ $manager->user_id }}">{{ $manager->users->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('selectedManager') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        @if($editMode)
                            <button type="submit" class="btn btn-archive-accent w-100">{{ __('archive.update') }}</button>
                            <button type="button" wire:click="cancelUpdate" class="btn btn-outline-secondary">{{ __('archive.cancel') }}</button>
                        @else
                            <button type="submit" class="btn btn-archive-accent w-100">{{ __('archive.add') }}</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($departments->isEmpty())
        <x-empty-state icon="bi-building-x" :title="__('archive.no_departments')" :message="__('archive.no_departments_desc')" />
    @else
        <div class="row g-3">
            @foreach($departments as $department)
                <div class="col-md-6 col-xl-4" wire:key="dept-{{ $department->id }}">
                    <div class="dept-card">
                        <h5 class="fw-bold mb-2">{{ $department->dep_name }}</h5>
                        <p class="small text-archive-muted mb-1">{{ __('archive.created_by') }}: {{ $department->user->name }}</p>
                        <p class="small text-archive-muted mb-3">{{ __('archive.manager') }}: {{ $department->manager?->name ?? __('archive.not_available') }}</p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="dept-stat">
                                    <div class="value text-success">{{ $department->folders->count() }}</div>
                                    <div class="label">{{ __('archive.folders_count') }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="dept-stat">
                                    <div class="value text-danger">{{ $department->files->count() }}</div>
                                    <div class="label">{{ __('archive.files_count') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button wire:click="editDepartment({{ $department->id }})" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil me-1"></i>{{ __('archive.edit') }}
                            </button>
                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteDept{{ $department->id }}">
                                <i class="bi bi-trash me-1"></i>{{ __('archive.delete') }}
                            </button>
                        </div>
                        <div class="modal fade" id="deleteDept{{ $department->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('archive.delete_department') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">{{ __('archive.delete_department_confirm') }} <strong>{{ $department->dep_name }}</strong>? {{ __('archive.delete_department_note') }}</div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('archive.cancel') }}</button>
                                        <button wire:click="DepartmentDelete({{ $department->id }})" type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('archive.delete') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $departments->links() }}</div>
    @endif
</div>
