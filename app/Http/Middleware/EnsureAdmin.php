<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->hasFullSystemAccess()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Administrator access is required for that action.');
        }

        return $next($request);
    }
}
