{{-- Lembar Kontrol & alur persetujuan Surat Tugas. Dipakai di surat-tugas/show.blade.php --}}
@php
    $kontrolList = $st->kontrol()->with('petugas.pegawai')->orderBy('urutan')->get();
    $uAktif = auth()->user();
    $adminKtr = $uAktif->isAdmin();
    $pembuatKtr = (int) $st->created_by === (int) $uAktif->id;
    $bolehKelolaAlur = $adminKtr || $pembuatKtr;

    $adaKontrol = $kontrolList->isNotEmpty();
    $alurSesdep = $st->memakaiAlurSesdep();
    $bolehMulai = !$adaKontrol && $st->status === 'draf' && $bolehKelolaAlur && $alurSesdep;

    $warnaKtr = [
        'menunggu' => 'bg-slate-100 text-slate-700',
        'disetujui' => 'bg-emerald-50 text-emerald-700',
        'ditolak' => 'bg-red-50 text-red-700',
    ];
    $adaDitolak = $kontrolList->contains('status', 'ditolak');
    $semuaDisetujui = $adaKontrol && $kontrolList->every(fn($x) => $x->status === 'disetujui');
    $dikunci = $adaKontrol && !$adaDitolak && $st->status === 'draf';
@endphp

<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2.5">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-semibold text-slate-900">Persetujuan &amp; Lembar Kontrol</h3>
            @if ($semuaDisetujui && $st->status === 'draf')
                <span
                    class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Siap
                    diterbitkan</span>
            @elseif ($adaDitolak)
                <span
                    class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700 ring-1 ring-inset ring-red-200">Perlu
                    revisi</span>
            @elseif ($dikunci)
                <span
                    class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Dalam
                    proses</span>
            @endif
        </div>
        @if ($adaKontrol)
            <a href="{{ route('surat-tugas.kontrol.cetak', $st) }}" target="_blank"
                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">Cetak
                Lembar Kontrol</a>
        @endif
    </div>

    <div class="space-y-3 p-4 text-sm">
        @if (session('error'))
            <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700" role="alert">
                {{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"
                role="status">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800" role="status">
                {{ session('warning') }}</div>
        @endif

        @if (!$adaKontrol)
            @if (!$alurSesdep)
                <p class="text-xs text-slate-500">ST ini tidak terhubung ke unit pembuat, jadi tidak memakai alur persetujuan
                    Sesdep. Penerbitan mengikuti jalur lama.</p>
            @else
                <p class="text-xs text-slate-500">Belum diajukan. Petugas tiap tahap ditentukan otomatis dari unit pembuat.
                    Setelah diajukan, ST dikunci dan tidak bisa diedit atau dihapus sampai ada tahap yang ditolak.</p>
            @endif

            @if ($bolehMulai)
                <form method="POST" action="{{ route('surat-tugas.kontrol.mulai', $st) }}"
                    onsubmit="return confirm('Ajukan ST ini untuk persetujuan? Setelah diajukan, ST dikunci dari edit dan hapus.')">
                    @csrf
                    <button
                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-[13px] font-semibold text-white hover:bg-blue-700">Ajukan
                        untuk persetujuan</button>
                </form>
            @endif
        @else
            @if ($dikunci)
                <p class="text-xs text-slate-500">ST terkunci selama proses persetujuan. Kunci terbuka kalau ada tahap yang
                    ditolak.</p>
            @endif

            <ol class="space-y-2">
                @foreach ($kontrolList as $k)
                    @php
                        $giliran = $k->status === 'menunggu'
                            && !$kontrolList->where('urutan', '<', $k->urutan)->contains(fn($x) => $x->status !== 'disetujui');
                        $bolehAksi = $st->status === 'draf' && $giliran && ($adminKtr || (int) $k->petugas_id === (int) $uAktif->id);
                    @endphp
                    <li class="rounded-md border p-3 {{ $giliran ? 'border-blue-300 bg-blue-50/30' : 'border-slate-200' }}">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="text-xs text-slate-400">{{ $k->urutan }}.</span>
                                <span class="font-semibold text-slate-800">{{ $k->tahap_label }}</span>
                                <span class="text-xs text-slate-500">&mdash;
                                    {{ $k->petugas->name ?? '-' }}@if ($k->petugas?->pegawai?->jabatan)
                                    ({{ $k->petugas->pegawai->jabatan }})@endif</span>
                                @if ($giliran)
                                    <span class="ml-1 text-[11px] font-semibold text-blue-700">Giliran sekarang</span>
                                @endif
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $warnaKtr[$k->status] ?? '' }}">
                                {{ $k->status_label }}@if ($k->acted_at) &middot; {{ $k->acted_at->format('d/m/Y H:i') }}@endif
                            </span>
                        </div>

                        @if ($k->catatan)
                            <p class="mt-1.5 rounded bg-slate-50 px-2 py-1 text-xs text-slate-600">
                                <span class="font-medium">{{ $k->status === 'ditolak' ? 'Alasan ditolak:' : 'Catatan:' }}</span>
                                {{ $k->catatan }}
                            </p>
                        @endif

                        @if ($bolehAksi)
                            <form method="POST" action="{{ route('surat-tugas.kontrol.aksi', $k->id) }}" x-data="{ catatan: '' }"
                                class="mt-2 flex flex-wrap items-center gap-2">
                                @csrf @method('PATCH')
                                <input type="text" name="catatan" x-model="catatan" placeholder="Catatan (wajib jika menolak)"
                                    class="min-w-[14rem] flex-1 rounded-md border-slate-300 py-1 text-xs">
                                <button name="aksi" value="setujui"
                                    class="rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Setujui</button>
                                <button name="aksi" value="tolak" :disabled="!catatan.trim()"
                                    class="rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40">Tolak</button>
                            </form>
                            @error('catatan')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </li>
                @endforeach
            </ol>

            @if ($adaDitolak && $st->status === 'draf' && $bolehKelolaAlur)
                <div class="rounded-md border border-amber-200 bg-amber-50 p-3">
                    <p class="text-xs text-amber-800">Ada tahap yang ditolak. Perbaiki ST sesuai catatan, lalu ajukan ulang.
                        Proses kembali ke tahap Eselon 2.</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <a href="{{ route('surat-tugas.edit', $st) }}"
                            class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit
                            ST</a>
                        <form method="POST" action="{{ route('surat-tugas.kontrol.aksi', $kontrolList->first()->id) }}"
                            onsubmit="return confirm('Ajukan ulang ST ini? Proses kembali ke tahap Eselon 2.')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="aksi" value="ulang">
                            <button
                                class="rounded-md border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">Ajukan
                                ulang</button>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>
</section>