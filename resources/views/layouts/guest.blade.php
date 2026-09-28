<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $attributes->get('title') ? $attributes->get('title') . ' | ' : '' }}{{ config('app.name', 'Laravel') }}
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    @if ($attributes->get('scene'))
        <div class="relative min-h-screen overflow-hidden bg-indigo-50 flex flex-col items-center px-4">

            {{-- Background SVG --}}
            <x-login-scene class="absolute inset-0 w-full h-full" />

            {{-- Logo tengah atas --}}
            <a href="/" class="relative z-10 mt-8 sm:mt-10">
                <img src="{{ asset('images/logoheader.png') }}" alt="Logo"
                    class="h-12 sm:h-14 lg:h-16 w-auto object-contain">
            </a>

            {{-- Card login --}}
            <main class="relative z-10 flex w-full flex-1 items-center justify-center py-8">
                <div class="w-full max-w-sm rounded-lg border border-indigo-100 bg-white/70 backdrop-blur-md
                               shadow-xl shadow-indigo-900/5 p-6 sm:p-7">

                    @isset($tagline)
                        <h1 class="text-lg font-bold text-gray-900 leading-snug">
                            {{ $tagline }}
                        </h1>
                    @endisset

                    <div class="mt-5">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            {{-- Footer --}}
            <footer class="relative z-10 pb-6 text-xs text-gray-600">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}
            </footer>
        </div>
    @else
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    @endif
</body>

</html>