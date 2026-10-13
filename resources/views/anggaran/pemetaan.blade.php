<x-app-layout title="Pemetaan Unit">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pemetaan Unit Anggaran') }}
        </h2>
    </x-slot>

    @php
        $inp = 'w-full rounded-lg border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white';
        $btn = 'inline-flex items-center rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition shadow-sm';
        $btnLite = 'inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition';
    @endphp

    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Pemetaan RO ke Unit</h2>
                <p class="mt-1 text-sm text-gray-500">Aturan paling spesifik menang: SubKomponen, lalu Komponen, lalu Kegiatan + RO, lalu RO saja.
                    Perubahan langsung berlaku ke semua data, tanpa upload ulang.</p>
            </div>
            <a href="{{ route('anggaran.index') }}" class="{{ $btnLite }} shrink-0">&larr; Monitor Anggaran</a>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <datalist id="dl-unit">
            @foreach ($units as $u)<option value="{{ $u }}">@endforeach
        </datalist>

        <datalist id="dl-induk">
            @foreach ($induks as $u)<option value="{{ $u }}">@endforeach
        </datalist>

        {{-- Belum dipetakan --}}
        @if ($unmapped->isNotEmpty())
            <div class="bg-white border border-amber-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-amber-50 border-b border-amber-200 text-sm text-amber-900">
                    <b>{{ $unmapped->count() }} baris belum dipetakan</b> di upload aktif ({{ $upload->periode ?? '' }}).
                    <span class="text-xs text-amber-700">RO yang dipecah per komponen/subkomponen tampil per kode, dan aturannya disimpan per kode itu.</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500">
                            <tr>
                                <th class="text-left px-4 py-2 font-semibold">Kegiatan</th>
                                <th class="text-left px-3 py-2 font-semibold">RO</th>
                                <th class="text-left px-3 py-2 font-semibold">Komponen / Sub</th>
                                <th class="text-left px-3 py-2 font-semibold">Uraian</th>
                                <th class="text-right px-3 py-2 font-semibold">Pagu</th>
                                <th class="text-left px-4 py-2 font-semibold">Pilih unit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($unmapped as $r)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $r->keg_kode }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $r->ro_kode }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">
                                        @if ($r->level === 'sub'){{ $r->sub_kode }}
                                        @elseif ($r->level === 'komp'){{ $r->komp_kode }}
                                        @else–@endif
                                    </td>
                                    <td class="px-3 py-2">{{ $r->ro_uraian }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($r->pagu, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2">
                                        <form method="POST" action="{{ route('anggaran.pemetaan.store') }}" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="ro_kode" value="{{ $r->ro_kode }}">
                                            @if ($r->level === 'sub')
                                                <input type="hidden" name="sub_kode" value="{{ $r->sub_kode }}">
                                            @elseif ($r->level === 'komp')
                                                <input type="hidden" name="komp_kode" value="{{ $r->komp_kode }}">
                                            @endif
                                            <input name="unit" list="dl-unit" required placeholder="Unit kerja" class="{{ $inp }} !w-44">
                                            <input name="induk" list="dl-induk" placeholder="Induk (otomatis)" class="{{ $inp }} !w-32">
                                            @unless ($r->level)
                                                <label class="flex items-center gap-1 text-xs text-gray-500">
                                                    <input type="checkbox" name="keg_kode" value="{{ $r->keg_kode }}" class="rounded border-gray-300"> hanya {{ $r->keg_kode }}
                                                </label>
                                            @endunless
                                            <button class="{{ $btn }}">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($upload)
            <div class="rounded-lg border border-gray-100 bg-white px-4 py-3 text-sm text-gray-500 shadow-sm">Semua kode di upload aktif sudah terpetakan.</div>
        @endif

        {{-- Tambah aturan --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Tambah / ubah aturan</h3>
            <p class="text-xs text-gray-500 mb-3">Kosongkan kolom yang tidak perlu. Kalau kuncinya sudah ada, unitnya diperbarui. Contoh: RO <span class="font-mono">EBA.962</span> + SubKomponen <span class="font-mono">962.0P</span>. Kegiatan otomatis dikosongkan bila Komponen/SubKomponen diisi. Induk dikosongkan = otomatis dari akhiran nama unit (D1-D4), atau unit itu sendiri.</p>
            <form method="POST" action="{{ route('anggaran.pemetaan.store') }}" class="grid grid-cols-2 lg:grid-cols-7 gap-3 items-end">
                @csrf
                <div><label class="block text-xs text-gray-500 mb-1">Kegiatan</label><input name="keg_kode" placeholder="CL.7458" class="{{ $inp }}"></div>
                <div><label class="block text-xs text-gray-500 mb-1">RO *</label><input name="ro_kode" required placeholder="ABR.001" class="{{ $inp }}"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Komponen</label><input name="komp_kode" placeholder="001" class="{{ $inp }}"></div>
                <div><label class="block text-xs text-gray-500 mb-1">SubKomponen</label><input name="sub_kode" placeholder="962.0A" class="{{ $inp }}"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Unit kerja *</label><input name="unit" list="dl-unit" required placeholder="Asdep 1 D1 / BIRO UHM" class="{{ $inp }}"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Induk</label><input name="induk" list="dl-induk" placeholder="D1 (otomatis)" class="{{ $inp }}"></div>
                <div><button class="{{ $btn }} w-full justify-center">Simpan</button></div>
            </form>
        </div>

        {{-- Daftar aturan --}}
        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Aturan pemetaan ({{ $rules->total() }})</h3>
                <form method="GET" class="flex items-center gap-2">
                    <input name="q" value="{{ $q }}" placeholder="Cari kode / unit" class="{{ $inp }} !w-56">
                    <button class="{{ $btnLite }}">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500">
                        <tr>
                            <th class="text-left px-4 py-2 font-semibold">Kegiatan</th>
                            <th class="text-left px-3 py-2 font-semibold">RO</th>
                            <th class="text-left px-3 py-2 font-semibold">Komponen</th>
                            <th class="text-left px-3 py-2 font-semibold">SubKomponen</th>
                            <th class="text-left px-3 py-2 font-semibold">Unit kerja &middot; Induk</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rules as $r)
                            <tr>
                                <td class="px-4 py-2 font-mono text-xs">{{ $r->keg_kode ?: '–' }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->ro_kode }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->komp_kode ?: '–' }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->sub_kode ?: '–' }}</td>
                                <td class="px-3 py-2">
                                    <form method="POST" action="{{ route('anggaran.pemetaan.update', $r) }}" class="flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <input name="unit" value="{{ $r->unit }}" list="dl-unit" required class="{{ $inp }} !w-56">
                                        <input name="induk" value="{{ $r->induk }}" list="dl-induk" placeholder="–" class="{{ $inp }} !w-28">
                                        <button class="{{ $btnLite }}">Simpan</button>
                                    </form>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" action="{{ route('anggaran.pemetaan.destroy', $r) }}"
                                        onsubmit="return confirm('Hapus aturan {{ $r->ro_kode }} {{ $r->sub_kode ?: $r->komp_kode }}?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-400">Tidak ada aturan yang cocok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($rules->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">{{ $rules->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>