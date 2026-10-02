<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $attributes->get('title') ? $attributes->get('title') . ' | ' : '' }}{{ config('app.name', 'Laravel') }}
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #eaedfa;
        }

        /* ===== CITY BACKGROUND & LOGIN LAYOUT ===== */
        .city-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            background: #eef2ff;
        }

        .city-bg-scene {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }

        .city-bg-scene svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Konten Utama */
        .city-main {
            position: relative;
            z-index: 10;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 16px 50px;
        }

        /* Kartu form */
        .city-card {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(30, 45, 110, 0.14);
            padding: 36px 36px 30px;
            width: 100%;
            max-width: 400px;
        }

        .city-card-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1a1f36;
            text-align: center;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }

        .city-card-sub {
            font-size: 0.82rem;
            color: #6b7280;
            text-align: center;
            margin-bottom: 24px;
        }

        .city-field {
            position: relative;
            margin-bottom: 16px;
        }

        .city-field label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .city-field .field-wrap {
            position: relative;
        }

        .city-field .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            color: #6b7280;
            pointer-events: none;
        }

        .city-field input {
            width: 100%;
            padding: 11px 40px 11px 40px;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-size: 0.85rem;
            color: #111827;
            background: #ffffff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .city-field input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            background: #ffffff;
        }

        .city-field input::placeholder {
            color: #9ca3af;
        }

        .city-field .eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            display: flex;
            align-items: center;
            padding: 0;
        }

        .city-field .eye-btn:hover {
            color: #2563eb;
        }

        .city-error {
            font-size: 0.72rem;
            color: #ef4444;
            margin-top: 4px;
        }

        .city-status {
            font-size: 0.78rem;
            color: #16a34a;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 16px;
        }

        .city-btn {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 10px;
            background: #1e293b;
            color: #ffffff;
            font-size: 0.88rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            letter-spacing: 0.03em;
            transition: background .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(30, 41, 59, 0.25);
        }

        .city-btn:hover {
            background: #334155;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(30, 41, 59, 0.32);
        }

        .city-copy {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 500;
            color: #4b5563;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            padding: 5px 16px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            white-space: nowrap;
        }

        @media (max-width: 480px) {
            .city-card {
                padding: 28px 20px 24px;
            }
        }
    </style>
</head>

<body>

    @if ($attributes->get('split'))
        <div class="city-page">
            <div class="city-bg-scene">
                <x-city-skyline />
            </div>
            <div class="city-main">
                <div class="city-card">
                    {{ $slot }}
                </div>
            </div>
            <div class="city-copy">&copy;2026 SPJ MKDI</div>
        </div>

    @else
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div><a href="/"><x-application-logo class="w-20 h-20 fill-current text-gray-500" /></a></div>
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">{{ $slot }}
            </div>
        </div>
    @endif

</body>

</html>