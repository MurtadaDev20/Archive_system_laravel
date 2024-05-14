<?php

namespace App\Http\Middleware;

use Closure;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();
    
        if ($user) {
            foreach ($user->roles as $role) {
                if ($role->name == 'Admin') {
                    return $next($request);
                } else{
                    return redirect(RouteServiceProvider::ALLUSERS);
                }
            }
            return redirect('/'); 
        }
        
        
    
    }
}
