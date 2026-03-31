<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCatedratico
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        $user->loadMissing('roles');

        if (!$user->isAdmin() && !$user->isCatedratico()) {
            abort(403, 'Acceso restringido a catedráticos.');
        }

        return $next($request);
    }
}
