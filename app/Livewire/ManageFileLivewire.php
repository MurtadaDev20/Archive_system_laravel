<?php

namespace App\Livewire;

use App\Models\file;
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

    
    public function render()
    {
        $url = request()->url(); 
        $parts = explode('/', $url); 
        $id = end($parts);

        if (ctype_digit($id) && $id > 0) {
            $query = File::query()
                ->where('folder_id', $id);
        } else {
            $query = File::query();
        }

        $files = $query
            ->when($this->from, fn ($query, $from) => $query->where('created_at', '>=', $from))
            ->when($this->to, fn ($query, $to) => $query->where('created_at', '<=', $to))
            ->with('folder', 'user')
            ->when($this->searchByName, function ($query, $name) {
                $query->where('file_name', 'like', "%$name%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.manage-file-livewire', [
            'files' => $files,
        ]);
    }

    public function deleteFile($fileId)
    {
        $file = file::find($fileId);
        if ($file) {
            Storage::delete($file->file);
            $file->delete();
            return redirect()->to(route('manageFile'));
        }
    }
    public function renderId($folderID)
    {


        return view('livewire.manage-file-livewire', [
            'files' => file::query()
                ->when($this->from, fn ($query, $from) => $query->where('created_at', '>=', $from))
                ->when($this->to, fn ($query, $to) => $query->where('created_at', '<=', $to))
                ->with('folder', 'user')
                ->when($this->searchByName, function ($query, $name) {
                    $query->where('file_name', 'like', "%$name%");
                })

                ->orderBy('created_at', 'desc')
                ->where('folder_id', $folderID)
                ->where('folder_id', $folderID)
                ->paginate(10),
        ]);
    }
}
