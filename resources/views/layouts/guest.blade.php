<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    @isset($background)
        <div class="min-h-screen lg:grid lg:grid-cols-5">

            <div class="hidden lg:block lg:col-span-3 relative bg-gray-800 overflow-hidden">
                {{-- Normal --}}
                <div class="absolute inset-0 bg-cover bg-[center_15%]" style="background-image: url('{{ $background }}')">
                </div>

                {{-- Lirik --}}
                <div data-pose="lirik"
                    class="pose absolute inset-0 bg-cover bg-[center_15%] opacity-0 transition-opacity duration-300"
                    style="background-image: url('{{ asset('images/login-bg-lirik.webp') }}')"></div>

                {{-- Senyum --}}
                <div data-pose="senyum"
                    class="pose absolute inset-0 bg-cover bg-[center_15%] opacity-0 transition-opacity duration-300"
                    style="background-image: url('{{ asset('images/login-bg-senyum.webp') }}')"></div>

                {{-- Teks putih di bawah --}}
                <div class="absolute inset-x-0 bottom-0 p-10 pt-24 bg-gradient-to-t from-black/75 to-transparent">
                    <p class="text-white text-3xl font-bold leading-snug">CEPETAN BIKIN SPJ JANGAN LAMAAA</p>
                </div>
            </div>

            {{-- KANAN: satu kolom bertumpuk: logo, tagline, card --}}
            <div class="lg:col-span-2 min-h-screen bg-gray-100 flex items-center justify-center px-6 py-10">
                <div class="w-full max-w-md flex flex-col items-center">

                    <a href="/">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-24 h-24 object-contain">
                    </a>

                    @isset($tagline)
                        <h1 class="mt-5 text-center text-2xl font-bold text-gray-800 leading-snug">
                            {{ $tagline }}
                        </h1>
                    @endisset

                    <div class="w-full mt-6 px-6 py-6 bg-white shadow-md rounded-lg">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        <script>
            const pw = document.getElementById('password');
            const form = document.querySelector('form');
            const poses = document.querySelectorAll('.pose');
            let submitting = false;

            // Preload supaya nggak kedip pas ganti foto
            poses.forEach(p => {
                const url = p.style.backgroundImage.slice(5, -2);
                new Image().src = url;
            });

            function setPose(name) {
                poses.forEach(p => p.classList.toggle('opacity-0', p.dataset.pose !== name));
            }

            if (pw) {
                pw.addEventListener('focus', () => { if (!submitting) setPose('lirik'); });
                pw.addEventListener('blur', () => { if (!submitting) setPose(null); });
            }

            if (form) {
                form.addEventListener('submit', (e) => {
                    if (submitting) return;
                    e.preventDefault();
                    submitting = true;
                    setPose('senyum');
                    setTimeout(() => form.submit(), 700); // kasih waktu senyumnya kelihatan
                });
            }
        </script>
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
    @endisset
</body>

</html>