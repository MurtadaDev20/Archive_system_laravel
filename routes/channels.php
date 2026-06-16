<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('team.{managerId}', function ($user, $managerId) {
    if ($user->hasRole('Admin')) {
        return true;
    }

    if ((int) $user->id === (int) $managerId) {
        return true;
    }

    $scope = app(\App\Services\DepartmentScopeService::class);

    return $scope->departmentManagerId((int) $user->department_id) === (int) $managerId;
});
