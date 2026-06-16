<div>
    <div class="archive-card mb-4">
        <div class="archive-card-header">
            <h5>
                <i class="bi bi-person-plus me-2"></i>
                @if($editMode)
                    {{ __('archive.edit_user') }}
                @elseif(Auth::user()->hasRole('Admin'))
                    {{ __('archive.add_user') }}
                @else
                    {{ __('archive.add_employee') }}
                @endif
            </h5>
        </div>
        <div class="archive-card-body">
            @if(Auth::user()->hasRole('Admin'))
                <form wire:submit.prevent="submitUserForm" wire:key="admin-user-form-{{ $editMode ? 'edit-'.$editUserId : 'add' }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ __('archive.full_name') }}</label>
                            <input wire:model="fullname" type="text" class="form-control">
                            @error('fullname') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ __('archive.email') }}</label>
                            <input wire:model="email" type="email" class="form-control">
                            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if(!$editMode)
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">{{ __('archive.password') }}</label>
                            <input wire:model="password" type="password" class="form-control">
                            @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">{{ __('archive.role') }}</label>
                            <select wire:model.live="roleSelected" class="form-select" @if($editMode) disabled @endif>
                                <option value="">{{ __('archive.select_role') }}</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ archive_role_label($role->name) }}</option>
                                @endforeach
                            </select>
                            @error('roleSelected') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if ($showUserMode)
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">{{ __('archive.department') }}</label>
                            <select wire:model="selectDepartment" class="form-select">
                                <option value="">{{ __('archive.select_department') }}</option>
                                @foreach ($departments as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->dep_name }}</option>
                                @endforeach
                            </select>
                            @error('selectDepartment') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            @if($editMode)
                                <button type="submit" class="btn btn-archive-accent">{{ __('archive.update') }}</button>
                                <button type="button" wire:click="cancelUpdate" class="btn btn-outline-secondary">{{ __('archive.cancel') }}</button>
                            @else
                                <button type="submit" class="btn btn-archive-accent w-100">{{ __('archive.add_user') }}</button>
                            @endif
                        </div>
                    </div>
                </form>
            @elseif(Auth::user()->hasRole('Manager'))
                <form wire:submit.prevent="submitUserForm" wire:key="manager-user-form-{{ $editMode ? 'edit-'.$editUserId : 'add' }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('archive.employee_name') }}</label>
                            <input wire:model="fullname_manager" type="text" class="form-control">
                            @error('fullname_manager') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('archive.email') }}</label>
                            <input wire:model="email_manager" type="email" class="form-control">
                            @error('email_manager') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if(!$editMode)
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">{{ __('archive.password') }}</label>
                            <input wire:model="password_manager" type="password" class="form-control">
                            @error('password_manager') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-archive-accent w-100">{{ __('archive.add_employee') }}</button>
                        </div>
                        @else
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-archive-accent">{{ __('archive.update') }}</button>
                            <button type="button" wire:click="cancelUpdate" class="btn btn-outline-secondary">{{ __('archive.cancel') }}</button>
                        </div>
                        @endif
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="archive-card">
        <div class="archive-card-header">
            <h5><i class="bi bi-people me-2"></i>{{ __('archive.users_list') }}</h5>
        </div>
        <div class="archive-card-body p-0">
            <div class="table-responsive">
                <table class="table archive-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('archive.full_name') }}</th>
                            <th>{{ __('archive.email') }}</th>
                            <th>{{ __('archive.role') }}</th>
                            <th>{{ __('archive.department') }}</th>
                            <th>{{ __('archive.joined') }}</th>
                            <th class="text-end">{{ __('archive.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowNum = 0; @endphp
                        @foreach ($users as $user)
                            @if ($user->role_id == 1) @continue @endif
                            @php
                                $canViewRow = Auth::user()->hasRole('Admin')
                                    || (Auth::user()->hasRole('Manager') && $user->users->department_id
                                        && in_array((int) $user->users->department_id, app(\App\Services\DepartmentScopeService::class)->managedDepartmentIds(Auth::user()), true));
                            @endphp
                            @if ($canViewRow)
                                @php $rowNum++; @endphp
                                <tr wire:key="user-{{ $user->users->id }}">
                                    <td>{{ $users->firstItem() + $rowNum - 1 }}</td>
                                    <td class="fw-semibold">{{ $user->users->name }}</td>
                                    <td>{{ $user->users->email }}</td>
                                    <td><span class="badge text-bg-light border">{{ archive_role_label($user->role->name) }}</span></td>
                                    <td>{{ $user->users->department?->dep_name ?? '—' }}</td>
                                    <td>{{ $user->users->created_at->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        @can('update', $user->users)
                                            <button wire:click="editUser({{ $user->users->id }})" class="btn btn-light btn-icon btn-sm" title="{{ __('archive.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @if(Auth::user()->hasRole('Admin'))
                                            <button class="btn btn-outline-danger btn-icon btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUser{{ $user->users->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <div class="modal fade" id="deleteUser{{ $user->users->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __('archive.delete_user') }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">{{ __('archive.delete_user_confirm') }} <strong>{{ $user->users->name }}</strong>?</div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('archive.cancel') }}</button>
                                                            <button wire:click="deleteUser({{ $user->users->id }})" type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('archive.delete') }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">{{ $users->links() }}</div>
        </div>
    </div>
</div>
