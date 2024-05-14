<?php

namespace App\Livewire;

use App\Models\department;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\file;
use App\Models\folder;
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

        $this->folderName = folder::get();
        
    }
   
    public function save()
    {
        $this->validate([
            'fileName' => 'required',
            'folderName' => 'required',
            'attached' => 'required|max:30240', // Limit file size to 10MB (10240KB)
        ]);



        $filename = Str::uuid() . '.' . $this->attached->extension();
        $path = $this->attached->storeAs('uploads/public', $filename);


        $department = folder::where('id',$this->selectFolder)->first();
        // dd($department->dep_id);
        $user = Auth::user();
        $roleUser = RoleUser::where('user_id', $user->id)->first();
        

        
        file::create([
            'file_name' => $this->fileName,
            'folder_id'=>$this->selectFolder ,
            'user_id'=>$user->id ,
            'role_id'=>$roleUser->role_id ,
            'dep_id' => $department->dep_id,
            'file' => $path

        ]);
       

        
        $this->reset(['fileName', 'selectFolder', 'attached']);
        $this->uploadProgress = 0;
        $this->isUploading = false;

        toastr()->success('File uploaded successfully!');
        return redirect()->to(route('manageFile'));
    }
    public function render()
    {
        // $folder = folder::get();
        
        // dd($department->dep_id);
        return view('livewire.file-livewire');
    }
}
