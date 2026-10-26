{{-- Lembar Kontrol Surat Tugas (opsional). Dipakai di surat-tugas/show.blade.php --}}
@php
    $kontrolList = $st->kontrol()->with('petugas.pegawai')->get();
    $uAktif = auth()->user();
    $adminKtr = $uAktif->isAdmin();
    $pembuatKtr = (int) $st->created_by === (int) $uAktif->id;
    $bolehMulai = $kontrolList->isEmpty() && $st->status === 'draf' && ($adminKtr || $pembuatKtr);
    $opsiUser = $bolehMulai ? \App\Models\User::with('pegawai:id,jabatan')->orderBy('name')->get(['id', 'name', 'pegawai_id']) : collect();
    $warnaKtr = ['menunggu' => 'bg-slate-100 text-slate-700', 'disetujui' => 'bg-emerald-50 text-emerald-700', 'ditolak' => 'bg-red-50 text-red-700'];
    $adaDitolak = $kontrolList->contains('status', 'ditolak');
@endphp

<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2.5">
        <h3 class="text-sm font-semibold text-slate-900">Lembar Kontrol</h3>
        @if ($kontrolList->isNotEmpty())
            <a href="{{ route('surat-tugas.kontrol.cetak', $st) }}" target="_blank"
                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">Cetak Lembar Kontrol</a>
        @endif
    </div>

    <div class="p-4 space-y-3 text-sm">
        @if ($kontrolList->isEmpty())
            <p class="text-xs text-slate-500">Belum ada lembar kontrol. Lembar kontrol bersifat opsional; jika dibuat, ST baru dapat diterbitkan setelah semua tahap disetujui.</p>

            @if ($bolehMulai)
                <form method="POST" action="{{ route('surat-tugas.kontrol.mulai', $st) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @csrf
                    @foreach ([['pemeriksa_id', 'Pemeriksa'], ['es2_id', 'Persetujuan Eselon 2'], ['admin_sesdep_id', 'Admin Sesdep (kontrol & penomoran)'], ['sesdep_id', 'Persetujuan Sesdep']] as [$nm, $lb])
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ $lb }}</label>
                            <select name="{{ $nm }}" required class="w-full rounded-md border-slate-300 py-1.5 text-[13px]">
                                <option value="">Pilih petugas...</option>
                                @foreach ($opsiUser as $o)
                                    <option value="{{ $o->id }}" @selected((string) old($nm) === (string) $o->id)>{{ $o->name }}@if ($o->pegawai?->jabatan) ({{ $o->pegawai->jabatan }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <div class="sm:col-span-2">
                        <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-[13px] font-semibold text-white hover:bg-blue-700">Buat Lembar Kontrol</button>
                    </div>
                </form>
            @endif
        @else
            <ol class="space-y-2">
                @foreach ($kontrolList as $k)
                    @php
                        $giliran = $k->status === 'menunggu'
                            && !$kontrolList->where('urutan', '<', $k->urutan)->contains(fn ($x) => $x->status !== 'disetujui');
                        $bolehAksi = $st->status === 'draf' && $giliran && ($adminKtr || (int) $k->petugas_id === (int) $uAktif->id);
                    @endphp
                    <li class="rounded-md border border-slate-200 p-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="text-xs text-slate-400">{{ $k->urutan }}.</span>
                                <span class="font-semibold text-slate-800">{{ $k->tahap_label }}</span>
                                <span class="text-xs text-slate-500">&mdash; {{ $k->petugas->name ?? '-' }}@if ($k->petugas?->pegawai?->jabatan) ({{ $k->petugas->pegawai->jabatan }})@endif</span>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $warnaKtr[$k->status] ?? '' }}">
                                {{ $k->status_label }}@if ($k->acted_at) &middot; {{ $k->acted_at->format('d/m/Y H:i') }}@endif
                            </span>
                        </div>
                        @if ($k->catatan)
                            <p class="mt-1 text-xs text-slate-600">Catatan: {{ $k->catatan }}</p>
                        @endif

                        @if ($bolehAksi)
                            <form method="POST" action="{{ route('surat-tugas.kontrol.aksi', $k->id) }}" class="mt-2 flex flex-wrap items-center gap-2">
                                @csrf @method('PATCH')
                                <input type="text" name="catatan" placeholder="Catatan (opsional)" class="rounded-md border-slate-300 py-1 text-xs">
                                <button name="aksi" value="setujui" class="rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Setujui</button>
                                <button name="aksi" value="tolak" class="rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-700">Tolak</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ol>

            @if ($adaDitolak && $st->status === 'draf' && ($adminKtr || $pembuatKtr))
                <form method="POST" action="{{ route('surat-tugas.kontrol.aksi', $kontrolList->first()->id) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="aksi" value="ulang">
                    <button class="rounded-md border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">Ulangi dari tahap pemeriksaan</button>
                </form>
            @endif
        @endif
    </div>
</section>
