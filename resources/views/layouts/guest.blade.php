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
    @if ($attributes->get('split'))
        {{-- ===== MODE SPLIT: panel biru kiri + panel putih kanan dengan tepi awan ===== --}}
        <div class="min-h-screen bg-white">
            <div class="relative flex min-h-screen w-full flex-col overflow-hidden bg-white md:flex-row">

                {{-- Panel biru --}}
                <section class="relative flex flex-col items-center justify-center bg-gradient-to-b from-blue-500 to-blue-800
                                        px-8 pb-24 pt-12 text-center text-white md:w-1/2 md:pb-12">
                    <p class="text-xl font-semibold md:text-2xl">Selamat datang pikmin</p>

                    @isset($tagline)
                        <h1 class="mt-6 max-w-xs text-sm font-semibold leading-snug text-white md:text-base">
                            {{ $tagline }}
                        </h1>
                    @endisset
                    @isset($subtagline)
                        <p class="mt-1 max-w-xs text-xs leading-relaxed text-blue-100">{{ $subtagline }}</p>
                    @endisset

                    <p class="absolute bottom-5 left-0 hidden w-full text-xs text-blue-100 md:block">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}
                    </p>
                </section>

                {{-- Panel putih (form) --}}
                <section class="relative flex flex-1 items-center bg-white md:w-1/2">

                    {{-- Awan desktop: menempel di tepi kiri panel putih, tingginya mengikuti layar --}}
                    <svg class="pointer-events-none absolute top-0 hidden h-full w-auto md:block"
                        style="right: calc(100% - 1px)" viewBox="0 0 200 1000" aria-hidden="true">
                        <x-cloud-layers />
                    </svg>

                    {{-- Awan mobile: sama, diputar supaya lekukannya menghadap ke atas --}}
                    <svg class="pointer-events-none absolute inset-x-0 h-auto w-full md:hidden"
                        style="bottom: calc(100% - 1px)" viewBox="0 0 1000 200" aria-hidden="true">
                        <g transform="matrix(0 1 1 0 0 0)">
                            <x-cloud-layers />
                        </g>
                    </svg>

                    <main class="relative w-full px-8 py-10 md:px-12 md:py-14">
                        <div class="mx-auto w-full max-w-sm">
                            {{ $slot }}
                        </div>
                    </main>
                </section>
            </div>
        </div>
    @elseif ($attributes->get('scene'))
        <div class="relative min-h-screen overflow-hidden bg-indigo-50 flex flex-col items-center px-4">
            <x-login-scene class="absolute inset-0 w-full h-full" />

            <a href="/" class="relative z-10 mt-8 sm:mt-10">
                <img src="{{ asset('images/logoheader.png') }}" alt="Logo"
                    class="h-12 sm:h-14 lg:h-16 w-auto object-contain">
            </a>

            <main class="relative z-10 flex w-full flex-1 items-center justify-center py-8">
                <div class="w-full max-w-sm rounded-lg border border-indigo-100 bg-white/70 backdrop-blur-md
                                    shadow-xl shadow-indigo-900/5 p-6 sm:p-7">
                    @isset($tagline)
                        <h1 class="text-lg font-bold text-gray-900 leading-snug">{{ $tagline }}</h1>
                    @endisset
                    <div class="mt-5">{{ $slot }}</div>
                </div>
            </main>

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