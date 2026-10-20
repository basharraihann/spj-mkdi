@php
    $btn = 'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1';
    $lbl = 'block text-sm font-medium text-slate-600 mb-1';
    $rail = [
        'draf' => 'border-l-slate-300',
        'terbit' => 'border-l-blue-600',
        'selesai' => 'border-l-emerald-500',
        'batal' => 'border-l-red-400',
    ];
@endphp
<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Surat Tugas</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Diurutkan dari yang terbaru.</p>
                    </div>
                    <span class="rounded-full border bg-gray-50 px-3 py-1 text-xs text-gray-600">
                        {{ $items->count() }} dari {{ $items->total() }} surat tugas
                    </span>
                </div>

                <div
                    class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
                    <div>
                        <div class="text-xs font-medium text-blue-700">Nomor ST berikutnya</div>
                        <div class="mt-0.5 break-all text-xl font-semibold tabular-nums text-gray-900">
                            {{ $nomorBerikutnya }}
                        </div>
                        <div class="mt-0.5 text-xs text-gray-500">Nomor final ditetapkan saat surat tugas disimpan.
                            Tahun
                            {{ $tahun }}.
                        </div>
                    </div>
                    <a href="{{ route('surat-tugas.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Buat Surat Tugas
                    </a>
                </div>

                <form method="GET" class="flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-96">
                        <label for="q" class="{{ $lbl }} !text-xs">Cari</label>
                        <input id="q" type="text" name="q" value="{{ request('q') }}"
                            placeholder="Nomor surat atau perihal..."
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <button
                        class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cari</button>
                    @if (request('q'))
                        <a href="{{ route('surat-tugas.index') }}"
                            class="px-2 py-2 text-sm text-gray-500 hover:text-gray-800">Reset</a>
                    @endif
                </form>
            </div>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div
                    class="hidden grid-cols-12 gap-4 border-b border-slate-200 bg-slate-50 px-6 py-3 text-xs font-medium text-slate-500 md:grid">
                    <div class="col-span-3">Nomor dan status</div>
                    <div class="col-span-6">Kegiatan</div>
                    <div class="col-span-3 text-right">Aksi</div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($items as $it)
                        @php
                            $bolehKelola = $it->isDraf() && ($isAdmin || (int) $it->created_by === (int) auth()->id());
                        @endphp

                        <div
                            class="grid gap-4 border-l-4 px-6 py-4 transition hover:bg-slate-50/70 md:grid-cols-12 md:items-center {{ $rail[$it->status] ?? 'border-l-slate-300' }}">
                            <div class="min-w-0 md:col-span-3">
                                <a href="{{ route('surat-tugas.show', $it) }}"
                                    class="break-all text-sm font-semibold tabular-nums text-slate-900 hover:text-blue-700 focus:outline-none focus-visible:underline">
                                    @if ($it->nomor)
                                        {{ $it->nomor }}
                                    @else
                                        <span class="font-medium italic text-slate-500">Belum bernomor</span>
                                    @endif
                                </a>
                                <div class="mt-0.5 text-xs text-slate-500">{{ $it->tanggal_indo }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $it->status_badge }}">
                                        {{ $it->status_label }}
                                    </span>
                                    @if ($it->relationLoaded('tusi') && $it->tusi)
                                        <span
                                            class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-xs text-slate-600">
                                            {{ $it->tusi->nama }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0 md:col-span-6">
                                <p class="line-clamp-2 text-sm font-medium leading-snug text-slate-900">
                                    {{ \Illuminate\Support\Str::limit($it->untuk[0] ?? '-', 200) }}
                                </p>
                                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                                    <span>Penandatangan: {{ $it->nama_penandatangan ?: '-' }}</span>
                                    <span>{{ $it->peserta_count }} peserta</span>
                                    @if ($it->rentang_tanggal)
                                        <span>Pelaksanaan: {{ $it->rentang_tanggal }}</span>
                                    @endif
                                    @if ($it->lokasi)
                                        <span>{{ $it->lokasi }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 md:col-span-3 md:justify-end">
                                <a href="{{ route('surat-tugas.show', $it) }}"
                                    class="{{ $btn }} border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Lihat
                                </a>
                                <a href="{{ route('surat-tugas.pdf', $it) }}" target="_blank"
                                    class="{{ $btn }} border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    PDF
                                </a>
                                @if ($bolehKelola)
                                    <a href="{{ route('surat-tugas.edit', $it) }}"
                                        class="{{ $btn }} border-amber-200 bg-white text-amber-700 hover:bg-amber-50">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('surat-tugas.destroy', $it) }}" class="inline"
                                        onsubmit="return confirm('Hapus surat tugas ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="{{ $btn }} border-red-200 bg-white text-red-600 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-14 text-center">
                            @if (request('q'))
                                <p class="font-medium text-slate-700">Tidak ada surat tugas yang cocok dengan
                                    "{{ request('q') }}".</p>
                                <a href="{{ route('surat-tugas.index') }}"
                                    class="mt-2 inline-block text-sm text-blue-700 hover:underline">Tampilkan semua
                                    surat tugas</a>
                            @else
                                <p class="font-medium text-slate-700">Belum ada surat tugas.</p>
                                <a href="{{ route('surat-tugas.create') }}"
                                    class="mt-2 inline-block text-sm text-blue-700 hover:underline">Buat surat tugas
                                    pertama</a>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

            <div>{{ $items->links() }}</div>

            @if ($isAdmin)
                <details class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <summary
                        class="cursor-pointer select-none px-6 py-4 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Atur counter nomor surat tugas
                    </summary>
                    <form method="POST" action="{{ route('surat-tugas.counter') }}"
                        class="flex flex-wrap items-end gap-4 border-t border-slate-100 px-6 py-5 text-sm">
                        @csrf
                        <div>
                            <label class="{{ $lbl }}">Tahun</label>
                            <input type="number" name="tahun" value="{{ $tahun }}"
                                class="w-28 rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="{{ $lbl }}">Nomor terakhir terpakai</label>
                            <input type="number" name="last_number" value="{{ $counter }}"
                                class="w-40 rounded-lg border-slate-300 text-sm">
                        </div>
                        <button
                            class="rounded-lg border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">
                            Simpan counter
                        </button>
                        <p class="text-slate-500">Contoh: isi 203 agar ST berikutnya bernomor 204. Counter hanya boleh
                            naik.</p>
                    </form>
                </details>
            @endif
        </div>
    </div>
</x-app-layout>