<?php

namespace App\Livewire;

use App\Models\file;
use Livewire\Component;
use Livewire\WithPagination;

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

    public function viewFile($fileId)
    {
        $file = File::findOrFail($fileId);
        $filePath = storage_path('app/' . $file->file);
        $this->fileContent = file_get_contents($filePath);
    }

    public function render()
    {
        $url = request()->url(); // Get the current URL
        $parts = explode('/', $url); // Split the URL into parts
        $id = end($parts);

        // Check if $id is a positive integer
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
                ->paginate(10),
        ]);
    }
}
