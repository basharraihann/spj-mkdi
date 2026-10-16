<x-app-layout title="Pemetaan Unit">

    <style>
        /* Kartu: gaya sama dengan halaman Monitor Anggaran */
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 0 0 1px rgb(226 232 240 / .8), 0 1px 2px rgb(15 23 42 / .04);
        }
    </style>

    @php
        $inp = 'w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white';
        $btn = 'inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition shadow-sm';
        $btnLite = 'inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition';
        $card = 'card';
        $iconCircle = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100';
        $docIcon = '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>';
    @endphp

    <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ===== Judul (pita putih di bawah navbar) ===== --}}
        <div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-8 border-b border-slate-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-800">Pemetaan Unit Anggaran</h2>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">Aturan paling spesifik menang: SubKomponen, lalu
                        Komponen, lalu Kegiatan + RO, lalu RO saja.
                        Perubahan langsung berlaku ke semua data, tanpa upload ulang.</p>
                </div>
                <a href="{{ route('anggaran.index') }}"
                    class="{{ $btnLite }} !px-4 !py-2.5 !text-sm shrink-0 gap-2 shadow-sm">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Monitor Anggaran
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $e)
                <div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <datalist id="dl-unit">
            @foreach ($units as $u)
            <option value="{{ $u }}">@endforeach
        </datalist>

        <datalist id="dl-induk">
            @foreach ($induks as $u)
            <option value="{{ $u }}">@endforeach
        </datalist>

        {{-- ===== Belum dipetakan ===== --}}
        @if ($unmapped->isNotEmpty())
            <div class="card overflow-hidden" style="box-shadow:0 0 0 1px rgb(253 230 138), 0 1px 2px rgb(15 23 42 / .04)">
                <div class="px-5 py-3 bg-amber-50 border-b border-amber-200 text-sm text-amber-900">
                    <b>{{ $unmapped->count() }} baris belum dipetakan</b> di upload aktif ({{ $upload->periode ?? '' }}).
                    <span class="text-xs text-amber-700">RO yang dipecah per komponen/subkomponen tampil per kode, dan
                        aturannya disimpan per kode itu.</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-xs text-white" style="background:#2563eb">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-semibold">Kegiatan</th>
                                <th class="text-left px-3 py-2.5 font-semibold">RO</th>
                                <th class="text-left px-3 py-2.5 font-semibold">Komponen / Sub</th>
                                <th class="text-left px-3 py-2.5 font-semibold">Uraian</th>
                                <th class="text-right px-3 py-2.5 font-semibold">Pagu</th>
                                <th class="text-left px-4 py-2.5 font-semibold">Pilih unit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
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
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($r->pagu, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <form method="POST" action="{{ route('anggaran.pemetaan.store') }}"
                                            class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="ro_kode" value="{{ $r->ro_kode }}">
                                            @if ($r->level === 'sub')
                                                <input type="hidden" name="sub_kode" value="{{ $r->sub_kode }}">
                                            @elseif ($r->level === 'komp')
                                                <input type="hidden" name="komp_kode" value="{{ $r->komp_kode }}">
                                            @endif
                                            <input name="unit" list="dl-unit" required placeholder="Unit kerja"
                                                class="{{ $inp }} !w-44">
                                            <input name="induk" list="dl-induk" placeholder="Induk (otomatis)"
                                                class="{{ $inp }} !w-32">
                                            @unless ($r->level)
                                                <label class="flex items-center gap-1 text-xs text-slate-500">
                                                    <input type="checkbox" name="keg_kode" value="{{ $r->keg_kode }}"
                                                        class="rounded border-slate-300"> hanya {{ $r->keg_kode }}
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
        @endif

        {{-- ===== Tambah aturan ===== --}}
        <div class="{{ $card }} p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <span class="{{ $iconCircle }}">{!! $docIcon !!}</span>
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Pemetaan RO ke Unit</h3>
                    <p class="text-sm text-slate-500">Tentukan unit kerja untuk setiap kode anggaran.</p>
                </div>
            </div>

            @if ($unmapped->isEmpty() && $upload)
                <div class="mt-4 flex items-center gap-2.5 rounded-xl bg-blue-50 px-4 py-2.5 text-sm text-blue-700">
                    <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    Semua kode di upload aktif sudah terpetakan.
                </div>
            @endif

            <div class="mt-5 border-t border-slate-100 pt-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-1">Tambah / ubah aturan</h3>
                <p class="text-xs text-slate-500 mb-4">Kosongkan kolom yang tidak perlu. Kalau kuncinya sudah ada,
                    unitnya diperbarui. Contoh: RO <span class="font-mono">EBA.962</span> + SubKomponen <span
                        class="font-mono">962.0P</span>. Kegiatan otomatis dikosongkan bila Komponen/SubKomponen diisi.
                    Induk dikosongkan = otomatis dari akhiran nama unit (D1-D4), atau unit itu sendiri.</p>
                <form method="POST" action="{{ route('anggaran.pemetaan.store') }}"
                    class="grid grid-cols-2 lg:grid-cols-7 gap-3 items-end">
                    @csrf
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">Kegiatan</label><input
                            name="keg_kode" placeholder="CL.7458" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">RO <span
                                class="text-red-500">*</span></label><input name="ro_kode" required
                            placeholder="ABR.001" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">Komponen</label><input
                            name="komp_kode" placeholder="001" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">SubKomponen</label><input
                            name="sub_kode" placeholder="962.0A" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">Unit kerja <span
                                class="text-red-500">*</span></label><input name="unit" list="dl-unit" required
                            placeholder="Asdep 1 D1 / BIRO UHM" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">Induk</label><input name="induk"
                            list="dl-induk" placeholder="D1 (otomatis)" class="{{ $inp }}"></div>
                    <div><button class="{{ $btn }} w-full">Simpan</button></div>
                </form>
            </div>
        </div>

        {{-- ===== Daftar aturan ===== --}}
        <div class="{{ $card }} overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 sm:px-6 py-4">
                <div class="flex items-start gap-3">
                    <span class="{{ $iconCircle }}">{!! $docIcon !!}</span>
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">Aturan pemetaan ({{ $rules->total() }})</h3>
                        <p class="text-sm text-slate-500">Daftar seluruh aturan pemetaan unit kerja dan kode anggaran.
                        </p>
                    </div>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <div class="relative">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input name="q" value="{{ $q }}" placeholder="Cari kode / unit" class="{{ $inp }} !w-56 !pl-9">
                    </div>
                    <button class="{{ $btnLite }} !px-4 !py-2.5 !text-sm">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-white" style="background:#2563eb">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-semibold">Kegiatan</th>
                            <th class="text-left px-3 py-2.5 font-semibold">RO</th>
                            <th class="text-left px-3 py-2.5 font-semibold">Komponen</th>
                            <th class="text-left px-3 py-2.5 font-semibold">SubKomponen</th>
                            <th class="text-left px-3 py-2.5 font-semibold">Unit kerja &middot; Induk</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rules as $r)
                            <tr>
                                <td class="px-4 py-2 font-mono text-xs">{{ $r->keg_kode ?: '–' }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->ro_kode }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->komp_kode ?: '–' }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $r->sub_kode ?: '–' }}</td>
                                <td class="px-3 py-2">
                                    <form method="POST" action="{{ route('anggaran.pemetaan.update', $r) }}"
                                        class="flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <input name="unit" value="{{ $r->unit }}" list="dl-unit" required
                                            class="{{ $inp }} !w-56">
                                        <input name="induk" value="{{ $r->induk }}" list="dl-induk" placeholder="–"
                                            class="{{ $inp }} !w-28">
                                        <button class="{{ $btnLite }}">Simpan</button>
                                    </form>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" action="{{ route('anggaran.pemetaan.destroy', $r) }}"
                                        onsubmit="return confirm('Hapus aturan {{ $r->ro_kode }} {{ $r->sub_kode ?: $r->komp_kode }}?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-medium text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-sm text-slate-400">Tidak ada aturan yang
                                    cocok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($rules->hasPages())
                <div class="px-5 py-3 border-t border-slate-100">{{ $rules->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>