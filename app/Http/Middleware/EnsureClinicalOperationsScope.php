<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicalOperationsScope
{
    // Routes Clinical Operations users may access
    private const ALLOWED = [
        'sample-dispatches.*',
        'drivers.*',
        'notifications.*',
        'settings',
        'settings.password',
        'profile.*',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->isAdmin() || $user->department !== 'Clinical Operations') {
            return $next($request);
        }

        foreach (self::ALLOWED as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        return redirect()->route('sample-dispatches.index')
            ->with('error', 'Your account only has access to Sample Dispatches.');
    }
}
