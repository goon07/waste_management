<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        Log::info('RoleMiddleware executed', [
            'user' => Auth::user() ? Auth::user()->toArray() : null,
            'roles' => $roles,
            'route' => $request->route()->getName(),
        ]);

        if (!Auth::check()) {
            Log::warning('RoleMiddleware: User not authenticated', ['route' => $request->route()->getName()]);
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!in_array($user->role, $roles)) {
            Log::warning('RoleMiddleware: Unauthorized role', [
                'user_role' => $user->role,
                'required_roles' => $roles,
                'route' => $request->route()->getName(),
            ]);
            abort(403, 'Unauthorized: User does not have the required role.');
        }

        return $next($request);
    }
}