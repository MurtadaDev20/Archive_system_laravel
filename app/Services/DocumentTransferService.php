<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DocumentComment;
use App\Models\DocumentTransfer;
use App\Models\File;
use App\Models\Folder;
use App\Models\Status;
use App\Models\User;
use App\Notifications\DocumentTransferAcceptedNotification;
use App\Notifications\DocumentTransferRejectedNotification;
use App\Notifications\DocumentTransferredNotification;
use App\Support\ArchiveNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DocumentTransferService
{
    public function send(File $document, int $toDepartmentId, ?int $toUserId, string $comment): DocumentTransfer
    {
        $transfer = DocumentTransfer::create([
            'file_id' => $document->id,
            'from_department_id' => $document->dep_id,
            'to_department_id' => $toDepartmentId,
            'from_user_id' => Auth::id(),
            'to_user_id' => $toUserId,
            'status' => DocumentTransfer::STATUS_SENT,
            'comment' => $comment,
            'sent_at' => now(),
        ]);

        AuditLogger::log(
            'document.transfer.send',
            __('archive.audit_transfer_send', ['title' => $document->file_name]),
            $document,
            ['transfer_id' => $transfer->id]
        );

        $transfer->load('fromDepartment', 'toDepartment');

        $this->recordComment(
            $document,
            __('archive.transfer_comment_sent', [
                'from' => $transfer->fromDepartment?->dep_name ?? '—',
                'to' => $transfer->toDepartment?->dep_name ?? '—',
                'comment' => $comment,
            ])
        );

        $this->notifyRecipients($transfer);

        $managerId = (int) (Department::find($toDepartmentId)?->manager_id ?? $toUserId ?? 0);
        ArchiveNotifier::transferSent($document, $managerId);

        return $transfer->load(['fromDepartment', 'toDepartment', 'document']);
    }

    public function receive(DocumentTransfer $transfer): DocumentTransfer
    {
        $transfer->update([
            'status' => DocumentTransfer::STATUS_RECEIVED,
            'received_at' => now(),
        ]);

        return $transfer->fresh();
    }

    public function accept(DocumentTransfer $transfer, ?string $response = null): DocumentTransfer
    {
        $transfer->update([
            'status' => DocumentTransfer::STATUS_ACCEPTED,
            'response_comment' => $response,
            'responded_at' => now(),
        ]);

        $document = $transfer->document()->with(['folder', 'department'])->first();
        $toDepartment = Department::find($transfer->to_department_id);
        $targetFolder = $this->resolveTargetFolder($transfer);

        $updates = ['dep_id' => $transfer->to_department_id];
        if ($targetFolder) {
            $updates['folder_id'] = $targetFolder->id;
        }

        $document->update($updates);

        $this->transitionAfterAccept($document, $response);

        $this->recordComment(
            $document,
            __('archive.transfer_comment_accepted', [
                'dept' => $toDepartment?->dep_name ?? '—',
                'note' => $response ?: __('archive.not_available'),
            ])
        );

        AuditLogger::log(
            'document.transfer.accept',
            __('archive.audit_transfer_accept', ['title' => $document->file_name]),
            $document,
            ['transfer_id' => $transfer->id, 'to_department' => $toDepartment?->dep_name]
        );

        $this->notifySender($transfer, new DocumentTransferAcceptedNotification($transfer->fresh(['document', 'toDepartment', 'fromDepartment'])));

        if ($transfer->from_user_id) {
            ArchiveNotifier::transferAccepted($document, (int) $transfer->from_user_id);
        }

        return $transfer->fresh(['fromDepartment', 'toDepartment', 'document']);
    }

    public function reject(DocumentTransfer $transfer, string $response): DocumentTransfer
    {
        $transfer->update([
            'status' => DocumentTransfer::STATUS_REJECTED,
            'response_comment' => $response,
            'responded_at' => now(),
        ]);

        $transfer->load('toDepartment');
        $document = $transfer->document;

        $this->recordComment(
            $document,
            __('archive.transfer_comment_rejected', [
                'dept' => $transfer->toDepartment?->dep_name ?? '—',
                'reason' => $response,
            ])
        );

        AuditLogger::log(
            'document.transfer.reject',
            __('archive.audit_transfer_reject', ['title' => $document->file_name]),
            $document,
            ['transfer_id' => $transfer->id, 'reason' => $response]
        );

        $this->notifySender($transfer, new DocumentTransferRejectedNotification($transfer->fresh(['document', 'toDepartment', 'fromDepartment'])));

        if ($transfer->from_user_id) {
            ArchiveNotifier::transferRejected($document, (int) $transfer->from_user_id);
        }

        return $transfer->fresh(['fromDepartment', 'toDepartment', 'document']);
    }

    protected function transitionAfterAccept(File $document, ?string $response): void
    {
        $pendingApprovalId = Status::idForSlug('pending_approval');
        if (! $pendingApprovalId || (int) $document->status_id === $pendingApprovalId) {
            return;
        }

        app(DocumentWorkflowService::class)->transition(
            $document->fresh(),
            'pending_approval',
            $response ?: __('archive.transfer_accept_workflow_note')
        );
    }

    protected function resolveTargetFolder(DocumentTransfer $transfer): ?Folder
    {
        $department = Department::find($transfer->to_department_id);
        if (! $department) {
            return null;
        }

        return Folder::where('dep_id', $department->id)->orderBy('id')->first();
    }

    protected function notifyRecipients(DocumentTransfer $transfer): void
    {
        $recipients = collect();

        if ($transfer->to_user_id) {
            $user = User::find($transfer->to_user_id);
            if ($user) {
                $recipients->push($user);
            }
        }

        $department = Department::find($transfer->to_department_id);
        if ($department?->manager_id) {
            $manager = User::find($department->manager_id);
            if ($manager) {
                $recipients->push($manager);
            }
        }

        $recipients->unique('id')->each(function (User $user) use ($transfer) {
            Notification::send($user, new DocumentTransferredNotification($transfer));
        });
    }

    protected function notifySender(DocumentTransfer $transfer, $notification): void
    {
        if (! $transfer->from_user_id) {
            return;
        }

        $sender = User::find($transfer->from_user_id);
        if ($sender) {
            Notification::send($sender, $notification);
        }
    }

    protected function recordComment(File $document, string $body): void
    {
        DocumentComment::create([
            'file_id' => $document->id,
            'user_id' => Auth::id(),
            'body' => $body,
        ]);
    }
}
