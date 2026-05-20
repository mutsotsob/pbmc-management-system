<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Notifications\PasswordChangedNotification;
use Throwable;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'current_password' => ['required'],
                'password'         => ['required', 'confirmed', 'min:8'],
            ]);

            if (!Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The current password you entered is incorrect.'],
                ]);
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            $user->notify(new PasswordChangedNotification());

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
}
