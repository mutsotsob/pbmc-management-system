<?php

namespace App\Http\Controllers;

use App\Models\Pbmc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PbmcController extends Controller
{
    /**
     * Display PBMC dashboard / list
     */
    public function index()
    {
        $pbmcs = Pbmc::latest()->get();
        return view('pbmc.index', compact('pbmcs'));
    }

    /**
     * Show create PBMC form
     */
    public function create()
    {
        // Optional: prevent creating if PBMC already exists
        if (Pbmc::exists()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'PBMC already exists.');
        }

        return view('pbmc.create');
    }

    /**
     * Store new PBMC
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Pbmc::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'PBMC created successfully.');
    }

    /**
     * Show single PBMC
     */
    public function show(Pbmc $pbmc)
    {
        return view('pbmc.show', compact('pbmc'));
    }

    /**
     * Show edit PBMC form
     */
    public function edit(Pbmc $pbmc)
    {
        return view('pbmc.edit', compact('pbmc'));
    }

    /**
     * Update PBMC
     */
    public function update(Request $request, Pbmc $pbmc)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $pbmc->update($request->only([
            'title',
            'description',
            'status',
        ]));

        return redirect()
            ->route('pbmc.index')
            ->with('success', 'PBMC updated successfully.');
    }

    /**
     * Delete PBMC
     */
    public function destroy(Pbmc $pbmc)
    {
        $pbmc->delete();

        return redirect()
            ->route('pbmc.index')
            ->with('success', 'PBMC deleted successfully.');
    }
}
