<?php

namespace App\Livewire;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Userslivewire extends Component
{

    public $fullname;
    public $email;
    public $password;
    public $selectManager;
    public $roleSelected;
    public $showUserMode = false;

    public function mount()
    { 
        
        
    }

    public function addUser()
    {
        $this->validate([
            'fullname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'roleSelected' => 'required',
        ]);

        if($this->selectManager > 0){
            $user = new User();
        $user->name = $this->fullname;
        $user->email = $this->email;
        $user->manager_id = $this->selectManager;
        $user->password = Hash::make($this->password);
        $user->save();

        $role_user = new RoleUser();
        $role_user->user_id = $user->id;
        $role_user->role_id = $this->roleSelected;
        $role_user->save();

        // Clear form fields
        $this->fullname = '';
        $this->email = '';
        $this->password = '';
        // $this->roleSelect = '';
        toastr()->success('User added successfully!');
        }
        else{
        $user = new User();
        $user->name = $this->fullname;
        $user->email = $this->email;
        $user->password = Hash::make($this->password);
        $user->save();

        $role_user = new RoleUser();
        $role_user->user_id = $user->id;
        $role_user->role_id = $this->roleSelected;
        $role_user->save();

        // Clear form fields
        $this->fullname = '';
        $this->email = '';
        $this->password = '';
        // $this->roleSelect = '';

        toastr()->success('User added successfully!');
        
        }
    }

    public function render()
    {
        if($this->roleSelected == "4")
        {
            $this->showUserMode = true;
        }else{
            $this->showUserMode = false;
        }

        $roles = Role::all();
        $manager = RoleUser::with('role','users')->get();
        return view('livewire.userslivewire',[
            
            'roles' => $roles,
            'manager' =>$manager
        ]);
    }
}
