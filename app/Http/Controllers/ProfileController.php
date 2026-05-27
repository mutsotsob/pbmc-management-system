<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        if ($request->hasFile('profile_photo')) {
            $validated = $request->validate([
                'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            $user = $request->user();
            $oldPhoto = $user->profile_photo_path;

            $path = $validated['profile_photo']->store('profile-photos', 'public');

            if (!$path) {
                return Redirect::route('profile.edit')
                    ->with('error', 'Profile picture could not be uploaded. Please try again.');
            }

            $user->update(['profile_photo_path' => $path]);

            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }

            return Redirect::route('profile.edit')
                ->with('status', 'profile-photo-updated');
        }

        return Redirect::route('profile.edit')
            ->with('error', 'Profile details are managed by administrators. You can update your password from this page.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        return Redirect::route('profile.edit')
            ->with('error', 'Self-service account deletion is disabled. Please contact an administrator.');
    }
}
