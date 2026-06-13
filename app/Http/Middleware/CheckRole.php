<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $allowedRoles = ! empty($roles) ? $roles : ['Admin', 'Super Admin'];

        if (Auth::user()->hasAnyRole($allowedRoles)) {
            return $next($request);
        }

        return redirect(RouteServiceProvider::ALLUSERS);
    }
}
