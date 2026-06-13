<?php

namespace App\Providers;

use App\Services\DocumentInboxService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.main-sidebar', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $view->with(
                'sidebarCounts',
                app(DocumentInboxService::class)->sidebarCounts(Auth::user())
            );
        });

        View::composer('layouts.admin.*', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();

            $view->with(
                'homeRoute',
                $user->hasRole('Admin') ? route('dashboard') : route('workspace')
            );
        });
    }
}
