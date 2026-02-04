{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('content')
<div class="max-w-4xl mx-auto">

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

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 text-red-700 text-sm">
            <p class="font-semibold mb-2">Please fix the errors below:</p>
            <ul class="list-disc ml-5 space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Create User</h2>
                <p class="text-sm text-gray-500">Add a new user to the system.</p>
            </div>

            <a href="{{ url('/admin/users') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm hover:bg-gray-50">
                <i data-feather="arrow-left" class="w-4 h-4"></i>
                Back
            </a>
        </div>

        <form method="POST" action="{{ url('/admin/users') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white"
                           placeholder="Full name">
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Email <span class="text-red-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white"
                           placeholder="user@example.com">
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}"
                           class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white"
                           placeholder="07XXXXXXXX">
                    @error('phone_number')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- User Type --}}
                <div>
                    <label class="block text-sm font-medium mb-1">User Type <span class="text-red-600">*</span></label>
                    <select name="user_type" class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white">
                        <option value="user" {{ old('user_type', 'user') === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('user_type') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('user_type')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Department --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Department</label>
                    <input type="text" name="department" value="{{ old('department') }}"
                           class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white"
                           placeholder="e.g. Laboratory">
                    @error('department')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Job Title --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Job Title</label>
                    <input type="text" name="job_title" value="{{ old('job_title') }}"
                           class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white"
                           placeholder="e.g. Lab Manager">
                    @error('job_title')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Status</label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="user_status" value="1"
                               {{ old('user_status', '1') ? 'checked' : '' }}
                               class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                    @error('user_status')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Password <span class="text-red-600">*</span></label>
                    <input type="password" name="password"
                           class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white"
                           placeholder="Min 8 characters">
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Confirm Password <span class="text-red-600">*</span></label>
                    <input type="password" name="password_confirmation"
                           class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white"
                           placeholder="Repeat password">
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ url('/admin/users') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-lg border text-sm hover:bg-gray-50">
                    Cancel
                </a>

                <button type="submit"
                        class="inline-flex items-center px-6 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                    <i data-feather="save" class="w-4 h-4 mr-2"></i>
                    Save User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
