@php
    $u = auth()->user();
    $st = $suratKeluar->status_persetujuan;
    $admin = $u->isAdmin();
    $payload = $suratKeluar->isi_payload ?? [];
    $siapTerbit = $st === 'disetujui_es1' || ($st === 'disetujui_es2' && !$suratKeluar->butuh_eselon1);
    $bolehUbah = ($suratKeluar->bisa_diubah || $admin) && ($admin || $u->isTu() || $suratKeluar->created_by === $u->id);
    $tahap = ['draft' => 'Draf', 'diajukan' => 'Diperiksa Es 2', 'disetujui_es2' => 'Disetujui Es 2'];
    if ($suratKeluar->butuh_eselon1) { $tahap['disetujui_es1'] = 'Disetujui Es 1'; }
    $tahap['terbit'] = 'Terbit';
    $urutan = array_keys($tahap);
    $posisi = array_search($st, $urutan, true);
    $btn = 'px-4 py-2 text-xs font-semibold rounded-xl shadow-sm transition';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">{{ $suratKeluar->nama_jenis }}: {{ $suratKeluar->hal }}</h2>
            <a href="{{ route('surat-keluar.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">Kembali</a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        {{-- Lembar Kontrol: tahapan persetujuan --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-3">Lembar Kontrol</h3>
            <ol class="flex flex-wrap gap-2 text-xs font-semibold">
                @foreach ($tahap as $key => $label)
                    @php $i = array_search($key, $urutan, true); @endphp
                    <li class="px-3 py-1.5 rounded-full border {{ $i < $posisi ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($i === $posisi ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-400 border-slate-200') }}">
                        {{ $i + 1 }}. {{ $label }}
                    </li>
                @endforeach
            </ol>
            @if (!empty($payload['catatan_revisi']) && $st === 'draft')
                <p class="mt-3 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl p-3"><strong>Catatan revisi:</strong> {{ $payload['catatan_revisi'] }}</p>
            @endif

            <div class="mt-4 flex flex-wrap items-start gap-2">
                @if ($st === 'draft' && ($admin || $u->isTu() || $suratKeluar->created_by === $u->id))
                    <form method="POST" action="{{ route('surat-keluar.status', $suratKeluar->id) }}">@csrf @method('PATCH')
                        <input type="hidden" name="aksi" value="ajukan">
                        <button class="{{ $btn }} bg-blue-600 hover:bg-blue-700 text-white">Ajukan untuk Diperiksa</button>
                    </form>
                @endif

                @if ($st === 'diajukan' && ($admin || $u->isEselon2()))
                    <form method="POST" action="{{ route('surat-keluar.status', $suratKeluar->id) }}">@csrf @method('PATCH')
                        <input type="hidden" name="aksi" value="setujui_es2">
                        <button class="{{ $btn }} bg-emerald-600 hover:bg-emerald-700 text-white">Setujui (Eselon 2)</button>
                    </form>
                @endif

                @if ($st === 'disetujui_es2' && $suratKeluar->butuh_eselon1 && ($admin || $u->isEselon1()))
                    <form method="POST" action="{{ route('surat-keluar.status', $suratKeluar->id) }}">@csrf @method('PATCH')
                        <input type="hidden" name="aksi" value="setujui_es1">
                        <button class="{{ $btn }} bg-emerald-600 hover:bg-emerald-700 text-white">Setujui (Eselon 1)</button>
                    </form>
                @endif

                @if (in_array($st, ['diajukan', 'disetujui_es2', 'disetujui_es1'], true) && ($admin || $u->isEselon2() || $u->isEselon1()))
                    <form method="POST" action="{{ route('surat-keluar.status', $suratKeluar->id) }}" class="flex gap-2">@csrf @method('PATCH')
                        <input type="hidden" name="aksi" value="kembalikan">
                        <input type="text" name="catatan" placeholder="Catatan revisi..." class="rounded-xl border-slate-200 text-xs">
                        <button class="{{ $btn }} bg-amber-500 hover:bg-amber-600 text-white">Kembalikan</button>
                    </form>
                @endif

                @if ($siapTerbit && ($admin || $u->isTu()))
                    <form method="POST" action="{{ route('surat-keluar.status', $suratKeluar->id) }}" enctype="multipart/form-data" class="flex flex-wrap gap-2 items-center">@csrf @method('PATCH')
                        <input type="hidden" name="aksi" value="terbit">
                        <input type="file" name="file_scan_ttd" accept=".pdf,.jpg,.jpeg,.png" class="text-xs">
                        <button class="{{ $btn }} bg-slate-800 hover:bg-slate-900 text-white">Terbitkan (TTD Basah)</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Ringkasan dokumen --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-sm text-slate-700">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-xs">
                <div><dt class="font-semibold text-slate-500">Jenis</dt><dd>{{ $suratKeluar->nama_jenis }}@if (!empty($payload['template'])) &mdash; {{ \App\Models\SuratKeluar::TEMPLATE[$suratKeluar->jenis_dokumen][$payload['template']] ?? $payload['template'] }}@endif</dd></div>
                <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ $suratKeluar->status_label }}</dd></div>
                <div><dt class="font-semibold text-slate-500">Nomor Surat</dt><dd>{{ $suratKeluar->nomor_surat ?: '— (draf)' }}</dd></div>
                <div><dt class="font-semibold text-slate-500">Kode Klasifikasi</dt><dd>{{ $payload['kode_klasifikasi'] ?? '-' }}</dd></div>
                <div><dt class="font-semibold text-slate-500">Tanggal</dt><dd>{{ optional($suratKeluar->tanggal_surat)->format('d M Y') }}</dd></div>
                <div><dt class="font-semibold text-slate-500">Sifat</dt><dd>{{ ucfirst($suratKeluar->sifat) }}</dd></div>
                <div><dt class="font-semibold text-slate-500">Yth. / Tujuan</dt><dd>{{ $suratKeluar->tujuan ?: '-' }}</dd></div>
                <div><dt class="font-semibold text-slate-500">Dari</dt><dd>{{ $suratKeluar->dari ?: '-' }}</dd></div>
                <div><dt class="font-semibold text-slate-500">Lampiran</dt><dd>{{ $suratKeluar->dengan_lampiran ? 'Dengan lampiran' : 'Tanpa lampiran' }}</dd></div>
                <div><dt class="font-semibold text-slate-500">Pembuat</dt><dd>{{ $suratKeluar->pembuat->name ?? '-' }}</dd></div>
            </dl>

            @if ($suratKeluar->file_scan_ttd)
                <p class="mt-4 text-xs"><a class="text-blue-600 underline" target="_blank" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($suratKeluar->file_scan_ttd) }}">Lihat hasil scan TTD basah</a></p>
            @endif
        </div>

        <div class="flex flex-wrap justify-end gap-2">
            @if ($bolehUbah)
                <a href="{{ route('surat-keluar.edit', $suratKeluar->id) }}" class="{{ $btn }} bg-slate-100 hover:bg-slate-200 text-slate-700">Edit</a>
                <form method="POST" action="{{ route('surat-keluar.destroy', $suratKeluar->id) }}" onsubmit="return confirm('Hapus dokumen ini?')">@csrf @method('DELETE')
                    <button class="{{ $btn }} bg-red-50 hover:bg-red-100 text-red-700 border border-red-200">Hapus</button>
                </form>
            @endif
            <a href="{{ route('surat-keluar.pdf', $suratKeluar->id) }}" target="_blank" class="{{ $btn }} bg-blue-600 hover:bg-blue-700 text-white">Pratinjau / Cetak PDF</a>
        </div>
    </div>
</x-app-layout>
