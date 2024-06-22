<?php

namespace App\Livewire;

use App\Models\file;
use App\Models\folder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class ManageFileLivewire extends Component
{
    use WithPagination;

    // public $files;
    public $num = 1;
    public $from;
    public $to;
    public $searchByName;
    public $fileContent;

    public function downloadFile($fileId)
    {
        $file = File::findOrFail($fileId);
        $filePath = storage_path('app/' . $file->file);
        return response()->download($filePath);
    }

    public function viewPdf($fileId)
    {
        $file = File::findOrFail($fileId);
        $filePath = storage_path('app/' . $file->file);

        $this->fileContent = base64_encode(file_get_contents($filePath));
        // $this->emit('loadPdf', $this->fileContent);
    }

    public function deleteFile($fileId)
    {
        $file = file::find($fileId);
        if ($file) {
        // Construct the path relative to 'storage/app'
        $filePath = 'public/' . $file->file;

        // Delete the file from storage
        Storage::delete($filePath);
            $file->delete();
            return redirect()->to(route('manageFile'));
        }
    }

    public function render()
    {
        //used to get id folder
        $url = request()->url(); 
        $parts = explode('/', $url); 
        $id = end($parts);

        // used to filter between file filter by using dep_id becuse have same dep_id
        $user = Auth::user();
        if (ctype_digit($id) && $id > 0) 
        {
            $files = file::where('folder_id',$id)->orderByDesc('created_at');
        } 
        else 
            {
                $folder = folder::where('user_id', $user->manager_id)->orWhere('user_id', $user->id)->first();
                if($folder)
                {
                    $files = file::where('dep_id',$folder->dep_id)->orderByDesc('created_at');
                }
                
            }

        // search by using name or created at 
        if ($this->from) $files = $files->where('created_at', '>=', $this->from);
        if ($this->to) $files = $files->where('created_at', '<=', $this->to);
        if ($this->searchByName) {
            $files = $files->where('file_name', 'like', "%{$this->searchByName}%")
                ->orwhere('code', 'like', "%{$this->searchByName}%")
                ->orWhereHas('folder', function ($query) {$query->where('folder_name', 'like', "%{$this->searchByName}%");})
                ->orWhereHas('user', function ($query) {$query->where('name', 'like', "%{$this->searchByName}%");});
            }
        $files = $files->paginate(20);
        return view('livewire.manage-file-livewire', compact('files'));
    }

    

}
