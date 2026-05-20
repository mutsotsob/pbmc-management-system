@extends('layouts.app')

@section('title', 'Profile')
@section('topnav-title', 'My Profile')

@section('content')
@php
    $initials = collect(preg_split('/\s+/', trim($user->name)) ?: [])
        ->filter()
        ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<div class="mx-auto max-w-5xl space-y-5">
    @if (session('status') === 'password-updated')
        <x-alert type="success">Your password has been updated successfully.</x-alert>
    @endif

    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-gray-50 px-5 py-5 sm:px-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-pbmc text-lg font-bold text-white">
                        {{ $initials ?: 'U' }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :value="$user->user_status ? 'Active' : 'Disabled'" :type="$user->user_status ? 'success' : 'danger'" />
                    <x-status-badge :value="$user->isAdmin() ? 'Admin' : 'User'" :type="$user->isAdmin() ? 'info' : 'neutral'" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 divide-y divide-gray-100 lg:grid-cols-12 lg:divide-x lg:divide-y-0">
            <section class="p-5 sm:p-6 lg:col-span-7">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <i data-feather="user-check" class="h-4 w-4 text-gray-500"></i>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Account Information</h3>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            These details are managed by administrators to keep user access controlled and auditable.
                        </p>
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Email Address</dt>
                        <dd class="mt-1 break-words text-sm font-semibold text-gray-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Department</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $user->department ?: 'Not assigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Job Title</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $user->job_title ?: 'Not assigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Phone Number</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $user->phone_number ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Role</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ ucfirst($user->user_type ?? 'user') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Email Verification</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $user->email_verified_at ? $user->email_verified_at->format('d M Y, H:i') : 'Not verified' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Account Created</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $user->created_at?->format('d M Y, H:i') ?? 'N/A' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                    <div class="flex gap-3">
                        <i data-feather="info" class="mt-0.5 h-4 w-4 shrink-0 text-blue-600"></i>
                        <p class="text-sm text-blue-800">
                            To update your name, department, role, email, or account status, please contact an administrator.
                        </p>
                    </div>
                </div>
            </section>

            <section class="p-5 sm:p-6 lg:col-span-5">
                <div class="mb-5">
                    <div class="flex items-center gap-2">
                        <i data-feather="lock" class="h-4 w-4 text-gray-500"></i>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Password Security</h3>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Use a strong password with at least eight characters. You will need your current password to make this change.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    <div>
                        <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700">Current Password</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                        @if($errors->updatePassword->has('current_password'))
                            <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-gray-700">New Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                        @if($errors->updatePassword->has('password'))
                            <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                    </div>

                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                        <i data-feather="shield" class="h-4 w-4"></i>
                        Update Password
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
