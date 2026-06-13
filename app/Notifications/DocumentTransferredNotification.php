<?php

namespace App\Notifications;

use App\Models\DocumentTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentTransferredNotification extends Notification
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
            'title' => 'إحالة مستند',
            'message' => "تم إحالة المستند: {$doc->file_name}",
            'document_id' => $doc->id,
            'transfer_id' => $this->transfer->id,
            'url' => route('document.show', $doc),
        ];
    }
}
