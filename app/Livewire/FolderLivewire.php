<?php

namespace App\Livewire;

use App\Models\department;
use App\Models\folder;
use App\Models\RoleUser;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class FolderLivewire extends Component
{
    use WithPagination;
    #[Rule('required|unique:folders,folder_name')]
    public $folder;
    public $folderse;
    public $department;
    public $user_id;
    public $editFolderId;
    public $role_id;
    public $editMode = false;
    

    
    public function addFolder()
    {
        $this->validate();
        $user = Auth::user();

        if ($user) {
            $department  = department::where('manager_id', Auth::user()->id)->first();
            if ($department) {
                $dep_id = $department->id;

                $roleUser = RoleUser::where('user_id', $user->id)->first();
                folder::create([
                    'folder_name' => $this->folder,
                    'dep_id' => $dep_id ,
                    'user_id' => $user->id,
                    'role_id' => $roleUser->role_id,
                ]);

                toastr()->success('Data has been saved successfully!');

                $this->reset('folder');
                $this->folderse = folder::all();

                // $this->emit('departmentAdded');
            }
            else{
                toastr()->error('You Are Not Manager');
            }
            

        }
    }

    ///////////////////////////////////////// Update //////////////////////////////

    public function editFolder($folderId)
    {
        $folder = folder::find($folderId);

        $this->editMode = true;
        $this->editFolderId = $folderId;
        $this->folder = $folder->folder_name;
        
        // dd($department->manager_id);
    }

    public function updateFolder()
    {
        $this->validate(['folder' => 'required']);

        $folder = folder::find($this->editFolderId);
        $folder->folder_name = $this->folder;
        $folder->save();

        $this->editMode = false;
        $this->folder = '';
        $this->folder = folder::all();
    }

    public function cancelUpdate()
    {
        $this->editMode = false;
        $this->folder = '';
    }

    public function deleteFolder($folderId)
    {
        $folder = folder::find($folderId);

        if ($folder) {
            // Check if the folder contains any files
            if ($folder->files()->count() > 0) {
                toastr()->error('Cannot delete folder. It contains files.');
                return redirect()->to(route('folders'));
                
                
            }
    
            $folder->delete();
            toastr()->success('Delete successfully!');
            return redirect()->to(route('folders'));
        }

        
    }

    public function render()
    {

        return view('livewire.folder-livewire',[
            'folders' => folder::with('user', 'role','files')
            ->orderBy('created_at', 'desc')
            ->paginate(16),
        ]);
    }
}
