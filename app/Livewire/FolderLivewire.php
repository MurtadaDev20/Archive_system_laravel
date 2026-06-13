<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Folder;
use App\Models\RoleUser;
use App\Services\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class FolderLivewire extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Rule('required|min:2|max:255')]
    public $folder;

    public $editFolderId;
    public $editMode = false;

    public function addFolder()
    {
        $this->authorize('create', Folder::class);
        $this->validate();

        $user = Auth::user();
        $department = Department::where('manager_id', $user->id)->first();

        if (! $department) {
            toastr()->error(__('archive.msg_folder_manager_only'));

            return;
        }

        $roleUser = RoleUser::where('user_id', $user->id)->first();
        $folder = Folder::create([
            'folder_name' => $this->folder,
            'dep_id' => $department->id,
            'user_id' => $user->id,
            'role_id' => $roleUser?->role_id,
        ]);

        Storage::disk('local')->makeDirectory("documents/{$folder->id}");
        AuditLogger::log(
            'folder.create',
            __('archive.audit_folder_create', ['name' => $folder->folder_name]),
            $folder,
            ['name' => $folder->folder_name]
        );

        toastr()->success(__('archive.msg_folder_created'));
        $this->reset('folder');
    }

    public function editFolder(int $folderId)
    {
        $folder = Folder::findOrFail($folderId);
        $this->authorize('update', $folder);
        $this->editMode = true;
        $this->editFolderId = $folderId;
        $this->folder = $folder->folder_name;
    }

    public function updateFolder()
    {
        $this->validate(['folder' => 'required|min:2|max:255']);
        $folder = Folder::findOrFail($this->editFolderId);
        $this->authorize('update', $folder);
        $folder->update(['folder_name' => $this->folder]);

        AuditLogger::log(
            'folder.update',
            __('archive.audit_folder_update', ['name' => $folder->folder_name]),
            $folder,
            ['name' => $folder->folder_name]
        );
        toastr()->success(__('archive.msg_folder_updated'));

        $this->editMode = false;
        $this->folder = '';
    }

    public function cancelUpdate()
    {
        $this->editMode = false;
        $this->folder = '';
    }

    public function deleteFolder(int $folderId)
    {
        $folder = Folder::findOrFail($folderId);
        $this->authorize('delete', $folder);

        if ($folder->files()->count() > 0) {
            toastr()->error(__('archive.msg_folder_has_files'));

            return;
        }

        AuditLogger::log(
            'folder.delete',
            __('archive.audit_folder_delete', ['name' => $folder->folder_name]),
            $folder,
            ['name' => $folder->folder_name]
        );
        $folder->delete();
        toastr()->success(__('archive.msg_folder_deleted'));

        return redirect()->route('folders');
    }

    public function render()
    {
        return view('livewire.folder-livewire', [
            'folders' => Folder::with(['user', 'files'])
                ->orderByDesc('created_at')
                ->paginate(12),
        ]);
    }
}
