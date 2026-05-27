<x-guest-layout>

    <!-- Branding -->
    <div class="flex flex-col items-center mb-8 text-center">
        <img
            src="{{ asset('idrl.png') }}"
            alt="IDRL Logo"
            class="h-20 w-auto mb-4"
        >

        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
            PBMC Records
        </h1>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Secure Login Portal · 2026
        </p>
    </div>

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder=""
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder=""
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end">
            <a href="{{ route('password.request') }}"
               class="text-sm text-gray-500 hover:text-orange-500 transition-colors">
                Forgot your password?
            </a>
        </div>

        <!-- Submit -->
        <div>
            <x-primary-button class="w-full justify-center py-3 text-sm">
                {{ __('Sign in') }}
            </x-primary-button>
        </div>
    </form>

</x-guest-layout>
