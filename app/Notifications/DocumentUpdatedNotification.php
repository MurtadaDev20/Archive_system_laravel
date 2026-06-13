<?php

namespace App\Notifications;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public File $document, public string $action = 'update') {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تحديث مستند',
            'message' => "تم تحديث المستند: {$this->document->file_name}",
            'document_id' => $this->document->id,
            'url' => route('document.show', $this->document),
        ];
    }
}
