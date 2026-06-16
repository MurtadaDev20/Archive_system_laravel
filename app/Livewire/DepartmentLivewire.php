<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\RoleUser;
use App\Services\AuditLogger;
use App\Services\DepartmentScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Rule as LivewireRule;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentLivewire extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[LivewireRule('required')]
    public $department;

    public $editMode = false;
    public $editDepartmentId;
    public $managerSelect;

    #[LivewireRule('required')]
    public $selectedManager;

    public function mount()
    {
        $this->managerSelect = RoleUser::with('users', 'role')->get();
    }

    public function addDepartment()
    {
        $this->authorize('create', Department::class);
        $this->validate([
            'department' => ['required', Rule::unique('departments', 'dep_name')],
            'selectedManager' => ['required', Rule::unique('departments', 'manager_id')],
        ]);

        $user = Auth::user();
        $roleUser = RoleUser::where('user_id', $user->id)->first();

        $dept = Department::create([
            'dep_name' => $this->department,
            'user_id' => $user->id,
            'role_id' => $roleUser?->role_id,
            'manager_id' => $this->selectedManager,
        ]);

        $this->syncDepartmentManager($dept);

        AuditLogger::log(
            'department.create',
            __('archive.audit_department_create', ['name' => $dept->dep_name]),
            $dept,
            ['name' => $dept->dep_name]
        );
        toastr()->success(__('archive.msg_department_created'));
        $this->reset('department');
    }

    public function editDepartment(int $departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $this->authorize('update', $department);
        $this->editMode = true;
        $this->editDepartmentId = $departmentId;
        $this->department = $department->dep_name;
        $this->selectedManager = $department->manager_id ? (string) $department->manager_id : '';
    }

    public function updateDepartment()
    {
        $department = Department::findOrFail($this->editDepartmentId);
        $this->authorize('update', $department);

        $this->validate([
            'department' => [
                'required',
                Rule::unique('departments', 'dep_name')->ignore($this->editDepartmentId),
            ],
            'selectedManager' => [
                'required',
                Rule::unique('departments', 'manager_id')->ignore($this->editDepartmentId),
            ],
        ]);

        $oldManagerId = $department->manager_id;

        $department->update([
            'dep_name' => $this->department,
            'manager_id' => $this->selectedManager,
        ]);

        $this->syncDepartmentManager($department->fresh(), $oldManagerId);

        AuditLogger::log(
            'department.update',
            __('archive.audit_department_update', ['name' => $department->dep_name]),
            $department,
            ['name' => $department->dep_name]
        );
        toastr()->success(__('archive.msg_department_updated'));

        $this->editMode = false;
        $this->department = '';
        $this->selectedManager = '';
    }

    public function cancelUpdate()
    {
        $this->editMode = false;
        $this->department = '';
        $this->selectedManager = '';
    }

    public function DepartmentDelete(int $deptId)
    {
        $dept = Department::findOrFail($deptId);
        $this->authorize('delete', $dept);

        if ($dept->folders()->count() > 0) {
            toastr()->error(__('archive.msg_department_has_folders'));

            return;
        }

        AuditLogger::log(
            'department.delete',
            __('archive.audit_department_delete', ['name' => $dept->dep_name]),
            $dept,
            ['name' => $dept->dep_name]
        );
        $dept->delete();

        return redirect()->route('departments');
    }

    protected function syncDepartmentManager(Department $department, ?int $previousManagerId = null): void
    {
        if ($previousManagerId && (int) $previousManagerId !== (int) $department->manager_id) {
            DepartmentScopeService::clearUserCache((int) $previousManagerId);
        }

        if ($department->manager_id) {
            \App\Models\User::where('id', $department->manager_id)->update([
                'department_id' => $department->id,
            ]);

            \App\Models\User::where('department_id', $department->id)
                ->where('id', '!=', $department->manager_id)
                ->update(['manager_id' => $department->manager_id]);

            DepartmentScopeService::clearUserCache((int) $department->manager_id);
        }

        DepartmentScopeService::clearDepartmentCache((int) $department->id);
    }

    public function render()
    {
        return view('livewire.department-livewire', [
            'departments' => Department::with(['user', 'folders', 'files', 'manager'])->paginate(8),
        ]);
    }
}
