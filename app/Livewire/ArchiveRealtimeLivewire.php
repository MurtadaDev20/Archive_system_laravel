<?php

namespace App\Livewire;

use App\Services\ArchiveRealtimeService;
use App\Services\DocumentInboxService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ArchiveRealtimeLivewire extends Component
{
    public int $version = 0;

    public function sync(): void
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        $realtime = app(ArchiveRealtimeService::class);
        $currentVersion = $realtime->versionFor($user);

        if ($this->version > 0 && $currentVersion > $this->version) {
            foreach ($realtime->pullNotifications($user) as $notification) {
                $this->dispatch(
                    'archive-notify',
                    type: $notification['type'] ?? 'info',
                    message: $notification['message'] ?? ''
                );
            }

            $this->dispatch('archive-refreshed');

            $inbox = app(DocumentInboxService::class);
            $this->dispatch('sidebar-counts-updated', counts: [
                'documents' => $inbox->sidebarDocumentsBadge($user),
                'transfers' => $inbox->pendingTransferCount($user),
                'approvals' => $inbox->pendingApprovalCount($user),
            ]);
        }

        $this->version = $currentVersion;
    }

    public function render()
    {
        return view('livewire.archive-realtime-livewire');
    }
}
