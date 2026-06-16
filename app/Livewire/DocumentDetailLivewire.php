<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\DocumentComment;
use App\Models\DocumentTransfer;
use App\Models\File;
use App\Services\AuditLogger;
use App\Services\DocumentInboxService;
use App\Services\DocumentQrService;
use App\Services\DocumentTransferService;
use App\Services\DocumentWorkflowService;
use App\Services\Ocr\DocumentOcrProcessor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DocumentDetailLivewire extends Component
{
    use AuthorizesRequests;

    protected $listeners = [
        'archive-refreshed' => 'refreshDocument',
    ];

    public File $document;
    public string $commentBody = '';
    public string $transferComment = '';
    public string $transferResponseComment = '';
    public string $workflowComment = '';
    public int $toDepartmentId = 0;
    public string $activeTab = 'info';

    public function mount(int $documentId, DocumentQrService $qr): void
    {
        $this->document = File::with([
            'folder', 'department', 'category', 'documentType', 'tags',
            'user', 'owner', 'approver', 'status',
            'versions.uploader', 'transfers.fromDepartment', 'transfers.toDepartment', 'transfers.fromUser',
            'workflowLogs.fromStatus', 'workflowLogs.toStatus', 'workflowLogs.user',
            'comments.user',
        ])->findOrFail($documentId);

        $this->authorize('view', $this->document);

        $qr->ensure($this->document->fresh());
        $this->document->refresh();

        AuditLogger::log('document.view', __('archive.audit_document_view', ['name' => $this->document->file_name]), $this->document, ['name' => $this->document->file_name]);
    }

    public function regenerateQr(DocumentQrService $qr): void
    {
        $this->authorize('view', $this->document);
        $qr->regenerate($this->document->fresh());
        $this->document->refresh();
        toastr()->success(__('archive.msg_qr_regenerated'));
    }

    public function reprocessOcr(DocumentOcrProcessor $ocr): void
    {
        $this->authorize('update', $this->document);

        if (! $this->document->supportsOcr()) {
            toastr()->warning(__('archive.ocr_not_supported'));

            return;
        }

        $ocr->queue($this->document, true);
        $this->document->refresh();
        toastr()->success(__('archive.ocr_reprocess_queued'));
    }

    public function addComment(): void
    {
        $this->validate(['commentBody' => 'required|string|max:2000']);
        DocumentComment::create([
            'file_id' => $this->document->id,
            'user_id' => Auth::id(),
            'body' => $this->commentBody,
        ]);
        $this->commentBody = '';
        $this->document->load('comments.user');
        toastr()->success(__('archive.msg_comment_added'));
    }

    public function sendTransfer(DocumentTransferService $transfers): void
    {
        $this->validate([
            'toDepartmentId' => 'required|exists:departments,id',
            'transferComment' => 'required|string|max:1000',
        ]);

        if ((int) $this->toDepartmentId === (int) $this->document->dep_id) {
            toastr()->error(__('archive.msg_transfer_same_department'));

            return;
        }

        $hasPending = $this->document->transfers()
            ->where('to_department_id', $this->toDepartmentId)
            ->whereIn('status', [DocumentTransfer::STATUS_SENT, DocumentTransfer::STATUS_RECEIVED])
            ->exists();

        if ($hasPending) {
            toastr()->error(__('archive.msg_transfer_already_pending'));

            return;
        }

        $department = \App\Models\Department::find($this->toDepartmentId);
        $transfers->send(
            $this->document,
            $this->toDepartmentId,
            $department?->manager_id,
            $this->transferComment
        );
        $this->reset(['transferComment', 'toDepartmentId']);
        $this->document->load('transfers.fromDepartment', 'transfers.toDepartment', 'comments.user');
        toastr()->success(__('archive.msg_transfer_sent'));
        $this->dispatch('archive-refreshed');
    }

    public function respondTransfer(int $transferId, string $action, DocumentTransferService $transfers): void
    {
        $transfer = DocumentTransfer::where('file_id', $this->document->id)->findOrFail($transferId);
        $this->authorize('respond', $transfer);

        if ($action === 'reject') {
            $this->validate(
                ['transferResponseComment' => 'required|string|min:3|max:1000'],
                [],
                ['transferResponseComment' => __('archive.reject_reason')]
            );
            $transfers->reject($transfer, $this->transferResponseComment);
            toastr()->success(__('archive.msg_transfer_rejected'));
        } else {
            $this->validate(['transferResponseComment' => 'nullable|string|max:1000']);
            $transfers->accept($transfer, $this->transferResponseComment ?: null);
            toastr()->success(__('archive.msg_transfer_accepted'));
        }

        $this->transferResponseComment = '';
        $this->document->refresh()->load(
            'transfers.fromDepartment',
            'transfers.toDepartment',
            'transfers.fromUser',
            'department',
            'folder',
            'status',
            'comments.user',
            'workflowLogs'
        );
        $this->dispatch('transferUpdated');
        $this->dispatch('archive-refreshed');
    }

    public function refreshDocument(): void
    {
        $this->document->refresh()->load([
            'folder', 'department', 'category', 'documentType', 'tags',
            'user', 'owner', 'approver', 'status',
            'transfers.fromDepartment', 'transfers.toDepartment', 'transfers.fromUser',
            'workflowLogs.fromStatus', 'workflowLogs.toStatus', 'workflowLogs.user',
            'comments.user',
        ]);
    }

    public function workflowAction(string $action, DocumentWorkflowService $workflow): void
    {
        $this->authorize('view', $this->document);

        try {
            $workflow->executeAction(
                $this->document,
                $action,
                Auth::user(),
                $this->workflowComment ?: null
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        $this->workflowComment = '';
        $this->document->refresh()->load('status', 'workflowLogs', 'approver');
        toastr()->success(__('archive.msg_workflow_updated'));
        $this->dispatch('transferUpdated');
        $this->dispatch('archive-refreshed');
    }

    /** @deprecated استخدم workflowAction */
    public function advanceWorkflow(DocumentWorkflowService $workflow): void
    {
        $this->authorize('approve', $this->document);
        $workflow->advance($this->document);
        $this->document->refresh()->load('status', 'workflowLogs');
        toastr()->success(__('archive.msg_workflow_advanced'));
    }

    public function render()
    {
        $auditLogs = AuditLog::where('auditable_type', File::class)
            ->where('auditable_id', $this->document->id)
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('livewire.document-detail-livewire', [
            'auditLogs' => $auditLogs,
            'departments' => \App\Models\Department::orderBy('dep_name')->get(),
            'inbox' => app(DocumentInboxService::class),
            'qrLabel' => app(DocumentQrService::class)->labelData($this->document),
            'workflow' => app(DocumentWorkflowService::class),
            'workflowActions' => app(DocumentWorkflowService::class)->availableActions($this->document, Auth::user()),
        ]);
    }
}
