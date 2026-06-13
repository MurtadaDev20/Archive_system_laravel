<?php

namespace App\Notifications;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public File $document) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'رفض مستند',
            'message' => "تم رفض المستند: {$this->document->file_name}",
            'document_id' => $this->document->id,
            'url' => route('document.show', $this->document),
        ];
    }
}
