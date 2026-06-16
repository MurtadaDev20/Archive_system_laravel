<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DepartmentScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Userslivewire extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $fullname;
    public $fullname_manager;
    public $email;
    public $email_manager;
    public $password;
    public $password_manager;
    public $selectManager;
    public $selectDepartment;
    public $roleSelected;
    public $showUserMode = false;
    public $editMode = false;
    public $editUserId;

    public function addUser()
    {
        $this->authorize('create', User::class);
        $this->validate([
            'fullname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'roleSelected' => 'required',
            'selectDepartment' => Rule::requiredIf($this->roleSelected === '4'),
        ]);

        $departmentId = $this->roleSelected === '4' ? (int) $this->selectDepartment : null;
        $managerId = null;

        if ($departmentId) {
            $managerId = Department::where('id', $departmentId)->value('manager_id');
        }

        $user = User::create([
            'name' => $this->fullname,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'department_id' => $departmentId,
            'manager_id' => $managerId,
        ]);

        RoleUser::create(['user_id' => $user->id, 'role_id' => $this->roleSelected]);
        AuditLogger::log(
            'user.create',
            __('archive.audit_user_create', ['email' => $user->email]),
            $user,
            ['email' => $user->email]
        );

        if ($departmentId) {
            DepartmentScopeService::clearDepartmentCache($departmentId);
        }

        $this->resetForm();
        toastr()->success(__('archive.msg_user_created'));
    }

    public function addUserByManager()
    {
        $this->authorize('createEmployee', User::class);
        $this->validate([
            'fullname_manager' => 'required',
            'email_manager' => 'required|email|unique:users,email',
            'password_manager' => 'required|min:8',
        ]);

        $managedIds = app(DepartmentScopeService::class)->managedDepartmentIds(Auth::user());
        $departmentId = $managedIds[0] ?? Auth::user()->department_id;

        if (! $departmentId) {
            toastr()->error(__('archive.msg_no_managed_department'));

            return;
        }

        $user = User::create([
            'name' => $this->fullname_manager,
            'email' => $this->email_manager,
            'manager_id' => Auth::id(),
            'department_id' => $departmentId,
            'password' => Hash::make($this->password_manager),
        ]);

        RoleUser::create(['user_id' => $user->id, 'role_id' => 4]);
        DepartmentScopeService::clearDepartmentCache((int) $departmentId);
        AuditLogger::log(
            'user.create.employee',
            __('archive.audit_user_create_employee', ['email' => $user->email]),
            $user,
            ['email' => $user->email]
        );

        $this->reset(['fullname_manager', 'email_manager', 'password_manager']);
        toastr()->success(__('archive.msg_employee_created'));
    }

    public function editUser(int $userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('update', $user);

        $roleUser = RoleUser::where('user_id', $userId)->first();

        $this->editUserId = $userId;
        $this->editMode = true;
        $this->roleSelected = $roleUser ? (string) $roleUser->role_id : '';
        $this->selectManager = $user->manager_id ? (string) $user->manager_id : '';
        $this->selectDepartment = $user->department_id ? (string) $user->department_id : '';
        $this->showUserMode = $this->roleSelected === '4';

        if (Auth::user()->hasRole('Manager')) {
            $this->fullname_manager = $user->name;
            $this->email_manager = $user->email;
        } else {
            $this->fullname = $user->name;
            $this->email = $user->email;
        }
    }

    public function submitUserForm()
    {
        if ($this->editMode) {
            $this->updateUser();
        } elseif (Auth::user()->hasRole('Admin')) {
            $this->addUser();
        } else {
            $this->addUserByManager();
        }
    }

    public function updateUser()
    {
        $user = User::findOrFail($this->editUserId);
        $this->authorize('update', $user);

        $isManagerEditing = Auth::user()->hasRole('Manager') && ! Auth::user()->hasRole('Admin');

        if ($isManagerEditing) {
            $this->validate([
                'fullname_manager' => 'required',
                'email_manager' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editUserId)],
            ]);

            $user->update([
                'name' => $this->fullname_manager,
                'email' => $this->email_manager,
            ]);
        } else {
            $this->validate([
                'fullname' => 'required',
                'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editUserId)],
                'selectDepartment' => Rule::requiredIf($this->roleSelected === '4'),
            ]);

            $data = [
                'name' => $this->fullname,
                'email' => $this->email,
            ];

            if ($this->roleSelected === '4' && $this->selectDepartment) {
                $departmentId = (int) $this->selectDepartment;
                $data['department_id'] = $departmentId;
                $data['manager_id'] = Department::where('id', $departmentId)->value('manager_id');
            }

            $user->update($data);

            if (! empty($data['department_id'])) {
                DepartmentScopeService::clearDepartmentCache((int) $data['department_id']);
            }
        }

        AuditLogger::log(
            'user.update',
            __('archive.audit_user_update', ['email' => $user->email]),
            $user,
            ['email' => $user->email]
        );

        $this->resetForm();
        toastr()->success(__('archive.msg_user_updated'));
    }

    public function cancelUpdate()
    {
        $this->resetForm();
    }

    public function deleteUser(int $userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('delete', $user);
        AuditLogger::log(
            'user.delete',
            __('archive.audit_user_delete', ['email' => $user->email]),
            $user,
            ['email' => $user->email]
        );
        $user->delete();

        return redirect()->route('allUsers');
    }

    private function resetForm(): void
    {
        $this->editMode = false;
        $this->editUserId = null;
        $this->showUserMode = false;
        $this->reset([
            'fullname',
            'email',
            'password',
            'roleSelected',
            'selectManager',
            'selectDepartment',
            'fullname_manager',
            'email_manager',
            'password_manager',
        ]);
    }

    public function render()
    {
        $this->showUserMode = $this->roleSelected == '4';

        $user = Auth::user();
        $query = RoleUser::with(['role', 'users.manager', 'users.department'])
            ->orderByDesc('created_at');

        if ($user->hasRole('Manager') && ! $user->hasRole('Admin')) {
            $managedIds = app(DepartmentScopeService::class)->managedDepartmentIds($user);
            $query->whereHas('users', fn ($q) => $q->whereIn('department_id', $managedIds ?: [0]));
        }

        return view('livewire.userslivewire', [
            'roles' => Role::all(),
            'departments' => Department::orderBy('dep_name')->get(['id', 'dep_name']),
            'manager' => RoleUser::with('role', 'users')->get(),
            'users' => $query->paginate(10),
        ]);
    }
}
