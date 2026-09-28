<x-guest-layout title="Login" :split="true">



    <h2 class="text-center text-xl font-semibold text-gray-800">Login</h2>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
        @csrf

        <!-- Username -->
        <div>
            <label for="username" class="block text-sm font-semibold text-gray-800">{{ __('Username') }}</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}"
                placeholder="Masukkan username" required autofocus autocomplete="username" class="mt-1 block w-full border-0 border-b-2 border-blue-200 bg-transparent px-0 py-2 text-sm text-gray-900
                       placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-0" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div x-data="{ show: false }">
            <label for="password" class="block text-sm font-semibold text-gray-800">{{ __('Password') }}</label>
            <div class="relative">
                <input id="password" type="password" x-bind:type="show ? 'text' : 'password'" name="password"
                    placeholder="Masukkan password" required autocomplete="current-password" class="mt-1 block w-full border-0 border-b-2 border-blue-200 bg-transparent py-2 pl-0 pr-10 text-sm text-gray-900
                           placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-0" />

                <button type="button" @click="show = !show"
                    x-bind:aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                    class="absolute inset-y-0 right-0 flex items-center text-gray-400 hover:text-blue-700 focus:outline-none">
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


        <!-- Tombol -->
        <div class="flex items-center gap-3 pt-1">
            <button type="submit" class="rounded-full bg-gradient-to-b from-blue-500 to-blue-700 px-8 py-2.5 text-sm font-semibold text-white
                       shadow-lg shadow-blue-900/25 transition hover:from-blue-600 hover:to-blue-800
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ __('Log in') }}
            </button>

        </div>
    </form>
</x-guest-layout>