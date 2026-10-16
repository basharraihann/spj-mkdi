@php
    // Warna status dibuat langsung (inline) supaya selalu tampil, tidak bergantung pada build CSS
    $badge = [
        'draf'    => 'background:#f3f4f6;color:#374151;',
        'terbit'  => 'background:#dbeafe;color:#1d4ed8;',
        'selesai' => 'background:#dcfce7;color:#15803d;',
        'batal'   => 'background:#fee2e2;color:#b91c1c;',
    ][$st->status] ?? 'background:#f3f4f6;color:#374151;';
    $label = 'text-xs font-semibold uppercase tracking-wide text-gray-500';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight break-all">Surat Tugas {{ $st->nomor_tampil }}</h2>
            <span class="px-3 py-1 rounded-full text-xs font-medium" style="{{ $badge }}">{{ $st->status_label }}</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="rounded-lg border text-sm p-3" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d;">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-lg border text-sm p-3" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">{{ session('error') }}</div>
            @endif

            {{-- Aksi --}}
            <div class="flex flex-wrap items-center gap-2" x-data="{ batal: false }">
                <a href="{{ route('surat-tugas.pdf', $st) }}" target="_blank" class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm">Lihat PDF</a>
                <a href="{{ route('surat-tugas.pdf', [$st, 'download' => 1]) }}" class="px-4 py-2 rounded-lg border bg-white text-sm">Unduh PDF</a>

                @if ($st->isDraf())
                    <a href="{{ route('surat-tugas.edit', $st) }}" class="px-4 py-2 rounded-lg border bg-white text-sm">Edit</a>
                    <form method="POST" action="{{ route('surat-tugas.terbitkan', $st) }}" onsubmit="return confirm('Terbitkan ST? Nomor akan dibuat otomatis dan ST dikunci.')">
                        @csrf
                        <button class="px-4 py-2 rounded-lg text-sm" style="background:#2563eb;color:#fff;">Terbitkan &amp; Beri Nomor</button>
                    </form>
                    @if (! $st->nomor)
                        <form method="POST" action="{{ route('surat-tugas.destroy', $st) }}" onsubmit="return confirm('Hapus draf ini?')">
                            @csrf @method('DELETE')
                            <button class="px-4 py-2 rounded-lg border border-red-300 bg-white text-red-600 text-sm">Hapus Draf</button>
                        </form>
                    @endif
                @endif

                @if ($st->status === 'terbit')
                    <form method="POST" action="{{ route('surat-tugas.selesai', $st) }}">
                        @csrf
                        <button class="px-4 py-2 rounded-lg text-sm" style="background:#16a34a;color:#fff;">Tandai Selesai</button>
                    </form>
                @endif

                @if (in_array($st->status, ['draf', 'terbit']))
                    <button type="button" @click="batal = !batal" class="px-4 py-2 rounded-lg border border-red-300 bg-white text-red-600 text-sm">Batalkan</button>
                    <form x-show="batal" x-cloak method="POST" action="{{ route('surat-tugas.batal', $st) }}" class="flex flex-col sm:flex-row gap-2 w-full">
                        @csrf
                        <input type="text" name="alasan_batal" placeholder="Alasan pembatalan (opsional)" class="flex-1 rounded-lg border-gray-300 text-sm">
                        <button class="px-4 py-2 rounded-lg text-sm" style="background:#dc2626;color:#fff;">Konfirmasi Batal</button>
                    </form>
                @endif

                <a href="{{ route('surat-tugas.index') }}" class="px-4 py-2 rounded-lg border bg-white text-sm sm:ml-auto">Kembali</a>
            </div>

            @if ($st->status === 'batal')
                <div class="rounded-lg border text-sm p-3" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
                    Dibatalkan {{ optional($st->dibatalkan_at)->format('d/m/Y H:i') }}.
                    {{ $st->alasan_batal ? 'Alasan: '.$st->alasan_batal : '' }}
                    @if ($st->nomor) Nomor {{ $st->nomor }} tidak akan dipakai ulang. @endif
                </div>
            @endif

            {{-- Detail --}}
            <div class="bg-white rounded-xl shadow-sm border p-5 sm:p-6 space-y-6 text-sm">

                <div class="grid md:grid-cols-3 gap-4 rounded-lg bg-gray-50 p-4">
                    <div><div class="{{ $label }}">Nomor</div><div class="mt-1 font-medium break-all">{{ $st->nomor_tampil }}</div></div>
                    <div><div class="{{ $label }}">Tempat, Tanggal</div><div class="mt-1 font-medium">{{ $st->tempat }}, {{ $st->tanggal_indo }}</div></div>
                    <div><div class="{{ $label }}">Penandatangan</div><div class="mt-1 font-medium">{{ $st->nama_penandatangan }}</div><div class="text-gray-500">{{ $st->jabatan_penandatangan }}</div></div>
                </div>

                <div class="grid lg:grid-cols-5 gap-6">
                    <div class="lg:col-span-3 space-y-5 leading-relaxed text-gray-800">
                        <div>
                            <div class="{{ $label }}">Menimbang</div>
                            <p class="mt-1">{{ $st->menimbang ?: '-' }}</p>
                        </div>

                        <div>
                            <div class="{{ $label }}">Dasar</div>
                            <ol class="mt-1 list-decimal pl-5 space-y-1">@forelse ($st->dasar ?? [] as $d)<li>{{ $d }}</li>@empty<li class="list-none -ml-5">-</li>@endforelse</ol>
                        </div>

                        <div>
                            <div class="{{ $label }}">Kepada</div>
                            <p class="mt-1">{{ $st->kepada }}</p>
                        </div>

                        <div>
                            <div class="{{ $label }}">Untuk</div>
                            <ol class="mt-1 list-decimal pl-5 space-y-1">@foreach ($st->untuk ?? [] as $u)<li>{{ $u }}</li>@endforeach</ol>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="{{ $label }} mb-2">Peserta ({{ $st->peserta->count() }})</div>
                        <div class="overflow-x-auto rounded-lg border">
                            <table class="min-w-full text-left">
                                <thead class="bg-gray-50 text-xs text-gray-500"><tr><th class="px-3 py-2 w-10 font-medium">No</th><th class="px-3 py-2 font-medium">Nama / NIP</th><th class="px-3 py-2 font-medium">Jabatan</th></tr></thead>
                                <tbody class="divide-y">
                                    @foreach ($st->peserta as $i => $p)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
                                            <td class="px-3 py-2">{{ $p->nama }}@if ($p->nip)<div class="text-xs text-gray-500">NIP. {{ $p->nip }}</div>@endif</td>
                                            <td class="px-3 py-2">{{ $p->jabatan }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>