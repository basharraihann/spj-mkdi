<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Pegawai</h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 mb-4 sm:mb-5 px-1 text-sm">
                <a href="{{ route('pegawais.index') }}"
                    class="text-gray-400 hover:text-gray-600 transition flex items-center gap-1.5 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 10-8 0" />
                    </svg>
                    Pegawai
                </a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-700 font-semibold truncate">{{ $pegawai->nama }}</span>
            </div>

            {{-- Success flash --}}
            @if (session('success'))
                <div
                    class="flex items-center gap-2.5 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75l2.25 2.25 4.5-4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Main card --}}
            <div
                class="relative bg-white shadow-sm hover:shadow-md rounded-2xl border border-gray-100 overflow-hidden transition-shadow duration-300">

                {{-- Decorative header strip --}}
                <div class="relative bg-gradient-to-r from-amber-500 to-orange-600 px-6 sm:px-8 py-6 sm:py-7">
                    <div
                        class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_top_right,white,transparent_60%)]">
                    </div>
                    <div class="relative flex items-center gap-4">
                        {{-- Avatar with initial --}}
                        <div
                            class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm ring-1 ring-white/20">
                            <span class="text-lg font-bold text-white">
                                {{ strtoupper(substr($pegawai->nama ?? '?', 0, 1)) }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg sm:text-xl font-bold text-white truncate">{{ $pegawai->nama }}</h3>
                            <p class="text-sm text-orange-100 mt-0.5 leading-relaxed">
                                Perbarui data pegawai ini.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-6 sm:p-8">

                    @if ($errors->any())
                        <div
                            class="flex gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-6 animate-[shake_0.4s_ease-in-out]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <div>
                                <p class="font-semibold mb-1">Periksa kembali isian kamu:</p>
                                <ul class="space-y-0.5 list-disc list-inside marker:text-red-400">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('pegawais.update', $pegawai) }}" method="POST" class="space-y-8">
                        @csrf
                        @method('PUT')
                        @include('pegawais._form')

                        {{-- Actions --}}
                        <div
                            class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('pegawais.index') }}"
                                class="inline-flex items-center justify-center gap-1.5 border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 font-semibold px-5 py-2.5 rounded-xl text-sm transition">
                                Batal
                            </a>
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-1.5 bg-amber-600 hover:bg-amber-700 active:scale-[0.98] text-white font-semibold px-5 sm:px-6 py-2.5 rounded-xl text-sm shadow-md shadow-amber-600/20 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-xs text-gray-400 text-center mt-5">
                Pastikan NIP dan nama sesuai dokumen resmi sebelum menyimpan.
            </p>
        </div>
    </div>

    <style>
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-4px);
            }

            40%,
            80% {
                transform: translateX(4px);
            }
        }
    </style>
</x-app-layout>