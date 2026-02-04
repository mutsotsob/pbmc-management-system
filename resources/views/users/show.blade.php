@extends('layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="max-w-3xl bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">User Profile</h2>
            <p class="text-sm text-gray-500">
                View user information and account status.
            </p>
        </div>

        <a href="{{ route('admin.users') }}"
           class="text-sm text-gray-600 hover:text-gray-800">
            ← Back to Users
        </a>
    </div>

    <!-- User Card -->
    <div class="border border-gray-200 rounded-xl p-5">

        <!-- Top section -->
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-pbmc flex items-center justify-center text-white text-xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>

            <div>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $user->name }}
                </p>
                <p class="text-sm text-gray-500">
                    {{ $user->email }}
                </p>
            </div>
        </div>

        <!-- Details grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Department -->
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Department</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $user->department ?? '—' }}
                </p>
            </div>

            <!-- Job Title -->
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Job Title</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $user->job_title ?? '—' }}
                </p>
            </div>

            <!-- Phone -->
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Phone Number</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $user->phone_number ?? '—' }}
                </p>
            </div>

            <!-- User Type -->
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">User Role</p>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                    {{ $user->user_type === 'admin'
                        ? 'bg-orange-100 text-orange-700'
                        : 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst($user->user_type) }}
                </span>
            </div>

            <!-- Status -->
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Account Status</p>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                    {{ $user->user_status
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700' }}">
                    {{ $user->user_status ? 'Active' : 'Disabled' }}
                </span>
            </div>

            <!-- Email Verified -->
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Email Verified</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $user->email_verified_at ? $user->email_verified_at->format('d M Y, H:i') : 'Not Verified' }}
                </p>
            </div>

            <!-- Created At -->
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Created At</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $user->created_at->format('d M Y, H:i') }}
                </p>
            </div>

            <!-- Updated At -->
            <div>
                <p class="text-xs text-gray-500 uppercase mb-1">Last Updated</p>
                <p class="text-sm font-medium text-gray-800">
                    {{ $user->updated_at->format('d M Y, H:i') }}
                </p>
            </div>

        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('admin.users.edit', $user) }}"
               class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:opacity-90">
                Edit User
            </a>

            <a href="{{ route('admin.users') }}"
               class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">
                Back
            </a>
        </div>

    </div>

</div>
@endsection
