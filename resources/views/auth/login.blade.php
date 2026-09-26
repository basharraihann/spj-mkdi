<x-guest-layout>
    <x-slot name="background">{{ asset('images/login-bg.webp') }}</x-slot>

    <x-slot name="tagline">
        MAUUU BIKIN SPJ KANN?
        <span class="block text-base font-normal text-gray-500 mt-1">
            YAUDAH LOGIN DULU YAK DIMARI...
        </span>
    </x-slot>

    {{-- sisanya tetap sama --}}

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Username -->
        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>
        <!-- Password -->
        <div class="mt-4" x-data="{ show: true }">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-20" type="text"
                    x-bind:type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" />

                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 px-4 text-sm font-semibold text-indigo-600 hover:text-indigo-800 focus:outline-none">
                    <span x-text="show ? 'Lah Ngintip' : 'Kasih Liat'"></span>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>