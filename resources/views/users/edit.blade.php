@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-3xl bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Edit User</h2>
            <p class="text-sm text-gray-500">
                Update user information and access level.
            </p>
        </div>

        <a href="{{ route('admin.users') }}"
           class="text-sm text-gray-600 hover:text-gray-800">
            ← Back to Users
        </a>
    </div>

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERRORS --}}
    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 text-red-700 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.store', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium mb-1">Full Name</label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $user->name) }}"
                       class="w-full rounded-lg border px-4 py-2.5 text-sm">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email"
                       name="email"
                       value="{{ old('email', $user->email) }}"
                       class="w-full rounded-lg border px-4 py-2.5 text-sm">
            </div>

            <!-- Department -->
            <div>
                <label class="block text-sm font-medium mb-1">Department</label>
                <input type="text"
                       name="department"
                       value="{{ old('department', $user->department) }}"
                       class="w-full rounded-lg border px-4 py-2.5 text-sm">
            </div>

            <!-- Job Title -->
            <div>
                <label class="block text-sm font-medium mb-1">Job Title</label>
                <input type="text"
                       name="job_title"
                       value="{{ old('job_title', $user->job_title) }}"
                       class="w-full rounded-lg border px-4 py-2.5 text-sm">
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-sm font-medium mb-1">Phone Number</label>
                <input type="text"
                       name="phone_number"
                       value="{{ old('phone_number', $user->phone_number) }}"
                       class="w-full rounded-lg border px-4 py-2.5 text-sm">
            </div>

            <!-- User Type -->
            <div>
                <label class="block text-sm font-medium mb-1">User Role</label>
                <select name="user_type"
                        class="w-full rounded-lg border px-4 py-2.5 text-sm">
                    <option value="user" {{ $user->user_type === 'user' ? 'selected' : '' }}>
                        User
                    </option>
                    <option value="admin" {{ $user->user_type === 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>
                </select>
            </div>

            <!-- Status -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-2">Account Status</label>
                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio"
                               name="user_status"
                               value="1"
                               {{ $user->user_status ? 'checked' : '' }}>
                        <span class="text-sm">Active</span>
                    </label>

                    <label class="inline-flex items-center gap-2">
                        <input type="radio"
                               name="user_status"
                               value="0"
                               {{ !$user->user_status ? 'checked' : '' }}>
                        <span class="text-sm">Disabled</span>
                    </label>
                </div>
            </div>

        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('admin.users') }}"
               class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">
                Cancel
            </a>

            <button type="submit"
                    class="px-6 py-2 rounded-lg bg-pbmc text-white text-sm font-semibold hover:opacity-90">
                Save Changes
            </button>
        </div>

    </form>

</div>
@endsection
