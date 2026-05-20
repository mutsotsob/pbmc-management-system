<x-guest-layout>

    <div class="flex flex-col items-center mb-8 text-center">
        <img src="{{ asset('idrl.png') }}" alt="IDRL Logo" class="h-20 w-auto mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">PBMC Records</h1>
        <p class="mt-1 text-sm text-gray-500">Choose a new password</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                          name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center py-3 text-sm">
            {{ __('Reset Password') }}
        </x-primary-button>

        <div class="text-center">
            <a href="{{ route('login') }}"
               class="text-sm text-gray-500 hover:text-orange-500 transition-colors">
                &larr; Back to sign in
            </a>
        </div>
    </form>

</x-guest-layout>
