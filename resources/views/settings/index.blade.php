@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto" x-data="settingsForm()">

    {{-- SUCCESS ALERT --}}
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 7000)"
            x-show="show"
            x-transition
            class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-green-700 text-sm"
        >
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR ALERT --}}
    @if (session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 7000)"
            x-show="show"
            x-transition
            class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 text-red-700 text-sm"
        >
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold mb-1">Account Settings</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Your profile information is read-only. Click <strong>Change Password</strong> to update your password.
                </p>
            </div>

            <!-- Change Password -->
            <div class="flex gap-2 items-center">
                <button
                    type="button"
                    @click="toggleChangePassword()"
                    x-text="changePassword ? 'Cancel Password' : 'Change Password'"
                    class="inline-flex items-center px-3 py-1.5 rounded-md border text-sm
                           bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                ></button>
            </div>
        </div>

        <form method="POST" action="{{ url('/settings/password') }}">
            @csrf

            <!-- GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- PROFILE (READ-ONLY) -->
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">
                        Profile
                    </h3>

                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input
                            type="text"
                            readonly
                            value="{{ auth()->user()->name }}"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm
                                   bg-gray-100 dark:bg-gray-800/50 cursor-not-allowed"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input
                            type="email"
                            readonly
                            value="{{ auth()->user()->email }}"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm
                                   bg-gray-100 dark:bg-gray-800/50 cursor-not-allowed"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">User Type</label>
                        <input
                            type="text"
                            readonly
                            value="{{ ucfirst(auth()->user()->user_type) }}"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm
                                   bg-gray-100 dark:bg-gray-800/50 cursor-not-allowed"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Department</label>
                        <input
                            type="text"
                            readonly
                            value="{{ auth()->user()->department ?? '—' }}"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm
                                   bg-gray-100 dark:bg-gray-800/50 cursor-not-allowed"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Job Title</label>
                        <input
                            type="text"
                            readonly
                            value="{{ auth()->user()->job_title ?? '—' }}"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm
                                   bg-gray-100 dark:bg-gray-800/50 cursor-not-allowed"
                        >
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">
                        Password
                    </h3>

                    <div>
                        <label class="block text-sm font-medium mb-1">Current Password</label>
                        <input
                            type="password"
                            name="current_password"
                            :disabled="!changePassword"
                            placeholder="Required to change password"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm
                                   bg-white dark:bg-gray-900
                                   disabled:bg-gray-100 disabled:cursor-not-allowed"
                        >
                        @error('current_password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">New Password</label>
                        <input
                            type="password"
                            name="password"
                            :disabled="!changePassword"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm
                                   bg-white dark:bg-gray-900
                                   disabled:bg-gray-100 disabled:cursor-not-allowed"
                        >
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Confirm Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            :disabled="!changePassword"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm
                                   bg-white dark:bg-gray-900
                                   disabled:bg-gray-100 disabled:cursor-not-allowed"
                        >
                    </div>
                </div>
            </div>

            <!-- SAVE -->
            <div class="mt-6 flex justify-end">
                <button
                    type="submit"
                    :disabled="!changePassword"
                    class="inline-flex items-center px-6 py-2.5 rounded-lg
                           bg-green-600 text-white text-sm font-semibold
                           hover:bg-green-700 transition
                           disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function settingsForm() {
    return {
        changePassword: false,

        toggleChangePassword() {
            this.changePassword = !this.changePassword;
        }
    }
}
</script>
@endsection
