<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDepartmentScope
{
    private const CLINICAL_OPERATIONS_ALLOWED = [
        'sample-dispatches.*',
        'drivers.*',
        'notifications.*',
        'settings',
        'settings.password',
        'profile.*',
        'password.update',
        'verification.*',
        'logout',
    ];

    private const ADMINISTRATION_ALLOWED = [
        'dashboard',
        'profile.*',
        'password.update',
        'verification.*',
        'logout',
    ];

    private const LABORATORY_ALLOWED = [
        'dashboard',
        'analytics.*',
        'sample-dispatches.show',
        'sample-dispatches.receive',
        'sample-dispatches.reject',
        'iavic114-reports.*',
        'profile.*',
        'password.update',
        'verification.*',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->hasFullAccessDepartment()) {
            return $next($request);
        }

        if ($user->isDepartment('Administration')) {
            return $this->allowOnly($request, $next, self::ADMINISTRATION_ALLOWED, 'dashboard',
                'Your account only has access to your transported sample metrics.');
        }

        if ($user->isDepartment('Laboratory')) {
            return $this->allowOnly($request, $next, self::LABORATORY_ALLOWED, 'dashboard',
                'Your account only has access to Home and Overview.');
        }

        if (!$user->isDepartment('Clinical Operations')) {
            return $next($request);
        }

        return $this->allowOnly($request, $next, self::CLINICAL_OPERATIONS_ALLOWED, 'sample-dispatches.index',
            'Your account only has access to Sample Dispatches.');
    }

    private function allowOnly(Request $request, Closure $next, array $allowedPatterns, string $redirectRoute, string $message): Response
    {
        foreach ($allowedPatterns as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        return redirect()->route($redirectRoute)
            ->with('error', $message);
    }

}
