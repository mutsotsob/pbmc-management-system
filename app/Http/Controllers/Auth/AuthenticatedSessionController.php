<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        AuditLog::record('login', $user, [], [
            'message' => 'User signed in.',
            'session_id' => $request->session()->getId(),
        ]);

        $home = (!$user->isAdmin() && $user->department === 'Clinical Operations')
            ? route('sample-dispatches.index', absolute: false)
            : route('dashboard', absolute: false);

        return redirect()
            ->intended($home)
            ->with('success', 'Signed in successfully.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            AuditLog::record('logout', $user, [], [
                'message' => 'User signed out.',
                'session_id' => $request->session()->getId(),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Signed out successfully.');
    }
}
