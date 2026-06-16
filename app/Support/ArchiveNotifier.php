<?php

namespace App\Support;

use App\Events\ArchiveActivityEvent;
use App\Models\File;
use App\Models\User;
use App\Services\ArchiveRealtimeService;
use App\Services\ArchiveTeamService;
use App\Services\DepartmentScopeService;
use Illuminate\Support\Facades\Log;

class ArchiveNotifier
{
    /**
     * إشعار أعضاء فريق مدير محدد (مدير + موظفوه) — مع استثناء من نفّذ الإجراء.
     *
     * @param  int[]  $recipientUserIds
     */
    public static function notifyTeam(
        int $teamManagerId,
        array $recipientUserIds,
        string $message,
        string $type = 'info',
        ?File $file = null,
        ?int $excludeActorId = null
    ): void {
        if ($teamManagerId <= 0) {
            return;
        }

        $realtime = app(ArchiveRealtimeService::class);
        $realtime->bumpTeam($teamManagerId);

        $recipients = array_values(array_unique(array_filter(array_map('intval', $recipientUserIds))));

        foreach ($recipients as $userId) {
            if ($userId <= 0 || ($excludeActorId && $userId === (int) $excludeActorId)) {
                continue;
            }

            $realtime->notifyUser($userId, $message, $type);
        }

        self::broadcastSafely($type, $message, $file?->id, $recipients, $teamManagerId, $excludeActorId);
    }

    protected static function broadcastSafely(
        string $type,
        string $message,
        ?int $fileId = null,
        array $targetUserIds = [],
        ?int $teamManagerId = null,
        ?int $excludeActorId = null
    ): void {
        $driver = (string) config('broadcasting.default', 'null');

        if (in_array($driver, ['null', 'log'], true) || ! $teamManagerId) {
            return;
        }

        try {
            event(new ArchiveActivityEvent(
                $type,
                $message,
                $fileId,
                $targetUserIds,
                $teamManagerId,
                $excludeActorId
            ));
        } catch (\Throwable $exception) {
            Log::warning('Archive broadcast skipped (WebSocket server unavailable?)', [
                'driver' => $driver,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public static function documentUploaded(File $file, User $uploader): void
    {
        $file->loadMissing('folder.department');
        $scope = app(DepartmentScopeService::class);
        $teamManagerId = $scope->managerIdForFile($file);

        if (! $teamManagerId) {
            return;
        }

        self::notifyTeam(
            $teamManagerId,
            [$teamManagerId],
            __('archive.realtime_document_uploaded', [
                'name' => $file->file_name,
                'user' => $uploader->name,
            ]),
            'info',
            $file,
            $uploader->id
        );
    }

    public static function transferSent(File $file, int $toManagerId): void
    {
        if ($toManagerId <= 0) {
            return;
        }

        $teamManagerId = (int) $toManagerId;

        self::notifyTeam(
            $teamManagerId,
            [$toManagerId],
            __('archive.realtime_transfer_sent', ['name' => $file->file_name]),
            'warning',
            $file
        );
    }

    public static function transferAccepted(File $file, int $senderId): void
    {
        if ($senderId <= 0) {
            return;
        }

        $sender = User::find($senderId);
        $teamManagerId = $sender?->department_id
            ? app(DepartmentScopeService::class)->departmentManagerId((int) $sender->department_id)
            : null;

        if (! $teamManagerId) {
            return;
        }

        self::notifyTeam(
            $teamManagerId,
            [$senderId],
            __('archive.realtime_transfer_accepted', ['name' => $file->file_name]),
            'success',
            $file
        );
    }

    public static function transferRejected(File $file, int $senderId): void
    {
        if ($senderId <= 0) {
            return;
        }

        $sender = User::find($senderId);
        $teamManagerId = $sender?->department_id
            ? app(DepartmentScopeService::class)->departmentManagerId((int) $sender->department_id)
            : null;

        if (! $teamManagerId) {
            return;
        }

        self::notifyTeam(
            $teamManagerId,
            [$senderId],
            __('archive.realtime_transfer_rejected', ['name' => $file->file_name]),
            'error',
            $file
        );
    }

    public static function workflowChanged(
        File $file,
        string $statusLabel,
        ?int $actorUserId = null
    ): void {
        $teams = app(ArchiveTeamService::class);
        $teamManagerId = $teams->managerIdForFile($file);

        if (! $teamManagerId) {
            return;
        }

        $message = __('archive.realtime_workflow_changed', [
            'name' => $file->file_name,
            'status' => $statusLabel,
        ]);

        $actor = $actorUserId ? User::find($actorUserId) : null;

        if ($actor && app(DepartmentScopeService::class)->canApproveFile($actor, $file)) {
            $recipients = $teams->documentStakeholderIds($file);
        } else {
            $recipients = [$teamManagerId];
        }

        if (empty($recipients)) {
            return;
        }

        self::notifyTeam($teamManagerId, $recipients, $message, 'info', $file, $actorUserId);
    }
}
