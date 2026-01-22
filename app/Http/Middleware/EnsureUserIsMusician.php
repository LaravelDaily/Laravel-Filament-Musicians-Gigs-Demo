<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMusician
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isMusician()) {
            abort(403);
        }

        return $next($request);
    }
}
