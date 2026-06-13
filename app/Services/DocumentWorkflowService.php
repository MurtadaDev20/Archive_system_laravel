<?php

namespace App\Services;

use App\Models\DocumentWorkflowLog;
use App\Models\File;
use App\Models\Status;
use App\Models\User;
use App\Notifications\DocumentApprovedNotification;
use App\Notifications\DocumentRejectedNotification;
use App\Notifications\DocumentUpdatedNotification;
use App\Support\ArchiveNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class DocumentWorkflowService
{
    /** مسار مبسّط: مسودة → اعتماد → معتمد → مؤرشف (+ رفض) */
    public const PIPELINE = [
        'draft',
        'pending_approval',
        'approved',
        'archived',
    ];

    public const LEGACY_WAITING = ['pending_review', 'under_review'];

    public const FLOW = [
        'draft' => 'pending_approval',
        'pending_approval' => 'approved',
        'approved' => 'archived',
    ];

    public function pipelineSlugs(): array
    {
        return self::PIPELINE;
    }

    public function normalizeSlug(?string $slug): string
    {
        if (in_array($slug, self::LEGACY_WAITING, true)) {
            return 'pending_approval';
        }

        return $slug ?: 'draft';
    }

    public function managerActionSlugs(): array
    {
        return array_merge(self::LEGACY_WAITING, ['pending_approval']);
    }

    public function managerActionStatusIds(): array
    {
        return Status::idsForSlugs($this->managerActionSlugs());
    }

    public function descriptionFor(string $slug): string
    {
        $slug = $this->normalizeSlug($slug);
        $key = 'archive.workflow_desc_'.$slug;

        return __($key);
    }

    public function guidanceFor(File $document): string
    {
        $slug = $this->normalizeSlug($document->status?->slug);
        $key = 'archive.workflow_guidance_'.$slug;

        return __($key);
    }

    public function nextStepLabel(string $slug): ?string
    {
        $slug = $this->normalizeSlug($slug);
        $next = self::FLOW[$slug] ?? null;
        if (! $next) {
            return null;
        }

        return archive_status_label(Status::idForSlug($next) ?? 0) ?: null;
    }

    public function isManager(User $user, File $document): bool
    {
        return $document->folder && (int) $user->id === (int) $document->folder->user_id;
    }

    public function isOwner(User $user, File $document): bool
    {
        return (int) $user->id === (int) $document->user_id
            || (int) $user->id === (int) $document->owner_id;
    }

    public function availableActions(File $document, User $user): array
    {
        $slug = $document->status?->slug;
        if (! $slug) {
            return [];
        }

        $manager = $this->isManager($user, $document);
        $owner = $this->isOwner($user, $document);
        $waiting = $slug === 'pending_approval' || in_array($slug, self::LEGACY_WAITING, true);
        $actions = [];

        if ($owner && in_array($slug, ['draft', 'rejected'], true)) {
            $actions[] = $this->action('submit_approval', 'primary');
        }

        if ($manager) {
            if ($waiting) {
                $actions[] = $this->action('approve', 'success');
                $actions[] = $this->action('reject', 'danger', true);
            } elseif ($slug === 'approved') {
                $actions[] = $this->action('archive', 'dark');
            }
        }

        return $actions;
    }

    public function canExecute(File $document, User $user, string $actionKey): bool
    {
        return collect($this->availableActions($document, $user))
            ->contains(fn (array $action) => $action['key'] === $actionKey);
    }

    public function executeAction(File $document, string $actionKey, ?User $actor = null, ?string $comment = null): File
    {
        $actor = $actor ?: Auth::user();

        if (! $this->canExecute($document, $actor, $actionKey)) {
            throw ValidationException::withMessages([
                'workflow' => __('archive.workflow_action_not_allowed'),
            ]);
        }

        if ($actionKey === 'reject' && blank($comment)) {
            throw ValidationException::withMessages([
                'workflowComment' => __('archive.workflow_reject_reason_required'),
            ]);
        }

        $toSlug = match ($actionKey) {
            'submit_approval', 'submit_review', 'resubmit' => 'pending_approval',
            'approve' => 'approved',
            'reject' => 'rejected',
            'archive' => 'archived',
            default => throw new \InvalidArgumentException("إجراء غير معروف: {$actionKey}"),
        };

        return $this->transition($document, $toSlug, $comment ?: __('archive.workflow_action_'.$actionKey), $actor);
    }

    public function transition(File $document, string $toSlug, ?string $comment = null, ?User $actor = null): File
    {
        $actor = $actor ?: Auth::user();
        $fromStatusId = $document->status_id;
        $toStatusId = Status::idForSlug($toSlug);

        if (! $toStatusId) {
            throw new \InvalidArgumentException("حالة غير معروفة: {$toSlug}");
        }

        $updates = ['status_id' => $toStatusId];

        if ($toSlug === 'approved') {
            $updates['approved_by'] = $actor?->id;
            $updates['approved_at'] = now();
        }

        if (in_array($toSlug, ['rejected', 'pending_approval', 'draft'], true)) {
            $updates['approved_by'] = null;
            $updates['approved_at'] = null;
        }

        if ($toSlug === 'archived') {
            $updates['archive_date'] = now()->toDateString();
        }

        $document->update($updates);

        DocumentWorkflowLog::create([
            'file_id' => $document->id,
            'from_status_id' => $fromStatusId,
            'to_status_id' => $toStatusId,
            'user_id' => $actor?->id,
            'comment' => $comment,
        ]);

        AuditLogger::log(
            'document.workflow',
            __('archive.audit_workflow', [
                'title' => $document->file_name,
                'status' => archive_status_label($toStatusId),
            ]),
            $document,
            ['to_status' => $toSlug]
        );

        $this->notifyWorkflowChange($document->fresh(), $toSlug);

        ArchiveNotifier::workflowChanged(
            $document->fresh(['status']),
            archive_status_label($toStatusId),
            $actor?->id
        );

        return $document->fresh(['status']);
    }

    protected function notifyWorkflowChange(File $document, string $toSlug): void
    {
        $owner = User::find($document->owner_id ?: $document->user_id);
        if (! $owner) {
            return;
        }

        match ($toSlug) {
            'approved' => Notification::send($owner, new DocumentApprovedNotification($document)),
            'rejected' => Notification::send($owner, new DocumentRejectedNotification($document)),
            default => Notification::send($owner, new DocumentUpdatedNotification($document)),
        };

        if ($toSlug === 'pending_approval' && $document->folder?->user) {
            Notification::send($document->folder->user, new DocumentUpdatedNotification($document));
        }
    }

    public function advance(File $document, ?string $comment = null): File
    {
        $currentSlug = $this->normalizeSlug($document->status?->slug);
        $nextSlug = self::FLOW[$currentSlug] ?? null;

        if (! $nextSlug) {
            throw new \RuntimeException(__('archive.workflow_cannot_advance'));
        }

        return $this->transition($document, $nextSlug, $comment);
    }

    protected function action(string $key, string $variant, bool $requiresComment = false): array
    {
        return [
            'key' => $key,
            'label' => __('archive.workflow_action_'.$key),
            'icon' => match ($key) {
                'submit_approval', 'submit_review' => 'bi-send',
                'approve' => 'bi-check-circle',
                'reject' => 'bi-x-circle',
                'archive' => 'bi-archive',
                default => 'bi-arrow-right',
            },
            'variant' => $variant,
            'requires_comment' => $requiresComment,
        ];
    }
}
