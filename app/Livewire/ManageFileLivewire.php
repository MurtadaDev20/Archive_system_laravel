<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\DocumentType;
use App\Models\File;
use App\Models\Tag;
use App\Services\AuditLogger;
use App\Services\DocumentInboxService;
use App\Services\DocumentSearchService;
use App\Services\DocumentWorkflowService;
use App\Services\Ocr\DocumentOcrProcessor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ManageFileLivewire extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $from;
    public $to;
    public $searchByName;
    public $statusFilter = '';
    public $categoryFilter = '';
    public $departmentFilter = '';
    public $tagFilter = '';
    public $documentTypeFilter = '';
    public $inboxFilter = '';

    protected $listeners = [
        'archive-refreshed' => '$refresh',
        'transferUpdated' => '$refresh',
    ];

    public function mount()
    {
        $inbox = request()->query('inbox');
        if (in_array($inbox, ['transfers', 'approvals'], true)) {
            $this->inboxFilter = $inbox;
        }
    }

    public function downloadFile(int $fileId)
    {
        $file = File::with('folder')->findOrFail($fileId);
        $this->authorize('download', $file);
        AuditLogger::log('document.download', __('archive.audit_file_download', ['name' => $file->file_name]), $file, ['name' => $file->file_name]);

        return Storage::disk($file->resolveStorageDisk())->download($file->file, $file->file_name);
    }

    public function deleteFile(int $fileId)
    {
        $file = File::with('folder')->findOrFail($fileId);
        $this->authorize('delete', $file);
        Storage::disk($file->resolveStorageDisk())->delete($file->file);
        AuditLogger::log('document.delete', __('archive.audit_file_delete', ['name' => $file->file_name]), $file, ['name' => $file->file_name]);
        $file->delete();
        toastr()->success(__('archive.msg_document_deleted'));

        return redirect()->route('manageFile');
    }

    public function approvedFile(int $fileId, DocumentWorkflowService $workflow)
    {
        $file = File::with('folder', 'status')->findOrFail($fileId);
        $this->authorize('approve', $file);
        $workflow->executeAction($file, 'approve', Auth::user(), __('archive.workflow_action_approve'));
        toastr()->success(__('archive.msg_document_approved'));
    }

    public function rejectFile(int $fileId, DocumentWorkflowService $workflow)
    {
        $file = File::with('folder', 'status')->findOrFail($fileId);
        $this->authorize('reject', $file);
        $workflow->executeAction($file, 'reject', Auth::user(), __('archive.workflow_reject_from_list'));
        toastr()->success(__('archive.msg_document_rejected'));
    }

    public function workflowFile(int $fileId, string $action, DocumentWorkflowService $workflow)
    {
        $file = File::with('folder', 'status')->findOrFail($fileId);
        $this->authorize('view', $file);

        $comment = $action === 'reject' ? __('archive.workflow_reject_from_list') : null;
        $workflow->executeAction($file, $action, Auth::user(), $comment);
        toastr()->success(__('archive.msg_workflow_updated'));
    }

    public function reprocessOcr(int $fileId, DocumentOcrProcessor $ocr): void
    {
        $file = File::findOrFail($fileId);
        $this->authorize('update', $file);

        if (! $file->supportsOcr()) {
            toastr()->warning(__('archive.ocr_not_supported'));

            return;
        }

        $ocr->queue($file, true);
        toastr()->success(__('archive.ocr_reprocess_queued'));
    }

    public function updatingSearchByName()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingTagFilter()
    {
        $this->resetPage();
    }

    public function updatingDocumentTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingFrom()
    {
        $this->resetPage();
    }

    public function updatingTo()
    {
        $this->resetPage();
    }

    public function updatingInboxFilter()
    {
        $this->resetPage();
    }

    public function render(DocumentSearchService $search, DocumentInboxService $inbox)
    {
        $user = Auth::user();
        $folderId = request()->route('folder_id');
        $filters = [
            'user' => $user,
            'search' => $this->searchByName,
            'status_id' => $this->statusFilter ?: null,
            'category_id' => $this->categoryFilter ?: null,
            'dep_id' => $this->departmentFilter ?: null,
            'tag_id' => $this->tagFilter ?: null,
            'document_type_id' => $this->documentTypeFilter ?: null,
            'from' => $this->from,
            'to' => $this->to,
            'folder_id' => ($folderId && ctype_digit((string) $folderId)) ? $folderId : null,
            'inbox' => $this->inboxFilter ?: null,
        ];

        $files = $search->search($filters, 10);
        $workflow = app(DocumentWorkflowService::class);
        $actionMap = [];

        foreach ($files as $file) {
            $actionMap[$file->id] = $workflow->availableActions($file, $user);
        }

        return view('livewire.manage-file-livewire', [
            'files' => $files,
            'sidebarCounts' => $inbox->sidebarCounts($user),
            'incomingIds' => $search->incomingTransferFileIds($user),
            'actionMap' => $actionMap,
            'statuses' => $search->filterStatuses($user),
            'departments' => $search->filterDepartments($user),
            'categories' => Cache::remember('archive.taxonomy.categories', 600, fn () => Category::orderBy('name')->get(['id', 'name'])),
            'tags' => Cache::remember('archive.taxonomy.tags', 600, fn () => Tag::orderBy('name')->get(['id', 'name'])),
            'documentTypes' => Cache::remember('archive.taxonomy.document_types', 600, fn () => DocumentType::orderBy('name')->get(['id', 'name'])),
        ]);
    }
}
