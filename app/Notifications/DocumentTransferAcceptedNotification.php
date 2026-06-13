<?php

namespace App\Notifications;

use App\Models\DocumentTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentTransferAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public DocumentTransfer $transfer) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $doc = $this->transfer->document;

        return [
            'title' => 'قبول إحالة',
            'message' => "تم قبول إحالة المستند: {$doc->file_name} إلى {$this->transfer->toDepartment?->dep_name}",
            'document_id' => $doc->id,
            'transfer_id' => $this->transfer->id,
            'url' => route('document.show', $doc),
        ];
    }
}
