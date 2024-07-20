<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Folder;
use App\Models\RoleUser;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;

class FolderLivewire extends Component
{
    use WithPagination;
    #[Rule('required')]
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
            $department  = Department::where('manager_id', Auth::user()->id)->first();
            if ($department) {
                $dep_id = $department->id;

                $roleUser = RoleUser::where('user_id', $user->id)->first();
                Folder::create([
                    'folder_name' => $this->folder,
                    'dep_id' => $dep_id ,
                    'user_id' => $user->id,
                    'role_id' => $roleUser->role_id,
                ]);
                // Create a folder in the storage directory with the name concatenated with the user's name

                $folderNameWithUser = $this->folder . '_' . $user->name;
                Storage::disk('public')->makeDirectory($folderNameWithUser);

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
        $folder = Folder::find($folderId);

        $this->editMode = true;
        $this->editFolderId = $folderId;
        $this->folder = $folder->folder_name;

        // dd($department->manager_id);
    }

    public function updateFolder()
    {
        $this->validate(['folder' => 'required']);

        $folder = Folder::find($this->editFolderId);
        $folder->folder_name = $this->folder;
        $folder->save();

        $this->editMode = false;
        $this->folder = '';
        $this->folder = Folder::all();
    }

    public function cancelUpdate()
    {
        $this->editMode = false;
        $this->folder = '';
    }

    public function deleteFolder($folderId)
    {
        $folder = Folder::find($folderId);

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
            'folders' => Folder::with('user', 'role','files')
            ->orderBy('created_at', 'desc')
            ->paginate(16),
        ]);
    }
}
