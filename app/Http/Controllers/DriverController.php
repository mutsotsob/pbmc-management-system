<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user->hasFullSystemAccess() && !$user->isDepartment('Clinical Operations')) {
            throw new HttpResponseException(
                redirect()
                    ->route('dashboard')
                    ->with('error', 'You do not have permission to manage drivers.')
            );
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $drivers = User::where('department', 'Administration')
            ->where('user_status', true)
            ->when($request->filled('q'), fn ($query) =>
                $query->where('name', 'like', '%' . $request->query('q') . '%')
                      ->orWhere('email', 'like', '%' . $request->query('q') . '%')
                      ->orWhere('phone_number', 'like', '%' . $request->query('q') . '%')
            )
            ->orderBy('name')
            ->get();

        return view('drivers.index', compact('drivers'));
    }

    public function show(User $driver)
    {
        $this->authorizeAccess();

        return view('drivers.show', compact('driver'));
    }

    public function edit(User $driver)
    {
        $this->authorizeAccess();

        return view('drivers.edit', compact('driver'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:150'],
            'phone_number'         => ['nullable', 'string', 'max:30'],
            'vehicle_registration' => ['nullable', 'string', 'max:50'],
        ]);

        Driver::create($validated);

        return back()->with('success', 'Driver added successfully.');
    }

    public function update(Request $request, User $driver)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email,' . $driver->id],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ]);

        $driver->update($validated);

        return redirect()->route('drivers.show', $driver)->with('success', 'Driver details updated successfully.');
    }

    public function toggleActive(Driver $driver)
    {
        $this->authorizeAccess();

        $driver->update(['active' => !$driver->active]);

        $status = $driver->fresh()->active ? 'activated' : 'deactivated';

        return back()->with('success', "Driver {$driver->name} {$status}.");
    }
}
