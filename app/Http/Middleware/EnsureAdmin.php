<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->loadMissing('roles')->isAdmin()) {
            abort(403, 'Acceso restringido a administradores.');
        }

        return $next($request);
    }
}
