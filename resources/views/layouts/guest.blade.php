<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $attributes->get('title') ? $attributes->get('title') . ' | ' : '' }}{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }

        /* ===== SPLIT LAYOUT ===== */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            background: #f0f4f8;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-card {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 560px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        }

        /* Panel kiri: gambar */
        .login-visual {
            position: relative;
            width: 48%;
            flex-shrink: 0;
            background: #b8d4ed;
            overflow: hidden;
        }

        .login-visual img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .login-visual-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(10,80,160,0.55) 0%, rgba(10,80,160,0.10) 60%, transparent 100%);
        }

        .login-visual-caption {
            position: absolute;
            bottom: 28px;
            left: 0; right: 0;
            text-align: center;
            color: #fff;
        }

        .login-visual-caption p:first-child {
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .login-visual-caption p:last-child {
            font-size: 0.78rem;
            opacity: 0.85;
            margin-top: 2px;
        }

        /* Dot indicators */
        .login-visual-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 10px;
        }

        .login-visual-dots span {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
        }

        .login-visual-dots span.active {
            background: #fff;
            width: 20px;
            border-radius: 4px;
        }

        /* Panel kanan: form */
        .login-form-panel {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 44px;
        }

        .login-form-inner {
            width: 100%;
            max-width: 320px;
        }

        /* Judul */
        .login-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #111827;
            text-align: center;
            margin-bottom: 22px;
        }

        /* Tab e-mail / mobile */
        .login-tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 22px;
        }

        .login-tab {
            flex: 1;
            text-align: center;
            padding-bottom: 10px;
            font-size: 0.78rem;
            font-weight: 500;
            color: #9ca3af;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all .2s;
        }

        .login-tab.active {
            color: #111827;
            border-bottom-color: #111827;
        }

        /* Input underline */
        .login-input-group {
            position: relative;
            margin-bottom: 18px;
        }

        .login-input-group .input-icon {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            width: 16px;
            height: 16px;
        }

        .login-input-group input {
            width: 100%;
            border: none;
            border-bottom: 1.5px solid #d1d5db;
            padding: 9px 28px 9px 24px;
            font-size: 0.82rem;
            color: #374151;
            background: transparent;
            outline: none;
            transition: border-color .2s;
        }

        .login-input-group input:focus {
            border-bottom-color: #3b82f6;
        }

        .login-input-group input::placeholder {
            color: #9ca3af;
        }

        /* Eye toggle */
        .login-input-group .eye-btn {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            padding: 0;
        }

        /* Forgot */
        .login-forgot {
            text-align: right;
            margin-bottom: 18px;
        }

        .login-forgot a {
            font-size: 0.75rem;
            color: #6b7280;
            text-decoration: none;
        }

        .login-forgot a:hover {
            color: #3b82f6;
        }

        /* Submit button */
        .login-btn {
            width: 100%;
            padding: 11px;
            border-radius: 8px;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(59,130,246,0.35);
            transition: opacity .2s, transform .15s;
        }

        .login-btn:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        /* Error */
        .login-error {
            color: #ef4444;
            font-size: 0.72rem;
            margin-top: 4px;
        }

        /* Status alert */
        .login-status {
            font-size: 0.78rem;
            color: #16a34a;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 14px;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .login-card { flex-direction: column; border-radius: 16px; }
            .login-visual { width: 100%; height: 220px; }
            .login-form-panel { padding: 28px 24px; }
        }
    </style>
</head>

<body style="background:#f0f4f8; margin:0;">

    @if ($attributes->get('split'))
    <div class="login-wrapper">
        <div class="login-card">

            {{-- Panel kiri: visual --}}
            <div class="login-visual">
                <img src="{{ asset('images/login-bg.jpg') }}" alt="Login Visual">
                <div class="login-visual-overlay"></div>
                <div class="login-visual-caption">
                    <p>Welcome Pikmin</p>
                    <p>&copy;2026 SPJ MKDI</p>
                    <div class="login-visual-dots">
                        <span class="active"></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>

            {{-- Panel kanan: form --}}
            <div class="login-form-panel">
                <div class="login-form-inner">
                    {{ $slot }}
                </div>
            </div>

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