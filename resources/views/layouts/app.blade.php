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

    <!-- SweetAlert2 (dipakai untuk konfirmasi hapus yang seragam di semua halaman) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased" x-data="{ sidebarOpen: false, sidebarCollapsed: true }">
    <div class="min-h-screen bg-gray-100 flex">

        @include('layouts.navigation')

        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-30 lg:hidden">
        </div>

        <div x-cloak class="flex-1 flex flex-col min-w-0 transition-all duration-200"
            :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-72'">

            <!-- Top bar (logo + mobile toggle + user menu) -->
            <div class="bg-white border-b border-gray-100 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">

                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true"
                        class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <x-application-logo class="h-8 w-auto" />
                </div>

                <div class="ms-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{-- Flash message global (success & error).
                Datanya cuma dititip lewat data-attribute yang disembunyikan di sini,
                lalu ditampilkan sebagai toast modern oleh script di bawah (lihat #flash-data).
                TIDAK perlu lagi nulis blok @if(session('success')) ... @endif di tiap Blade view. --}}
                <div id="flash-data" class="hidden" data-success="{{ session('success') }}"
                    data-error="{{ session('error') }}"></div>

                {{ $slot }}
            </main>
        </div>
    </div>

    {{--
    Fungsi konfirmasi hapus global.
    Dipakai di SEMUA tombol Hapus di seluruh aplikasi (Pegawai, Nomor Memo, dll)
    supaya tampilannya konsisten, bukan confirm() bawaan browser.

    Cara pakai di Blade manapun (contoh, JANGAN taruh di file ini):

    <form id="delete-pegawai-XXX" action="URL_DESTROY" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <button type="button" onclick="confirmDelete('delete-pegawai-XXX', 'pegawai NAMA')">
        Hapus
    </button>
    --}}
    <style>
        .swal-delete-popup {
            border-radius: 1.25rem !important;
            padding: 2rem 1.5rem !important;
        }

        .swal-delete-icon-wrap {
            width: 84px;
            height: 84px;
            margin: 0 auto 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .swal-delete-icon-wrap svg {
            width: 100%;
            height: 100%;
        }

        .swal-delete-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1f2937 !important;
            margin-bottom: 0.375rem !important;
        }

        .swal-delete-text {
            font-size: 0.9rem !important;
            color: #9ca3af !important;
            line-height: 1.4 !important;
        }

        .swal-delete-actions {
            gap: 0.75rem !important;
            width: 100%;
            margin-top: 1.5rem !important;
        }

        .swal-btn-cancel {
            flex: 1;
            border: 2px solid #fecaca !important;
            background: #ffffff !important;
            color: #ef4444 !important;
            font-weight: 600 !important;
            border-radius: 0.75rem !important;
            padding: 0.7rem 1rem !important;
            box-shadow: none !important;
            transition: background 0.15s ease;
        }

        .swal-btn-cancel:hover {
            background: #fef2f2 !important;
        }

        .swal-btn-confirm {
            flex: 1;
            border: none !important;
            background: #ef4444 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-radius: 0.75rem !important;
            padding: 0.7rem 1rem !important;
            box-shadow: none !important;
            transition: background 0.15s ease;
        }

        .swal-btn-confirm:hover {
            background: #dc2626 !important;
        }

        /* Toast flash message (success/error) */
        .app-toast-popup {
            border-radius: 0.875rem !important;
            padding: 0.85rem 1.1rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        }

        .app-toast-inner {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .app-toast-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }

        .app-toast-msg {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1f2937;
        }

        .app-toast-progress-success {
            background: #16a34a !important;
        }

        .app-toast-progress-error {
            background: #dc2626 !important;
        }
    </style>

    <script>
        function confirmDelete(formId, itemLabel = 'data ini') {
            const trashIconSvg = `
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="46" fill="#FEF2F2"/>
                    <rect x="30" y="38" width="40" height="38" rx="4" fill="#EF4444"/>
                    <rect x="24" y="30" width="52" height="8" rx="3" fill="#EF4444"/>
                    <rect x="40" y="20" width="20" height="10" rx="3" fill="#EF4444"/>
                    <line x1="40" y1="46" x2="40" y2="68" stroke="#FEF2F2" stroke-width="3" stroke-linecap="round"/>
                    <line x1="50" y1="46" x2="50" y2="68" stroke="#FEF2F2" stroke-width="3" stroke-linecap="round"/>
                    <line x1="60" y1="46" x2="60" y2="68" stroke="#FEF2F2" stroke-width="3" stroke-linecap="round"/>
                </svg>
            `;

            Swal.fire({
                html: `
                    <div class="swal-delete-icon-wrap">${trashIconSvg}</div>
                    <div class="swal-delete-title">Hapus ${itemLabel}?</div>
                    <div class="swal-delete-text">Data yang sudah dihapus tidak bisa dikembalikan.</div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                buttonsStyling: false,
                focusConfirm: false,
                customClass: {
                    popup: 'swal-delete-popup',
                    actions: 'swal-delete-actions',
                    confirmButton: 'swal-btn-confirm',
                    cancelButton: 'swal-btn-cancel',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }

        // Toast modern untuk flash message (session success/error).
        // Otomatis muncul di pojok kanan atas, slide-in, lalu hilang sendiri setelah beberapa detik.
        function showToast(message, type = 'success') {
            const isSuccess = type === 'success';

            const iconSvg = isSuccess
                ? `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="12" fill="#DCFCE7"/>
                        <path d="M7 12.5l3 3 7-7" stroke="#16A34A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                   </svg>`
                : `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="12" fill="#FEE2E2"/>
                        <path d="M15 9l-6 6M9 9l6 6" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                   </svg>`;

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'app-toast-popup',
                    timerProgressBar: isSuccess ? 'app-toast-progress-success' : 'app-toast-progress-error',
                },
                didOpen: (toastEl) => {
                    toastEl.addEventListener('mouseenter', Swal.stopTimer);
                    toastEl.addEventListener('mouseleave', Swal.resumeTimer);
                },
            });

            Toast.fire({
                html: `
                    <div class="app-toast-inner">
                        <span class="app-toast-icon">${iconSvg}</span>
                        <span class="app-toast-msg">${message}</span>
                    </div>
                `,
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const flash = document.getElementById('flash-data');
            if (!flash) return;

            const successMsg = flash.dataset.success;
            const errorMsg = flash.dataset.error;

            if (successMsg) showToast(successMsg, 'success');
            if (errorMsg) showToast(errorMsg, 'error');
        });
    </script>
</body>

</html>