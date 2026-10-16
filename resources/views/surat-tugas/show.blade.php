<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight break-all">Surat Tugas {{ $st->nomor_tampil }}</h2>
            <span class="px-3 py-1 rounded-full text-xs {{ $st->status_badge }}">{{ $st->status_label }}</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @foreach (['success' => 'green', 'error' => 'red'] as $k => $c)
                @if (session($k))
                    <div class="rounded-lg bg-{{ $c }}-50 border border-{{ $c }}-200 text-{{ $c }}-700 text-sm p-3">{{ session($k) }}</div>
                @endif
            @endforeach

            {{-- Aksi --}}
            <div class="flex flex-wrap gap-2" x-data="{ batal: false }">
                <a href="{{ route('surat-tugas.pdf', $st) }}" target="_blank" class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm">Lihat PDF</a>
                <a href="{{ route('surat-tugas.pdf', [$st, 'download' => 1]) }}" class="px-4 py-2 rounded-lg border text-sm">Unduh PDF</a>

                @if ($st->isDraf())
                    <a href="{{ route('surat-tugas.edit', $st) }}" class="px-4 py-2 rounded-lg border text-sm">Edit</a>
                    <form method="POST" action="{{ route('surat-tugas.terbitkan', $st) }}" onsubmit="return confirm('Terbitkan ST? Nomor akan dibuat otomatis dan ST dikunci.')">
                        @csrf
                        <button class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">Terbitkan &amp; Beri Nomor</button>
                    </form>
                    @if (! $st->nomor)
                        <form method="POST" action="{{ route('surat-tugas.destroy', $st) }}" onsubmit="return confirm('Hapus draf ini?')">
                            @csrf @method('DELETE')
                            <button class="px-4 py-2 rounded-lg border border-red-300 text-red-600 text-sm">Hapus Draf</button>
                        </form>
                    @endif
                @endif

                @if ($st->status === 'terbit')
                    <form method="POST" action="{{ route('surat-tugas.selesai', $st) }}">
                        @csrf
                        <button class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm">Tandai Selesai</button>
                    </form>
                @endif

                @if (in_array($st->status, ['draf', 'terbit']))
                    <button type="button" @click="batal = !batal" class="px-4 py-2 rounded-lg border border-red-300 text-red-600 text-sm">Batalkan</button>
                    <form x-show="batal" x-cloak method="POST" action="{{ route('surat-tugas.batal', $st) }}" class="flex flex-col sm:flex-row gap-2 w-full">
                        @csrf
                        <input type="text" name="alasan_batal" placeholder="Alasan pembatalan (opsional)" class="flex-1 rounded-lg border-gray-300 text-sm">
                        <button class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm">Konfirmasi Batal</button>
                    </form>
                @endif

                <a href="{{ route('surat-tugas.index') }}" class="px-4 py-2 rounded-lg border text-sm sm:ml-auto">Kembali</a>
            </div>

            @if ($st->status === 'batal')
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm p-3">
                    Dibatalkan {{ optional($st->dibatalkan_at)->format('d/m/Y H:i') }}.
                    {{ $st->alasan_batal ? 'Alasan: '.$st->alasan_batal : '' }}
                    @if ($st->nomor) Nomor {{ $st->nomor }} tidak akan dipakai ulang. @endif
                </div>
            @endif

            {{-- Detail --}}
            <div class="bg-white rounded-xl shadow-sm border p-6 space-y-5 text-sm">
                <div class="grid md:grid-cols-3 gap-4">
                    <div><div class="text-gray-500">Nomor</div><div class="font-medium">{{ $st->nomor_tampil }}</div></div>
                    <div><div class="text-gray-500">Tempat, Tanggal</div><div class="font-medium">{{ $st->tempat }}, {{ $st->tanggal_indo }}</div></div>
                    <div><div class="text-gray-500">Penandatangan</div><div class="font-medium">{{ $st->nama_penandatangan }}</div><div class="text-gray-500">{{ $st->jabatan_penandatangan }}</div></div>
                </div>

                <div>
                    <div class="text-gray-500">Menimbang</div>
                    <p>{{ $st->menimbang ?: '-' }}</p>
                </div>

                <div>
                    <div class="text-gray-500">Dasar</div>
                    <ol class="list-decimal pl-5 space-y-1">@forelse ($st->dasar ?? [] as $d)<li>{{ $d }}</li>@empty<li class="list-none -ml-5">-</li>@endforelse</ol>
                </div>

                <div>
                    <div class="text-gray-500">Kepada</div>
                    <p>{{ $st->kepada }}</p>
                </div>

                <div>
                    <div class="text-gray-500">Untuk</div>
                    <ol class="list-decimal pl-5 space-y-1">@foreach ($st->untuk ?? [] as $u)<li>{{ $u }}</li>@endforeach</ol>
                </div>

                <div>
                    <div class="text-gray-500 mb-1">Peserta</div>
                    <div class="overflow-x-auto"><table class="min-w-full border text-left">
                        <thead class="bg-gray-50"><tr><th class="px-3 py-2 w-10">No</th><th class="px-3 py-2">Nama / NIP</th><th class="px-3 py-2">Jabatan</th></tr></thead>
                        <tbody class="divide-y">
                            @foreach ($st->peserta as $i => $p)
                                <tr>
                                    <td class="px-3 py-2">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2">{{ $p->nama }}@if ($p->nip)<div class="text-gray-500">NIP. {{ $p->nip }}</div>@endif</td>
                                    <td class="px-3 py-2">{{ $p->jabatan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>