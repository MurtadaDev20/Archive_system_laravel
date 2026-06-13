<?php

namespace App\Policies;

use App\Models\DocumentTransfer;
use App\Models\User;
use App\Services\DocumentInboxService;

class DocumentTransferPolicy
{
    public function respond(User $user, DocumentTransfer $transfer): bool
    {
        return app(DocumentInboxService::class)->canRespondToTransfer($user, $transfer);
    }
}
