<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Pbmc;

use Throwable;

class DashboardController extends Controller
{

private function ensureAdmin()
{
    $user = Auth::user();

    if (!$user || strtolower(trim($user->user_type)) !== 'admin') {
        abort(403, 'Unauthorized access.');
    }
}

    public function settings()
    {
        return view('settings.index');
    }

    public function dashboard()
    {
        $pbmcs = Pbmc::latest()->get(); 

        return view('dashboard', compact('pbmcs'));
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'current_password' => ['required'],
                'password' => ['required', 'confirmed', 'min:8'],
            ]);

            if (!Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The current password you entered is incorrect.'],
                ]);
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return back()->with('success', 'Password updated successfully.');

        } catch (ValidationException $e) {
            throw $e;

        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('error', 'Failed to update password. Please try again later.')
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }
    }


  public function manageUsers(Request $request)
{
    $user = Auth::user();
    if (!$user || strtolower(trim($user->user_type ?? '')) !== 'admin') {
        abort(403, 'Unauthorized access.');
    }

    $q = trim((string) $request->query('q', ''));
    $sort = $request->query('sort', 'name');
    $dir  = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $allowedSorts = ['name','email','department','job_title','user_type','user_status','created_at'];
    if (!in_array($sort, $allowedSorts, true)) $sort = 'name';

    $users = User::query()
        ->when($q, function ($query) use ($q) {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('department', 'like', "%{$q}%")
                   ->orWhere('job_title', 'like', "%{$q}%");
            });
        })
        ->orderBy($sort, $dir)
        ->paginate(10)
        ->withQueryString();

    return view('users.index', compact('users', 'q', 'sort', 'dir'));
}


/**
 * Show create user form
 */
public function createUser()
{
    $this->ensureAdmin();

    return view('users.create');
}

/**
 * Store new user
 */
public function storeUser(Request $request)
{
    $this->ensureAdmin();

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users,email'],
        'department' => ['nullable', 'string', 'max:255'],
        'job_title' => ['nullable', 'string', 'max:255'],
        'user_type' => ['required', 'in:admin,user'],
        'password' => ['required', 'min:8', 'confirmed'],
    ]);

    User::create([
        ...$validated,
        'password' => Hash::make($validated['password']),
        'user_status' => true,
    ]);

    return redirect()
        ->route('admin.users')
        ->with('success', 'User created successfully.');
}

/**
 * Show user details
 */
public function showUser(User $user)
{
    $this->ensureAdmin();

    return view('users.show', compact('user'));
}

/**
 * Show edit form
 */
public function editUser(User $user)
{
    $this->ensureAdmin();

    return view('users.edit', compact('user'));
}

/**
 * Enable / Disable user
 */
public function toggleUserStatus(User $user)
{
    $this->ensureAdmin();

    // Prevent admin from disabling themselves
    if ($user->id === Auth::id()) {
        return back()->with('error', 'You cannot disable your own account.');
    }

    $user->update([
        'user_status' => !$user->user_status,
    ]);

    return back()->with('success', 'User status updated.');
}



    public function bulkEnableUsers(Request $request)
{
    $user = $request->user();
    if (!$user || strtolower(trim($user->user_type ?? '')) !== 'admin') abort(403);

    $data = $request->validate([
        'ids'   => ['required', 'array', 'min:1'],
        'ids.*' => ['integer', 'exists:users,id'],
    ]);

    User::whereIn('id', $data['ids'])->update(['user_status' => true]);

    return back()->with('success', 'Selected users enabled successfully.');
}

public function bulkDisableUsers(Request $request)
{
    $user = $request->user();
    if (!$user || strtolower(trim($user->user_type ?? '')) !== 'admin') abort(403);

    $data = $request->validate([
        'ids'   => ['required', 'array', 'min:1'],
        'ids.*' => ['integer', 'exists:users,id'],
    ]);

    User::whereIn('id', $data['ids'])->update(['user_status' => false]);

    return back()->with('success', 'Selected users disabled successfully.');
}


}
