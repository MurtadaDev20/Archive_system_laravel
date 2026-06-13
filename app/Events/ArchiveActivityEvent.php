<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ArchiveActivityEvent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $type,
        public string $message,
        public ?int $fileId = null,
        public array $targetUserIds = [],
        public ?int $teamManagerId = null,
        public ?int $excludeActorId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('team.'.$this->teamManagerId)];
    }

    public function broadcastAs(): string
    {
        return 'ArchiveActivity';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'file_id' => $this->fileId,
            'target_user_ids' => $this->targetUserIds,
            'team_manager_id' => $this->teamManagerId,
            'exclude_actor_id' => $this->excludeActorId,
        ];
    }
}
