<x-app-layout title="Detail Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Disposisi</h2>
    </x-slot>

    @php
        $badgeSifat = [
            'biasa' => 'bg-gray-100 text-gray-600',
            'segera' => 'bg-amber-100 text-amber-800',
            'sangat_segera' => 'bg-red-100 text-red-700',
            'rahasia' => 'bg-purple-100 text-purple-700',
        ];
        $tgl = fn($d) => $d ? $d->translatedFormat('d M Y') : '–';
        $input = 'w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none';
        // Form mana yang dibuka lagi setelah validasi gagal
        $formLama = in_array(old('_aksi'), ['teruskan', 'selesai'], true) ? old('_aksi') : '';
        $idLama = (int) old('_disposisi');
    @endphp

    <div class="py-6 sm:py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">

        <div class="flex flex-wrap items-center justify-between gap-2">
            <a href="{{ $bolehTeruskan ? route('disposisi.index') : route('kalender.index') }}"
                class="text-sm text-gray-500 hover:text-gray-800">&larr; {{ $bolehTeruskan ? 'Semua Disposisi' : 'Kalender Disposisi' }}</a>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('disposisi.lembar', $surat) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Lembar Disposisi (PDF)
                </a>
                @if ($bolehHapus)
                    <form method="POST" action="{{ route('disposisi.destroy', $surat) }}"
                        onsubmit="return confirm('Hapus surat ini beserta seluruh disposisinya?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-xl border border-red-200 bg-white px-3.5 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Kiri: data surat + posisi --}}
            <div class="space-y-5">
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $badgeSifat[$surat->sifat] ?? 'bg-gray-100 text-gray-600' }}">{{ $surat->sifat_label }}</span>
                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $surat->status === 'selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                            {{ $surat->status === 'selesai' ? 'Selesai' : 'Proses' }}</span>
                    </div>
                    <h3 class="mt-2 text-base font-semibold text-gray-900">{{ $surat->perihal }}</h3>
                    <dl class="mt-3 space-y-1.5 text-sm">
                        @foreach ([
                            'Nomor surat' => $surat->nomor_surat,
                            'Asal surat' => $surat->asal_surat,
                            'Tanggal surat' => $tgl($surat->tanggal_surat),
                            'Diterima' => $tgl($surat->tanggal_terima),
                            'Nomor agenda' => $surat->nomor_agenda ?: '–',
                            'Lampiran' => $surat->lampiran ?: '–',
                            'Jadwal' => $surat->tanggal_kegiatan
                                ? $tgl($surat->tanggal_kegiatan) . ($surat->waktu_label ? ', ' . $surat->waktu_label : '') . ($surat->tempat ? ' · ' . $surat->tempat : '')
                                : '–',
                            'Dicatat oleh' => ($surat->pembuat?->name ?? '-') . ($surat->unit ? ' · ' . $surat->unit->nama : ''),
                        ] as $k => $v)
                            <div class="flex gap-3">
                                <dt class="w-28 shrink-0 text-gray-500">{{ $k }}</dt>
                                <dd class="text-gray-800 break-words">{{ $v }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @if ($surat->file_path)
                        <a href="{{ route('disposisi.berkas', $surat) }}" target="_blank"
                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-50 px-3.5 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100">
                            Lihat file surat
                            <span class="text-xs font-normal text-blue-500">{{ $surat->file_name }}</span>
                        </a>
                    @endif
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Posisi surat sekarang</h3>
                    <p class="text-xs text-gray-500">Penerima yang belum meneruskan / menyelesaikan.</p>
                    <ul class="mt-3 space-y-2">
                        @forelse ($posisi as $d)
                            <li class="flex items-start justify-between gap-2 text-sm">
                                <div>
                                    <div class="font-medium text-gray-800">{{ $d->kepada?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $d->kepada?->unit?->nama }}</div>
                                </div>
                                <span class="whitespace-nowrap text-[11px] {{ $d->status === 'baru' ? 'text-red-600' : 'text-amber-600' }}">{{ $d->status_label }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-emerald-700">Semua disposisi sudah ditindaklanjuti.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Kanan: aksi saya + alur --}}
            <div class="lg:col-span-2 space-y-5">
                @foreach ($milikSaya as $m)
                    <div x-data="{ aksi: '{{ $idLama === $m->id ? $formLama : '' }}' }"
                        class="bg-white border-2 border-blue-200 rounded-2xl p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Disposisi untuk Anda</p>
                                <p class="text-sm text-gray-700">Dari <b>{{ $m->dari?->name ?? '-' }}</b>
                                    @if ($m->batas_waktu)
                                        · batas <span class="{{ $m->terlambat ? 'text-red-600 font-semibold' : '' }}">{{ $m->batas_waktu->translatedFormat('d M Y') }}</span>
                                    @endif
                                </p>
                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @foreach ($m->instruksi_label as $ins)
                                        <span class="rounded-md bg-blue-50 px-1.5 py-0.5 text-[11px] text-blue-800">{{ $ins }}</span>
                                    @endforeach
                                </div>
                                @if ($m->catatan)
                                    <p class="mt-1.5 text-sm text-gray-700 whitespace-pre-line">{{ $m->catatan }}</p>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                @if ($bolehTeruskan)
                                <button type="button" @click="aksi = aksi === 'teruskan' ? '' : 'teruskan'"
                                    :class="aksi === 'teruskan' ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 border-blue-200'"
                                    class="rounded-xl border px-3.5 py-2 text-sm font-semibold">Teruskan</button>
                                @endif
                                <button type="button" @click="aksi = aksi === 'selesai' ? '' : 'selesai'"
                                    :class="aksi === 'selesai' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-700 border-emerald-200'"
                                    class="rounded-xl border px-3.5 py-2 text-sm font-semibold">Selesaikan</button>
                            </div>
                        </div>

@if ($bolehTeruskan)
                        <form x-show="aksi === 'teruskan'" x-cloak method="POST" action="{{ route('disposisi.teruskan', $m) }}"
                            class="mt-4 border-t border-gray-100 pt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="_aksi" value="teruskan">
                            <input type="hidden" name="_disposisi" value="{{ $m->id }}">
                            @include('disposisi._tujuan', ['idForm' => 'teruskan-' . $m->id])
                            <div class="flex justify-end">
                                <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Kirim Disposisi</button>
                            </div>
                        </form>
                        @endif

                        <form x-show="aksi === 'selesai'" x-cloak method="POST" action="{{ route('disposisi.selesai', $m) }}"
                            enctype="multipart/form-data" class="mt-4 border-t border-gray-100 pt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="_aksi" value="selesai">
                            <input type="hidden" name="_disposisi" value="{{ $m->id }}">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Laporan tindak lanjut <span class="text-red-400">*</span></label>
                                <textarea name="laporan" rows="3" class="{{ $input }}"
                                    placeholder="Apa yang sudah dikerjakan / hasilnya">{{ $idLama === $m->id ? old('laporan') : '' }}</textarea>
                                @if ($idLama === $m->id)
                                    @error('laporan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Lampiran <span class="font-normal text-gray-400">(opsional, maks 10 MB)</span></label>
                                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                    class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-emerald-700">
                                @if ($idLama === $m->id)
                                    @error('file') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            @if ($m->status === 'diteruskan')
                                <p class="text-xs text-amber-700">Disposisi ini sudah Anda teruskan. Menyelesaikan berarti Anda menutup bagian Anda.</p>
                            @endif
                            <div class="flex justify-end">
                                <button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Tandai Selesai</button>
                            </div>
                        </form>
                    </div>
                @endforeach

                {{-- Feedback --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Feedback</h3>
                    <p class="text-xs text-gray-500 mb-3">Tanggapan dari penerima maupun pengirim disposisi.</p>
                    <ul class="space-y-2.5">
                        @forelse ($surat->feedback as $f)
                            <li class="rounded-xl {{ (int) $f->user_id === (int) auth()->id() ? 'bg-blue-50/60 border-blue-100' : 'bg-gray-50 border-gray-100' }} border px-3.5 py-2.5">
                                <div class="flex flex-wrap items-baseline justify-between gap-2 text-xs">
                                    <span class="font-semibold text-gray-800">{{ $f->user?->name ?? '-' }}
                                        <span class="font-normal text-gray-400">{{ $f->user?->unit?->nama }}</span></span>
                                    <span class="text-gray-400">{{ $f->created_at?->translatedFormat('d M Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $f->isi }}</p>
                            </li>
                        @empty
                            <li class="text-sm text-gray-400">Belum ada feedback.</li>
                        @endforelse
                    </ul>
                    <form method="POST" action="{{ route('disposisi.feedback', $surat) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="isi" rows="2" required maxlength="2000" class="{{ $input }}"
                            placeholder="Tulis feedback, mis. siap hadir / sudah ditindaklanjuti / perlu data tambahan"></textarea>
                        @error('isi') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            @if ($milikSaya->isNotEmpty())
                                <label class="flex items-center gap-2 text-xs text-gray-600">
                                    <input type="checkbox" name="tandai_selesai" value="1"
                                        class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    Sekalian tandai disposisi saya selesai
                                </label>
                            @else
                                <span></span>
                            @endif
                            <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Kirim Feedback</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Alur disposisi</h3>
                    <p class="text-xs text-gray-500 mb-4">Urutan dari pencatat surat sampai penerima terakhir.</p>
                    @if (count($pohon))
                        <ul class="space-y-3">
                            @foreach ($pohon as $n)
                                @include('disposisi._node', ['n' => $n])
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-400">Belum ada disposisi.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
