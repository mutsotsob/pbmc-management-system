<x-guest-layout>

    <div class="flex flex-col items-center mb-8 text-center">
        <img src="{{ asset('idrl.png') }}" alt="IDRL Logo" class="h-20 w-auto mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">PBMC Records</h1>
        <p class="mt-1 text-sm text-gray-500">Reset your password</p>
    </div>

    <p class="mb-5 text-sm text-gray-600 text-center leading-relaxed">
        Enter your email address and we'll send you a link to reset your password.
    </p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email')" required autofocus placeholder="" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center py-3 text-sm">
            {{ __('Send Reset Link') }}
        </x-primary-button>

        <div class="text-center">
            <a href="{{ route('login') }}"
               class="text-sm text-gray-500 hover:text-orange-500 transition-colors">
                &larr; Back to sign in
            </a>
        </div>
    </form>

</x-guest-layout>
