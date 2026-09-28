<x-guest-layout title="Login" :scene="true">

    <x-slot name="tagline">
        MAUUU BIKIN SPJ KANN?
        <span class="block text-sm font-normal text-gray-500 mt-1">
            YAUDAH LOGIN DULU YAK DIMARI...
        </span>
    </x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Username -->
        <div>
            <label for="username" class="block text-sm font-medium text-gray-800">{{ __('Username') }}</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}"
                placeholder="Masukkan username" required autofocus autocomplete="username" class="mt-1.5 block w-full rounded-md border border-indigo-100 bg-white px-3 py-2.5 text-sm text-gray-900
                       placeholder-gray-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div x-data="{ show: false }">
            <label for="password" class="block text-sm font-medium text-gray-800">{{ __('Password') }}</label>
            <div class="relative mt-1.5">
                <input id="password" type="password" x-bind:type="show ? 'text' : 'password'" name="password"
                    placeholder="Masukkan password" required autocomplete="current-password" class="block w-full rounded-md border border-indigo-100 bg-white py-2.5 pl-3 pr-11 text-sm text-gray-900
                           placeholder-gray-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" />

                <button type="button" @click="show = !show"
                    x-bind:aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-700 focus:outline-none">
                    <svg x-show="!show" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember me (toggle switch) -->
        <label for="remember_me" class="inline-flex cursor-pointer items-center gap-3">
            <input id="remember_me" type="checkbox" name="remember" class="peer sr-only">
            <span class="relative h-5 w-9 rounded-full bg-indigo-100 transition
                       after:absolute after:left-0.5 after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white
                       after:shadow after:transition after:content-['']
                       peer-checked:bg-indigo-600 peer-checked:after:translate-x-4
                       peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-400"></span>
            <span class="text-sm text-gray-700">{{ __('Remember me') }}</span>
        </label>

        <!-- Tombol -->
        <button type="submit" class="w-full rounded-md bg-gradient-to-b from-indigo-500 to-indigo-700 px-4 py-2.5 text-sm font-semibold text-white
                   shadow-md shadow-indigo-900/20 hover:from-indigo-600 hover:to-indigo-800
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
            {{ __('Log in') }}
        </button>

        @if (Route::has('password.request'))
            <a class="block text-sm text-gray-800 hover:text-indigo-700 hover:underline"
                href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
        @endif
    </form>
</x-guest-layout>