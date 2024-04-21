<?php
namespace App\Livewire;

use App\Models\department;
use App\Models\folder;
use App\Models\RoleUser;
use App\Models\User;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;


class DepartmentLivewire extends Component
{
    use WithPagination;
    #[Rule('required|unique:departments,dep_name')]
    public $department;
    public $user_id;
    public $role_id;
    public $departmentse;
    public $departmentsPagenate;
    public $manager_name;
    public $editMode = false;
    public $editDepartmentId;
    public $managerSelect;

    
    #[Rule('required|unique:departments,manager_id')]
    public $selectedManager;


   
    public function mount()
    {
        
        

        $this->managerSelect = RoleUser::with('users', 'role')->get();
    }
   

    public function addDepartment()
    {
        $this->validate();
        $user = Auth::user();

        if ($user) {
            $roleUser = RoleUser::where('user_id', $user->id)->first();
            Department::create([
                'dep_name' => $this->department,
                'user_id' => $user->id,
                'role_id' => $roleUser->role_id,
                'manager_id' =>$this->selectedManager
            ]);

            toastr()->success('Data has been saved successfully!');

            $this->reset('department');
            $this->departmentse = Department::all();

            // $this->emit('departmentAdded');
        }
    }



///////////////////////////////////////// Update //////////////////////////////

    public function editDepartment($departmentId)
    {
        $department = Department::find($departmentId);

        $this->editMode = true;
        $this->editDepartmentId = $departmentId;
        $this->department = $department->dep_name;
        
        // dd($department->manager_id);
    }

    public function updateDepartment()
    {
        $this->validate(['department' => 'required']);

        $department = Department::find($this->editDepartmentId);
        $department->dep_name = $this->department;
         $department->manager_id = $this->selectedManager;
        $department->save();

        $this->editMode = false;
        $this->department = '';
        $this->departmentse = Department::all();
    }

    public function deleteDepartment($departmentId)
    {
        Department::find($departmentId)->delete();
    }


    public function cancelUpdate()
    {
        $this->editMode = false;
        $this->department = '';
    }



    public function render()
    {
       
        //  dd($this->departments);
        return view('livewire.department-livewire',[
            'departments' => department::with('user', 'role','folders','files')->paginate(10),
        ]);
    }
   

}