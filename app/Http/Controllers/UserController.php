<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\UserCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'name');
        $dir  = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedSorts = ['name', 'email', 'department', 'job_title', 'user_type', 'user_status', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $users = User::query()
            ->when($q, fn ($query) => $query->where(fn ($qq) =>
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('department', 'like', "%{$q}%")
                   ->orWhere('job_title', 'like', "%{$q}%")
            ))
            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'q', 'sort', 'dir'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $newUser = User::create([
            ...$validated,
            'password'    => Hash::make($validated['password']),
            'user_status' => true,
        ]);

        $newUser->notify(new UserCreatedNotification($validated['password']));

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        $user->update(['user_status' => !$user->user_status]);

        return back()->with('success', 'User status updated.');
    }

    public function bulkEnable(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        User::whereIn('id', $data['ids'])->update(['user_status' => true]);

        return back()->with('success', 'Selected users enabled successfully.');
    }

    public function bulkDisable(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $activeAdminsAfter = User::where('user_type', 'admin')
            ->where('user_status', true)
            ->whereNotIn('id', $data['ids'])
            ->count();

        if ($activeAdminsAfter === 0) {
            return back()->with('error', 'Cannot disable all active administrators. At least one must remain active.');
        }

        User::whereIn('id', $data['ids'])->update(['user_status' => false]);

        return back()->with('success', 'Selected users disabled successfully.');
    }
}
