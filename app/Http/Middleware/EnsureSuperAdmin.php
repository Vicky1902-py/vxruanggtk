<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('super')->check()) {
            if ($request->session()->has('god_id')) {
                $god = SuperAdmin::find($request->session()->get('god_id'));
                if ($god) {
                    Auth::guard('super')->login($god);
                    return $next($request);
                }
            }

            return redirect()->route('super.login');
        }

        return $next($request);
    }
}
