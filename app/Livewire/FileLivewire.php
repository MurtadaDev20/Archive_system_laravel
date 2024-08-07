<?php

namespace App\Livewire;

use App\Events\FileCreated;
use App\Models\department;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\Folder;
use App\Models\RoleUser;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;


class FileLivewire extends Component
{
    use WithFileUploads;

    public $fileName;
    public $folderName;
    public $attached;
    public $selectFolder;
    public $uploadProgress = 0;
    public $isUploading = true;

    public function mount()
    {

        $this->folderName = Folder::all();

    }

    public function save()
    {
        $user = Auth::user();
        // sleep(1);
        $this->validate([
            'fileName' => 'required',
            'selectFolder' => 'required|exists:folders,id',
            'attached' => 'required|max:30240', // Limit file size to 10MB (10240KB)
        ]);



        $filename = Str::uuid() . '.' . $this->attached->extension();
        $folder = Folder::find($this->selectFolder);
        $folderNameWithUser = $folder->folder_name . '_' . $user->name;
        $path = $this->attached->storeAs($folderNameWithUser, $filename ,'public');


        $department = Folder::find($this->selectFolder);
        $user = Auth::user();
        $roleUser = RoleUser::where('user_id', $user->id)->first();

        // create code per file
        $code = "ARC" . date('YmdHis');

        $file = File::create([
            'code' => $code,
            'file_name' => $this->fileName,
            'folder_id'=>$this->selectFolder ,
            'user_id'=>$user->id ,
            'role_id'=>$roleUser->role_id ,
            'dep_id' => $department->dep_id,
            'file' => $path,
            'status_id' => 2

        ]);

        // update table auto by pusher

        event(new FileCreated($file));



        $this->reset(['fileName', 'selectFolder', 'attached']);
        $this->uploadProgress = 0;
        $this->isUploading = false;

        toastr()->success('File uploaded successfully!');
        return redirect()->to(route('manageFile'));
    }
    public function render()
    {

        return view('livewire.file-livewire');
    }
}
