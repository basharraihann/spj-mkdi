@php
    $btn = 'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition';
    $lbl = 'block text-sm font-medium text-gray-600 mb-1';
@endphp
<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Header + kartu info + filter --}}
            <div class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 space-y-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900">Surat Tugas</h2>
                        <p class="text-sm text-gray-500 mt-1">Diurutkan dari yang terbaru.</p>
                    </div>
                    <span class="rounded-full border bg-gray-50 px-4 py-1.5 text-sm text-gray-600">
                        {{ $items->count() }} dari {{ $items->total() }} surat tugas
                    </span>
                </div>

                <div
                    class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-medium text-blue-700">Nomor ST berikutnya</div>
                        <div class="text-3xl font-semibold text-gray-900 mt-0.5 break-all">{{ $nomorBerikutnya }}</div>
                        <div class="text-sm text-gray-500 mt-1">Nomor final ditetapkan saat surat tugas disimpan. Tahun
                            {{ $tahun }}.
                        </div>
                    </div>
                    <a href="{{ route('surat-tugas.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                        style="background:#2563eb;color:#fff;">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Buat Surat Tugas
                    </a>
                </div>

                <form method="GET" class="flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-96">
                        <label class="{{ $lbl }}">Cari</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nomor surat atau perihal..."
                            class="w-full rounded-xl border-gray-300 text-sm">
                    </div>
                    <button class="rounded-xl border px-4 py-2 text-sm font-medium hover:bg-gray-50">Cari</button>
                    @if (request('q'))
                        <a href="{{ route('surat-tugas.index') }}"
                            class="px-2 py-2 text-sm text-gray-500 hover:text-gray-800">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Daftar --}}
            <div
                class="hidden md:grid grid-cols-12 gap-4 px-5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                <div class="col-span-7">Kegiatan</div>
                <div class="col-span-5">Nomor &amp; Aksi</div>
            </div>

            @forelse ($items as $it)
                <div class="bg-white rounded-2xl border shadow-sm p-5 grid md:grid-cols-12 gap-4">
                    {{-- Kiri: kegiatan --}}
                    <div class="md:col-span-7 flex gap-4">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-sm text-gray-600">
                            {{ $items->firstItem() + $loop->index }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span
                                    class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">Surat
                                    Tugas</span>
                                <span class="text-gray-500">{{ $it->tanggal_indo }}</span>
                            </div>
                            <p class="mt-2 font-semibold text-gray-900 leading-snug">
                                {{ \Illuminate\Support\Str::limit($it->untuk[0] ?? '-', 160) }}
                            </p>
                            <dl class="mt-3 grid grid-cols-[auto,1fr] gap-x-6 gap-y-1 text-sm">
                                <dt class="text-gray-400">Penandatangan</dt>
                                <dd class="text-gray-800">{{ $it->nama_penandatangan ?: '-' }}</dd>
                                <dt class="text-gray-400">Peserta</dt>
                                <dd class="text-gray-800">{{ $it->peserta_count }} orang</dd>
                            </dl>
                        </div>
                    </div>

                    {{-- Kanan: nomor + aksi --}}
                    <div class="md:col-span-5">
                        <div class="rounded-xl border bg-gray-50/60 p-4">
                            <div class="font-semibold text-gray-900 break-all">
                                {{ $it->nomor ?? 'Belum bernomor' }}
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <a href="{{ route('surat-tugas.show', $it) }}"
                                    class="{{ $btn }} border-blue-200 bg-white text-blue-700 hover:bg-blue-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Lihat
                                </a>
                                <a href="{{ route('surat-tugas.pdf', $it) }}" target="_blank"
                                    class="{{ $btn }} border-blue-200 bg-white text-blue-700 hover:bg-blue-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    PDF
                                </a>
                                @if ($it->isDraf())
                                    <a href="{{ route('surat-tugas.edit', $it) }}"
                                        class="{{ $btn }} border-amber-200 bg-white text-amber-700 hover:bg-amber-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                        </svg>
                                        Edit
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('surat-tugas.destroy', $it) }}" class="inline"
                                    onsubmit="return confirm('Hapus surat tugas ini? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="{{ $btn }} border-red-200 bg-white text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border shadow-sm p-10 text-center text-gray-500">Belum ada surat tugas.
                </div>
            @endforelse

            <div>{{ $items->links() }}</div>

            {{-- Atur counter (admin) --}}
            @if ($isAdmin)
                <form method="POST" action="{{ route('surat-tugas.counter') }}"
                    class="bg-white rounded-2xl border shadow-sm p-5 flex flex-wrap items-end gap-4 text-sm">
                    @csrf
                    <div>
                        <label class="{{ $lbl }}">Tahun</label>
                        <input type="number" name="tahun" value="{{ $tahun }}"
                            class="rounded-xl border-gray-300 text-sm w-28">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Nomor terakhir terpakai</label>
                        <input type="number" name="last_number" value="{{ $counter }}"
                            class="rounded-xl border-gray-300 text-sm w-40">
                    </div>
                    <button class="rounded-xl border px-4 py-2 font-medium hover:bg-gray-50">Atur counter</button>
                    <p class="text-gray-500">Contoh: isi 203 agar ST berikutnya bernomor 204. Counter hanya boleh naik.</p>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>