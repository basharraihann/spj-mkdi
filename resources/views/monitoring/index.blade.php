@php
    $kartu = 'bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden';
    $kepala = 'px-4 py-3 border-b border-slate-100 bg-slate-50/60 text-xs font-bold text-blue-900 uppercase tracking-wider';
    $total = fn ($koleksi, $kunci) => (int) ($koleksi[$kunci] ?? 0);
    $adaTugas = $disposisiSaya->isNotEmpty() || $suratKeluarSaya->isNotEmpty() || $kontrolSaya->isNotEmpty() || $laporanSaya->isNotEmpty();
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">{{ __('Monitoring & Notifikasi') }}</h2>
            @if ($lihatSemua)
                <a href="{{ route('monitoring.export') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl shadow-sm transition">
                    Unduh Rekap Excel (.xlsx)
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- PERLU TINDAKAN SAYA --}}
        <div class="{{ $kartu }}">
            <div class="{{ $kepala }}">Perlu Tindakan Saya</div>
            <div class="p-4 space-y-5 text-sm text-slate-700">
                @unless ($adaTugas)
                    <p class="text-xs text-slate-400">Tidak ada yang menunggu tindakan Anda saat ini.</p>
                @endunless

                @if ($disposisiSaya->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-1">Disposisi masuk ({{ $disposisiSaya->count() }})</p>
                        <ul class="divide-y divide-slate-100">
                            @foreach ($disposisiSaya as $d)
                                <li class="py-2 flex flex-wrap items-center justify-between gap-2">
                                    <span>{{ $d->suratMasuk->nomor_agenda ?? '-' }} &mdash; {{ \Illuminate\Support\Str::limit($d->suratMasuk->perihal ?? '', 70) }}
                                        <span class="text-[11px] text-slate-400">({{ ucfirst($d->status) }}@if ($d->tenggat_waktu), tenggat {{ \Carbon\Carbon::parse($d->tenggat_waktu)->format('d/m/Y') }}@endif)</span>
                                    </span>
                                    <a href="{{ route('disposisi.show', $d->id) }}" class="text-xs font-medium text-blue-600 hover:underline">Buka</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($suratKeluarSaya->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-1">Surat keluar ({{ $suratKeluarSaya->count() }})</p>
                        <ul class="divide-y divide-slate-100">
                            @foreach ($suratKeluarSaya as [$s, $ket])
                                <li class="py-2 flex flex-wrap items-center justify-between gap-2">
                                    <span>{{ $s->nama_jenis }}: {{ \Illuminate\Support\Str::limit($s->hal, 70) }} <span class="text-[11px] text-slate-400">&mdash; {{ $ket }}</span></span>
                                    <a href="{{ route('surat-keluar.show', $s->id) }}" class="text-xs font-medium text-blue-600 hover:underline">Buka</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($kontrolSaya->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-1">Lembar kontrol Surat Tugas ({{ $kontrolSaya->count() }})</p>
                        <ul class="divide-y divide-slate-100">
                            @foreach ($kontrolSaya as $k)
                                <li class="py-2 flex flex-wrap items-center justify-between gap-2">
                                    <span>ST {{ $k->suratTugas->nomor ?: '(draf #' . $k->surat_tugas_id . ')' }} <span class="text-[11px] text-slate-400">&mdash; {{ $k->tahap_label }}</span></span>
                                    <a href="{{ route('surat-tugas.show', $k->surat_tugas_id) }}" class="text-xs font-medium text-blue-600 hover:underline">Buka</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($laporanSaya->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-1">Laporan Surat Tugas yang harus diisi ({{ $laporanSaya->count() }})</p>
                        <ul class="divide-y divide-slate-100">
                            @foreach ($laporanSaya as $l)
                                <li class="py-2 flex flex-wrap items-center justify-between gap-2">
                                    <span>ST {{ $l->suratTugas->nomor ?? '-' }}</span>
                                    <a href="{{ route('surat-tugas.show', $l->surat_tugas_id) }}" class="text-xs font-medium text-blue-600 hover:underline">Isi laporan</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- REKAP STATUS --}}
        @if ($rekap)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="{{ $kartu }}">
                    <div class="{{ $kepala }}">Surat Masuk &amp; Disposisi</div>
                    <div class="p-4 grid grid-cols-2 gap-3 text-xs">
                        @foreach (['baru' => 'Baru', 'didisposisi' => 'Didisposisi', 'selesai' => 'Selesai'] as $k => $lb)
                            <div class="rounded-xl bg-slate-50 p-3"><div class="text-slate-500">Surat {{ $lb }}</div><div class="text-xl font-bold text-slate-800">{{ $total($rekap['surat_masuk_status'], $k) }}</div></div>
                        @endforeach
                        <div class="rounded-xl bg-red-50 p-3"><div class="text-red-600">Disposisi lewat tenggat</div><div class="text-xl font-bold text-red-700">{{ $rekap['disposisi_lewat_tenggat'] }}</div></div>
                        @foreach (\App\Models\SuratMasuk::JALUR_LABEL as $k => $lb)
                            <div class="rounded-xl bg-blue-50/60 p-3"><div class="text-slate-500">{{ $lb }}</div><div class="text-xl font-bold text-slate-800">{{ $total($rekap['surat_masuk_jalur'], $k) }}</div></div>
                        @endforeach
                        @foreach (['baru' => 'Baru', 'dibaca' => 'Dibaca', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $k => $lb)
                            <div class="rounded-xl bg-slate-50 p-3"><div class="text-slate-500">Disposisi {{ $lb }}</div><div class="text-xl font-bold text-slate-800">{{ $total($rekap['disposisi_status'], $k) }}</div></div>
                        @endforeach
                    </div>
                </div>

                <div class="{{ $kartu }}">
                    <div class="{{ $kepala }}">Surat Keluar, Surat Tugas &amp; Laporan (SPJ)</div>
                    <div class="p-4 grid grid-cols-2 gap-3 text-xs">
                        @foreach (\App\Models\SuratKeluar::STATUS_LABEL as $k => $lb)
                            <div class="rounded-xl bg-slate-50 p-3"><div class="text-slate-500">Surat Keluar: {{ $lb }}</div><div class="text-xl font-bold text-slate-800">{{ $total($rekap['surat_keluar'], $k) }}</div></div>
                        @endforeach
                        @foreach (\App\Models\SuratTugas::STATUS_LABEL as $k => $lb)
                            <div class="rounded-xl bg-blue-50/60 p-3"><div class="text-slate-500">Surat Tugas: {{ $lb }}</div><div class="text-xl font-bold text-slate-800">{{ $total($rekap['surat_tugas'], $k) }}</div></div>
                        @endforeach
                        <div class="rounded-xl bg-amber-50 p-3 col-span-2"><div class="text-amber-700">ST terbit, laporan/SPJ belum dikirim</div><div class="text-xl font-bold text-amber-800">{{ $rekap['laporan_menunggu'] }}</div></div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
