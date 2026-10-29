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

    $adaDitolak = $kontrolList->contains('status', 'ditolak');
    $semuaDisetujui = $adaKontrol && $kontrolList->every(fn($x) => $x->status === 'disetujui');
    $dikunci = $adaKontrol && !$adaDitolak && $st->status === 'draf';
    $totalTahap = $kontrolList->count();
    $tahapSelesai = $kontrolList->where('status', 'disetujui')->count();

    $pill = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-inset';

    // Warna teks & titik status per tahap
    $teksStatus = [
        'menunggu' => 'text-slate-400',
        'disetujui' => 'text-emerald-700',
        'ditolak' => 'text-red-600',
    ];
    $titikStatus = [
        'menunggu' => 'bg-slate-300',
        'disetujui' => 'bg-emerald-500',
        'ditolak' => 'bg-red-500',
    ];
@endphp

<section x-data="{
        modal: { buka: false, judul: '', pesan: '', tombol: 'Ya, lanjutkan', form: '' },
        kirim: false,
        tanya(form, judul, pesan, tombol) {
            this.modal = { buka: true, judul: judul, pesan: pesan, tombol: tombol, form: form };
        },
        lanjut() {
            if (this.kirim) return;
            this.kirim = true;
            document.getElementById(this.modal.form).submit();
        }
    }" @keydown.escape.window="modal.buka = false"
    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">

    {{-- ===== Header ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 pt-4 sm:px-6 sm:pt-5">
        <div class="flex flex-wrap items-center gap-2.5">
            <h3 class="text-[15px] font-semibold tracking-tight text-slate-900">Persetujuan &amp; Lembar Kontrol</h3>

            @if ($semuaDisetujui && $st->status === 'draf')
                <span class="{{ $pill }} bg-emerald-50 text-emerald-700 ring-emerald-200">Siap diterbitkan</span>
            @elseif ($adaDitolak)
                <span class="{{ $pill }} bg-red-50 text-red-700 ring-red-200">Perlu revisi</span>
            @elseif ($dikunci)
                <span class="{{ $pill }} bg-amber-50 text-amber-700 ring-amber-200">Dalam proses</span>
            @endif
        </div>

        @if ($adaKontrol)
            <a href="{{ route('surat-tugas.kontrol.cetak', $st) }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                <span>Cetak lembar kontrol</span>
            </a>
        @endif
    </div>

    <div class="space-y-5 px-4 pb-5 pt-4 sm:px-6 sm:pb-6">

        @if (!$adaKontrol)
            {{-- ===== Belum diajukan ===== --}}
            <div>
                @if (!$alurSesdep)
                    <p class="text-sm font-medium text-slate-800">Tidak memakai alur persetujuan</p>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">ST ini tidak terhubung ke unit
                        pembuat, jadi penerbitan mengikuti jalur lama.</p>
                @else
                    <p class="text-sm font-medium text-slate-800">Belum diajukan</p>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">Petugas tiap tahap ditentukan
                        otomatis dari unit pembuat. Setelah diajukan, ST dikunci dan tidak bisa diedit atau dihapus
                        sampai ada tahap yang ditolak.</p>
                @endif
            </div>

            @if ($bolehMulai)
                <form id="form-ajukan" method="POST" action="{{ route('surat-tugas.kontrol.mulai', $st) }}">
                    @csrf
                    <button type="button"
                        @click="tanya('form-ajukan', 'Ajukan untuk persetujuan?', 'Setelah diajukan, ST dikunci dari edit dan hapus sampai ada tahap yang ditolak.', 'Ya, ajukan')"
                        class="inline-flex items-center rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                        Ajukan untuk persetujuan
                    </button>
                </form>
            @endif
        @else
            {{-- ===== Progres bersegmen ===== --}}
            <div class="space-y-2">
                <div class="flex gap-1" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $totalTahap }}"
                    aria-valuenow="{{ $tahapSelesai }}">
                    @foreach ($kontrolList as $seg)
                        <span
                            class="h-1 flex-1 rounded-full {{ $seg->status === 'disetujui' ? 'bg-emerald-500' : ($seg->status === 'ditolak' ? 'bg-red-400' : 'bg-slate-200') }}"></span>
                    @endforeach
                </div>
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-xs">
                    <span class="font-medium text-slate-600">{{ $tahapSelesai }} dari {{ $totalTahap }} tahap
                        disetujui</span>
                    @if ($dikunci)
                        <span class="inline-flex items-center gap-1.5 text-slate-400">
                            <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            ST terkunci selama proses persetujuan
                        </span>
                    @endif
                </div>
            </div>

            {{-- ===== Alur tahap ===== --}}
            <ol>
                @foreach ($kontrolList as $k)
                    @php
                        $giliran = $k->status === 'menunggu'
                            && !$kontrolList->where('urutan', '<', $k->urutan)->contains(fn($x) => $x->status !== 'disetujui');
                        $bolehAksi = $st->status === 'draf' && $giliran && ($adminKtr || (int) $k->petugas_id === (int) $uAktif->id);
                        $terakhir = $loop->last;
                    @endphp

                    <li class="relative flex gap-3.5">
                        {{-- Garis penghubung --}}
                        @unless ($terakhir)
                            <span
                                class="absolute left-3 top-[2.25rem] bottom-0 w-px -translate-x-1/2 {{ $k->status === 'disetujui' ? 'bg-emerald-300' : 'bg-slate-200' }}"
                                aria-hidden="true"></span>
                        @endunless

                        {{-- Penanda --}}
                        <span class="relative z-10 mt-3 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px] font-bold
                            @if ($k->status === 'disetujui') bg-emerald-500 text-white
                            @elseif ($k->status === 'ditolak') bg-red-500 text-white
                            @elseif ($giliran) bg-blue-600 text-white ring-4 ring-blue-100
                            @else bg-white text-slate-400 ring-1 ring-inset ring-slate-300 @endif">
                            @if ($k->status === 'disetujui')
                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            @elseif ($k->status === 'ditolak')
                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @else
                                {{ $k->urutan }}
                            @endif
                        </span>

                        {{-- Isi tahap --}}
                        <div
                            class="mb-1 min-w-0 flex-1 rounded-xl px-3.5 py-2.5 {{ $giliran ? 'bg-blue-50/60 ring-1 ring-inset ring-blue-100' : '' }}">
                            <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="text-sm font-semibold {{ $k->status === 'menunggu' && !$giliran ? 'text-slate-500' : 'text-slate-900' }}">{{ $k->tahap_label }}</span>
                                        @if ($giliran)
                                            <span
                                                class="rounded-full bg-blue-600 px-2 py-px text-[10px] font-semibold uppercase tracking-wide text-white">Giliran
                                                sekarang</span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $k->petugas->name ?? '-' }}@if ($k->petugas?->pegawai?->jabatan)
                                            <span class="text-slate-300">&middot;</span> {{ $k->petugas->pegawai->jabatan }}
                                        @endif
                                    </p>
                                </div>

                                <div class="text-left sm:text-right">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $teksStatus[$k->status] ?? $teksStatus['menunggu'] }}">
                                        <span
                                            class="h-1.5 w-1.5 rounded-full {{ $titikStatus[$k->status] ?? $titikStatus['menunggu'] }}"></span>
                                        {{ $k->status_label }}
                                    </span>
                                    @if ($k->acted_at)
                                        <p class="mt-0.5 text-[11px] tabular-nums text-slate-400">
                                            {{ $k->acted_at->format('d/m/Y H:i') }}</p>
                                    @endif
                                </div>
                            </div>

                            @if ($k->catatan)
                                <div
                                    class="mt-2.5 border-l-2 pl-3 {{ $k->status === 'ditolak' ? 'border-red-300' : 'border-slate-200' }}">
                                    <p
                                        class="text-[10px] font-semibold uppercase tracking-wider {{ $k->status === 'ditolak' ? 'text-red-500' : 'text-slate-400' }}">
                                        {{ $k->status === 'ditolak' ? 'Alasan ditolak' : 'Catatan' }}
                                    </p>
                                    <p class="mt-0.5 whitespace-pre-line text-[13px] leading-relaxed text-slate-600">
                                        {{ $k->catatan }}</p>
                                </div>
                            @endif

                            @if ($bolehAksi)
                                <form method="POST" action="{{ route('surat-tugas.kontrol.aksi', $k->id) }}"
                                    x-data="{ catatan: '' }" class="mt-3 space-y-2.5">
                                    @csrf @method('PATCH')
                                    <input type="text" name="catatan" x-model="catatan"
                                        placeholder="Catatan (wajib jika menolak)"
                                        class="w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                                    @error('catatan')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <div class="flex items-center justify-end gap-2">
                                        <button name="aksi" value="tolak" :disabled="!catatan.trim()"
                                            class="rounded-lg px-3.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent">
                                            Tolak
                                        </button>
                                        <button name="aksi" value="setujui"
                                            class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                            Setujui
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>

            {{-- ===== Revisi setelah ditolak ===== --}}
            @if ($adaDitolak && $st->status === 'draf' && $bolehKelolaAlur)
                <div class="rounded-xl border border-amber-200/80 bg-amber-50/60 px-4 py-3.5">
                    <p class="text-sm font-semibold text-amber-900">Ada tahap yang ditolak</p>
                    <p class="mt-0.5 text-[13px] leading-relaxed text-amber-800/90">Perbaiki ST sesuai catatan, lalu
                        ajukan ulang. Proses kembali ke tahap Eselon 2.</p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <a href="{{ route('surat-tugas.edit', $st) }}"
                            class="rounded-lg border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                            Edit ST
                        </a>

                        <form id="form-ulang" method="POST"
                            action="{{ route('surat-tugas.kontrol.aksi', $kontrolList->first()->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="aksi" value="ulang">
                            <button type="button"
                                @click="tanya('form-ulang', 'Ajukan ulang ST ini?', 'Proses persetujuan akan kembali ke tahap Eselon 2.', 'Ya, ajukan ulang')"
                                class="rounded-lg bg-amber-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-700">
                                Ajukan ulang
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- ===== Modal konfirmasi ===== --}}
    <template x-teleport="body">
        <div x-show="modal.buka" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog" aria-modal="true" aria-labelledby="judul-konfirmasi">
            <div x-show="modal.buka" x-transition.opacity class="absolute inset-0 bg-slate-900/50"
                @click="modal.buka = false"></div>

            <div x-show="modal.buka" x-transition
                class="relative w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl ring-1 ring-slate-200">
                <h4 id="judul-konfirmasi" class="text-base font-semibold text-slate-900" x-text="modal.judul"></h4>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500" x-text="modal.pesan"></p>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="modal.buka = false" :disabled="kirim"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-50">
                        Batal
                    </button>
                    <button type="button" @click="lanjut()" :disabled="kirim"
                        class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                        x-text="modal.tombol"></button>
                </div>
            </div>
        </div>
    </template>
</section>