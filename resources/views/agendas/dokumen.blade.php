<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Upload Dokumen — {{ $agenda->uraian_kegiatan }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 px-1 text-sm">
                <a href="{{ route('agendas.index') }}" class="text-gray-400 hover:text-gray-600 transition">Agenda</a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <a href="{{ route('agendas.show', $agenda) }}"
                    class="text-gray-400 hover:text-gray-600 transition truncate max-w-[10rem]">{{ $agenda->uraian_kegiatan }}</a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-700 font-semibold">Dokumen</span>
            </div>

            {{-- Kartu utama --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 sm:p-8 space-y-6">

                @php
                    $totalDokumen = count($kategoriList);
                    $terupload = collect($kategoriList)
                        ->keys()
                        ->filter(fn($key) => $agenda->dokumen($key))
                        ->count();
                @endphp

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Dokumen Pendukung</h3>
                        <p class="text-sm text-gray-400">Upload {{ $totalDokumen }} dokumen pendukung. Upload ulang
                            otomatis mengganti file lama.</p>
                    </div>
                    <span
                        class="flex-shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $terupload === $totalDokumen ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                        {{ $terupload }}/{{ $totalDokumen }} terupload
                    </span>
                </div>

                {{-- Progress bar --}}
                <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full transition-all"
                        style="width: {{ $totalDokumen > 0 ? ($terupload / $totalDokumen) * 100 : 0 }}%"></div>
                </div>

                {{-- Daftar dokumen --}}
                <div class="space-y-3">
                    @foreach ($kategoriList as $key => $label)
                        @php $existing = $agenda->dokumen($key); @endphp

                        <div
                            class="border border-gray-100 rounded-xl p-4 sm:p-5 {{ $existing ? 'bg-white' : 'bg-gray-50/60' }} transition">
                            <div class="flex items-start sm:items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0 {{ $existing ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                                        @if ($existing)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="font-semibold text-gray-800 text-sm">{{ $label }}</span>
                                </div>
                                <span
                                    class="flex-shrink-0 text-xs font-semibold {{ $existing ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $existing ? 'Sudah diupload' : 'Belum diupload' }}
                                </span>
                            </div>

                            @if ($existing)
                                <div
                                    class="flex items-center justify-between gap-3 text-sm bg-gray-50 border border-gray-100 rounded-lg px-3.5 py-2.5 mb-3">
                                    <a href="{{ asset('storage/' . $existing->file_path) }}" target="_blank"
                                        class="flex items-center gap-2 text-blue-600 hover:text-blue-700 transition min-w-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                        </svg>
                                        <span class="truncate font-medium">{{ $existing->nama_file }}</span>
                                    </a>
                                    <x-delete-button :action="route('agendas.dokumen.destroy', [$agenda, $existing])"
                                        :label="'file ini'" :id="'dokumen-' . $existing->id"
                                        class="flex-shrink-0 inline-flex items-center gap-1 text-red-500 hover:text-red-600 text-xs font-semibold transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Hapus
                                    </x-delete-button>
                                </div>
                            @endif

                            <form action="{{ route('agendas.dokumen.store', $agenda) }}" method="POST"
                                enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2">
                                @csrf
                                <input type="hidden" name="kategori" value="{{ $key }}">
                                <input type="file" name="file" required
                                    class="flex-1 text-sm text-gray-500 border border-gray-200 rounded-lg
                                                   file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0
                                                   file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-600
                                                   hover:file:bg-gray-200 file:transition cursor-pointer
                                                   focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-sm transition flex-shrink-0">
                                    @if ($existing)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Ganti
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        Upload
                                    @endif
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('agendas.show', $agenda) }}"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 transition pt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Agenda
                </a>
            </div>
        </div>
    </div>
</x-app-layout>