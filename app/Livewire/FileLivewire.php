<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\DocumentWorkflowLog;
use App\Models\File;
use App\Models\Folder;
use App\Models\RoleUser;
use App\Models\Status;
use App\Models\Tag;
use App\Notifications\DocumentUploadedNotification;
use App\Services\AuditLogger;
use App\Services\DocumentNumberService;
use App\Services\DocumentQrService;
use App\Services\DocumentStorageService;
use App\Services\DepartmentScopeService;
use App\Services\DocumentWorkflowService;
use App\Services\Ocr\DocumentOcrProcessor;
use App\Support\ArchiveNotifier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileLivewire extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public $fileName;
    public $description;
    public $attachedFiles = [];
    public $selectFolder;
    public $categoryId;
    public $documentTypeId;
    public $tagIds = [];
    public $expiryDate;
    public $notes;

    public function mount()
    {
        $this->authorize('create', File::class);
    }

    public function save(
        DocumentNumberService $numbers,
        DocumentStorageService $storage,
        DocumentWorkflowService $workflow,
        DocumentQrService $qr
    ) {
        $user = Auth::user();
        $fileCount = is_array($this->attachedFiles) ? count($this->attachedFiles) : 0;

        $rules = [
            'description' => 'nullable|string|max:2000',
            'selectFolder' => 'required|exists:folders,id',
            'attachedFiles' => 'required|array|min:1|max:30',
            'attachedFiles.*' => 'file|max:30240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'categoryId' => 'required|exists:categories,id',
            'documentTypeId' => 'required|exists:document_types,id',
            'expiryDate' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ];

        if ($fileCount === 1) {
            $rules['fileName'] = 'required|string|max:500';
        } else {
            $rules['fileName'] = 'nullable|string|max:500';
        }

        $this->validate($rules);

        $folder = Folder::findOrFail($this->selectFolder);
        $this->authorize('upload', $folder);

        $bulkUpload = $fileCount > 1;
        $created = [];
        foreach ($this->attachedFiles as $index => $upload) {
            $created[] = $this->persistDocument(
                $upload,
                $index,
                $fileCount,
                $user,
                $folder,
                $numbers,
                $storage,
                $workflow,
                $qr,
                $bulkUpload
            );
        }

        if ($bulkUpload) {
            $this->finalizeBatchUpload($created, $user, $folder);
        }

        $this->reset(['fileName', 'description', 'attachedFiles', 'selectFolder', 'categoryId', 'documentTypeId', 'tagIds', 'expiryDate', 'notes']);

        if (count($created) === 1) {
            toastr()->success(__('archive.msg_document_uploaded'));

            return redirect()->route('document.show', $created[0]);
        }

        toastr()->success(__('archive.msg_documents_uploaded', ['count' => count($created)]));

        return redirect()->route('manageFile');
    }

    protected function persistDocument(
        UploadedFile $upload,
        int $index,
        int $total,
        $user,
        Folder $folder,
        DocumentNumberService $numbers,
        DocumentStorageService $storage,
        DocumentWorkflowService $workflow,
        DocumentQrService $qr,
        bool $deferNotifications = false
    ): File {
        $roleUser = RoleUser::where('user_id', $user->id)->first();
        $title = $this->resolveDocumentTitle($upload, $index, $total);

        $scope = app(DepartmentScopeService::class);
        $isDeptManager = $scope->canManageDepartment($user, (int) $folder->dep_id);
        $statusId = $isDeptManager
            ? Status::idForSlug('approved')
            : Status::idForSlug('pending_approval');

        $file = File::create([
            'code' => 'ARC'.now()->format('YmdHis').Str::upper(Str::random(4)),
            'document_number' => $numbers->generate(),
            'file_name' => $title,
            'description' => $this->description,
            'folder_id' => $this->selectFolder,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'role_id' => $roleUser?->role_id,
            'dep_id' => $folder->dep_id,
            'category_id' => $this->categoryId ?: null,
            'document_type_id' => $this->documentTypeId ?: null,
            'status_id' => $statusId,
            'approved_by' => $isDeptManager ? $user->id : null,
            'approved_at' => $isDeptManager ? now() : null,
            'expiry_date' => $this->expiryDate ?: null,
            'notes' => $this->notes,
            'file' => 'pending',
            'current_version' => 1,
            'ocr_status' => File::OCR_PENDING,
            'ocr_languages' => config('ocr.languages'),
        ]);

        if ($isDeptManager) {
            DocumentWorkflowLog::create([
                'file_id' => $file->id,
                'from_status_id' => Status::idForSlug('pending_approval'),
                'to_status_id' => $statusId,
                'user_id' => $user->id,
                'comment' => __('archive.workflow_auto_approve_manager_upload'),
            ]);
        }

        $file->load('category');
        $path = $storage->store($file, $upload, $folder);
        $file->update(['file' => $path]);
        $qr->generate($file->fresh());

        DocumentVersion::create([
            'file_id' => $file->id,
            'version_number' => 1,
            'storage_path' => $path,
            'original_name' => $upload->getClientOriginalName(),
            'mime_type' => $upload->getMimeType(),
            'size' => $upload->getSize(),
            'uploaded_by' => $user->id,
            'change_notes' => 'النسخة الأولى',
        ]);

        if (! empty($this->tagIds)) {
            $file->tags()->sync($this->tagIds);
        }

        AuditLogger::log('document.create', __('archive.audit_file_upload', ['name' => $file->file_name]), $file, ['name' => $file->file_name]);

        if (! $deferNotifications) {
            $this->notifyDocumentUploaded($file, $user, $folder);
        }

        app(DocumentOcrProcessor::class)->queue($file);

        return $file;
    }

    protected function notifyDocumentUploaded(File $file, $user, Folder $folder): void
    {
        $managerId = app(DepartmentScopeService::class)->departmentManagerId((int) $folder->dep_id);

        if ($managerId && (int) $managerId !== (int) $user->id) {
            ArchiveNotifier::documentUploaded($file, $user);

            $manager = \App\Models\User::find($managerId);
            if ($manager) {
                Notification::send($manager, new DocumentUploadedNotification($file));
            }
        }
    }

    /** @param  File[]  $files */
    protected function finalizeBatchUpload(array $files, $user, Folder $folder): void
    {
        if (empty($files)) {
            return;
        }

        $count = count($files);
        $teamManagerId = app(DepartmentScopeService::class)->departmentManagerId((int) $folder->dep_id);

        if ($teamManagerId && (int) $teamManagerId !== (int) $user->id) {
            ArchiveNotifier::notifyTeam(
                $teamManagerId,
                [$teamManagerId],
                __('archive.realtime_documents_uploaded', [
                    'count' => $count,
                    'user' => $user->name,
                ]),
                'info',
                $files[0],
                $user->id
            );

            $manager = \App\Models\User::find($teamManagerId);
            if ($manager) {
                Notification::send($manager, new DocumentUploadedNotification($files[0]));
            }
        }
    }

    protected function resolveDocumentTitle(UploadedFile $upload, int $index, int $total): string
    {
        $basename = pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME);

        if ($total === 1 && filled($this->fileName)) {
            return $this->fileName;
        }

        if ($total > 1 && filled($this->fileName)) {
            return $this->fileName.' ('.($index + 1).')';
        }

        return $basename ?: $upload->getClientOriginalName();
    }

    public function render()
    {
        $user = Auth::user();
        $accessIds = app(\App\Services\DepartmentScopeService::class)->accessDepartmentIds($user);
        $folders = Folder::whereIn('dep_id', $accessIds)->orderBy('folder_name')->get();

        return view('livewire.file-livewire', [
            'folders' => $folders,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'documentTypes' => DocumentType::where('is_active', true)->orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }
}
