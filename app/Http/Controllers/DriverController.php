<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->department !== 'Clinical Operations') {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $drivers = Driver::when($request->filled('q'), fn ($query) =>
                $query->where('name', 'like', '%' . $request->query('q') . '%')
                      ->orWhere('phone_number', 'like', '%' . $request->query('q') . '%')
                      ->orWhere('vehicle_registration', 'like', '%' . $request->query('q') . '%')
            )
            ->orderBy('name')
            ->get();

        return view('drivers.index', compact('drivers'));
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

    public function update(Request $request, Driver $driver)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:150'],
            'phone_number'         => ['nullable', 'string', 'max:30'],
            'vehicle_registration' => ['nullable', 'string', 'max:50'],
        ]);

        $driver->update($validated);

        return back()->with('success', 'Driver updated successfully.');
    }

    public function toggleActive(Driver $driver)
    {
        $this->authorizeAccess();

        $driver->update(['active' => !$driver->active]);

        $status = $driver->fresh()->active ? 'activated' : 'deactivated';

        return back()->with('success', "Driver {$driver->name} {$status}.");
    }
}
