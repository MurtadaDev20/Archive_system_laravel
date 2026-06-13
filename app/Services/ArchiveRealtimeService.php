<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ArchiveRealtimeService
{
    public function notifyUser(int $userId, string $message, string $type = 'info'): void
    {
        if ($userId <= 0) {
            return;
        }

        $key = "archive.notify.{$userId}";
        $queue = Cache::get($key, []);
        $queue[] = ['message' => $message, 'type' => $type];
        Cache::put($key, array_slice($queue, -5), 300);

        DocumentInboxService::clearSidebarCache($userId);
        Cache::increment("archive.version.user.{$userId}");
    }

    public function bumpTeam(int $managerId): void
    {
        if ($managerId <= 0) {
            return;
        }

        $members = app(ArchiveTeamService::class)->memberIds($managerId);
        DocumentSearchService::clearTeamFilterCache($members);

        foreach ($members as $userId) {
            DocumentInboxService::clearSidebarCache($userId);
        }

        Cache::increment("archive.version.team.{$managerId}");
    }

    public function bumpAdmin(): void
    {
        Cache::increment('archive.version.admin');
    }

    public function versionFor(User $user): int
    {
        $versions = [(int) Cache::get("archive.version.user.{$user->id}", 0)];

        $teamManagerId = app(ArchiveTeamService::class)->managerIdFor($user);
        if ($teamManagerId) {
            $versions[] = (int) Cache::get("archive.version.team.{$teamManagerId}", 0);
        }

        if ($user->hasRole('Admin')) {
            $versions[] = (int) Cache::get('archive.version.admin', 0);
        }

        return max($versions);
    }

    public function pullNotifications(User $user): array
    {
        return Cache::pull("archive.notify.{$user->id}", []);
    }
}
