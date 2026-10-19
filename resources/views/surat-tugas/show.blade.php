@php
    // Warna status dibuat langsung (inline) supaya selalu tampil, tidak bergantung pada build CSS
    $badge = [
        'draf' => 'background:#f3f4f6;color:#374151;',
        'terbit' => 'background:#dbeafe;color:#1d4ed8;',
        'selesai' => 'background:#dcfce7;color:#15803d;',
        'batal' => 'background:#fee2e2;color:#b91c1c;',
    ][$st->status] ?? 'background:#f3f4f6;color:#374151;';
    $label = 'text-xs font-semibold uppercase tracking-wide text-gray-500';
    $adaTanggal = $st->peserta->contains(fn($p) => (bool) $p->tanggal_mulai);

    // Pengelompokan (tusi) — variabel dikirim dari SuratTugasController@show
    $tusiOptions = $tusiOptions ?? collect();
    $bolehUbahTusi = $bolehUbahTusi ?? false;
    $bolehKelola = $bolehKelola ?? false;
    // tusi yang sedang dipakai tapi sudah dinonaktifkan tetap ditampilkan di dropdown
    $tusiNonaktifDipakai = $st->tusi && !$tusiOptions->contains('id', $st->tusi_id);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight break-all">Surat Tugas {{ $st->nomor_tampil }}
            </h2>
            <span class="px-3 py-1 rounded-full text-xs font-medium" style="{{ $badge }}">{{ $st->status_label }}</span>
        </div>
    </x-slot>

    {{-- Aksi --}}
    <div class="flex flex-wrap items-center gap-2">
        @if ($st->status === 'draf' && $bolehKelola)
            <form method="POST" action="{{ route('surat-tugas.terbitkan', $st) }}"
                onsubmit="return confirm('Terbitkan ST ini? Setelah terbit, isi ST dikunci dan tidak bisa diedit, dan peserta yang punya akun akan mendapat tugas laporan.')">
                @csrf
                <button class="px-4 py-2 rounded-lg text-sm" style="background:#2563eb;color:#fff;">Terbitkan</button>
            </form>
        @endif

        @if ($st->status === 'terbit' && \Illuminate\Support\Facades\Route::has('surat-tugas.selesai'))
            <form method="POST" action="{{ route('surat-tugas.selesai', $st) }}">
                @csrf
                <button class="px-4 py-2 rounded-lg text-sm" style="background:#16a34a;color:#fff;">Tandai Selesai</button>
            </form>
        @endif

        <a href="{{ route('surat-tugas.index') }}"
            class="px-4 py-2 rounded-lg border bg-white text-sm sm:ml-auto">Kembali</a>
    </div>

    {{-- Detail --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 sm:p-6 space-y-6 text-sm">

        <div class="grid md:grid-cols-3 gap-4 rounded-lg bg-gray-50 p-4">
            <div>
                <div class="{{ $label }}">Nomor</div>
                <div class="mt-1 font-medium break-all">{{ $st->nomor_tampil }}</div>
            </div>
            <div>
                <div class="{{ $label }}">Tempat, Tanggal</div>
                <div class="mt-1 font-medium">{{ $st->tempat }}, {{ $st->tanggal_indo }}</div>
            </div>
            <div>
                <div class="{{ $label }}">Penandatangan</div>
                <div class="mt-1 font-medium">{{ $st->nama_penandatangan }}</div>
                <div class="text-gray-500">{{ $st->jabatan_penandatangan }}</div>
            </div>
        </div>

        <div class="grid lg:grid-cols-5 gap-6">
            <div class="lg:col-span-3 space-y-5 leading-relaxed text-gray-800">
                <div>
                    <div class="{{ $label }}">Menimbang</div>
                    <p class="mt-1">{{ $st->menimbang ?: '-' }}</p>
                </div>

                <div>
                    <div class="{{ $label }}">Dasar</div>
                    <ol class="mt-1 list-decimal pl-5 space-y-1">@forelse ($st->dasar ?? [] as $d)
                    <li>{{ $d }}</li>@empty<li class="list-none -ml-5">-</li>@endforelse
                    </ol>
                </div>

                <div>
                    <div class="{{ $label }}">Kepada</div>
                    <p class="mt-1">{{ $st->kepada }}</p>
                </div>

                <div>
                    <div class="{{ $label }}">Untuk</div>
                    <ol class="mt-1 list-decimal pl-5 space-y-1">@foreach ($st->untuk ?? [] as $u)
                    <li>{{ $u }}</li>@endforeach
                    </ol>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="{{ $label }} mb-2">Peserta ({{ $st->peserta->count() }})</div>
                <div class="overflow-x-auto rounded-lg border">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50 text-xs text-gray-500">
                            <tr>
                                <th class="px-3 py-2 w-10 font-medium">No</th>
                                <th class="px-3 py-2 font-medium">Nama / NIP</th>
                                <th class="px-3 py-2 font-medium">Jabatan</th>
                                @if ($adaTanggal)
                                <th class="px-3 py-2 font-medium">Tanggal Tugas</th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($st->peserta as $i => $p)
                                <tr>
                                    <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2">{{ $p->nama }}@if ($p->nip)
                                    <div class="text-xs text-gray-500">NIP. {{ $p->nip }}</div>@endif
                                        <div class="text-xs text-gray-400">{{ $p->jenis_label }}</div>
                                    </td>
                                    <td class="px-3 py-2">{{ $p->jabatan }}</td>
                                    @if ($adaTanggal)
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            {{ $p->tanggal_tugas_indo ?? 'Sesuai rentang ST' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Pengelompokan (Tusi) --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 sm:p-6 text-sm mt-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="{{ $label }}">Pengelompokan (Tusi)</div>

            @if ($st->tusi)
                <span class="px-3 py-1 rounded-full text-xs font-medium"
                    style="background:#e0e7ff;color:#3730a3;">{{ $st->tusi->nama }}</span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-medium"
                    style="background:#fef3c7;color:#92400e;">Belum dikelompokkan</span>
            @endif
        </div>

        @if ($bolehUbahTusi)
            <form method="POST" action="{{ route('surat-tugas.tusi', $st) }}"
                class="mt-3 flex flex-col sm:flex-row gap-2 sm:items-center">
                @csrf
                @method('PATCH')

                <select name="tusi_id" class="border rounded-lg px-3 py-2 text-sm w-full sm:max-w-md">
                    <option value="">— Belum dikelompokkan —</option>
                    @if ($tusiNonaktifDipakai)
                        <option value="{{ $st->tusi_id }}" selected>{{ $st->tusi->nama }} (nonaktif)</option>
                    @endif
                    @foreach ($tusiOptions as $t)
                        <option value="{{ $t->id }}" @selected((int) old('tusi_id', $st->tusi_id) === (int) $t->id)>
                            {{ $t->nama }}</option>
                    @endforeach
                </select>

                <button class="px-4 py-2 rounded-lg text-sm" style="background:#2563eb;color:#fff;">Simpan</button>
            </form>

            @error('tusi_id')
                <p class="mt-2 text-xs" style="color:#b91c1c;">{{ $message }}</p>
            @enderror

            <p class="mt-2 text-xs text-gray-500">Pengelompokan tetap bisa diubah setelah ST terbit dan tidak
                memengaruhi isi surat.</p>
        @else
            <p class="mt-2 text-xs text-gray-500">Hanya admin atau pembuat ST yang dapat mengubah pengelompokan.</p>
        @endif
    </div>

    {{-- Laporan Hasil ST --}}
    @if (in_array($st->status, ['terbit', 'selesai']))
        @php
            $laporan = $laporan ?? null;
            $tugasLapor = $tugasLapor ?? collect();
            $bolehIsiLaporan = $bolehIsiLaporan ?? false;

            $statusLaporan = $laporan ? ($laporan->status === 'dikirim' ? 'Dikirim' : 'Draf') : 'Belum diisi';
            $warnaLaporan = [
                'Dikirim' => 'background:#dcfce7;color:#15803d;',
                'Draf' => 'background:#fef3c7;color:#92400e;',
                'Belum diisi' => 'background:#f3f4f6;color:#374151;',
            ][$statusLaporan];
            $warnaTugas = [
                'menunggu' => 'background:#fef3c7;color:#92400e;',
                'selesai' => 'background:#dcfce7;color:#15803d;',
                'ditutup' => 'background:#f3f4f6;color:#6b7280;',
            ];

            $barisTL = old('tindak_lanjut', $laporan
                ? $laporan->tindakLanjut->map(fn($t) => [
                    'uraian' => $t->uraian,
                    'pic' => $t->pic,
                    'tenggat' => optional($t->tenggat)->format('Y-m-d'),
                ])->values()->all()
                : []);
            if (empty($barisTL)) {
                $barisTL = [['uraian' => '', 'pic' => '', 'tenggat' => '']];
            }
        @endphp

        <div class="bg-white rounded-xl shadow-sm border p-5 sm:p-6 text-sm mt-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="{{ $label }}">Laporan Hasil ST</div>
                <span class="px-3 py-1 rounded-full text-xs font-medium"
                    style="{{ $warnaLaporan }}">{{ $statusLaporan }}</span>
            </div>

            {{-- Penugasan --}}
            <div class="mt-3">
                <div class="{{ $label }} mb-1">Penugasan</div>
                @forelse ($tugasLapor as $tg)
                    <div class="flex items-center justify-between py-1.5 border-b last:border-0">
                        <span>{{ $tg->user->name ?? '—' }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs"
                            style="{{ $warnaTugas[$tg->status] ?? '' }}">{{ ucfirst($tg->status) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">Tidak ada peserta yang akunnya dipetakan ke pegawai, jadi tidak ada
                        tugas lapor otomatis. Admin masih bisa mengisi laporan.</p>
                @endforelse
                @if ($tugasLapor->isNotEmpty())
                    <p class="text-xs text-gray-500 mt-1">Cukup satu peserta yang mengirim laporan; tugas peserta lain
                        otomatis ditutup.</p>
                @endif
            </div>

            @if ($errors->any())
                <div class="rounded-lg p-3 mt-3 text-xs" style="background:#fee2e2;color:#b91c1c;">
                    {{ $errors->first() }}</div>
            @endif

            @if ($bolehIsiLaporan)
                {{-- Form isi laporan (Alpine untuk baris tindak lanjut) --}}
                <form method="POST" action="{{ route('surat-tugas.laporan.simpan', $st) }}" class="mt-4 space-y-4"
                    x-data="{ rows: @js($barisTL) }">
                    @csrf

                    <div>
                        <label class="{{ $label }}">Notulensi / Hasil Rapat <span
                                style="color:#dc2626;">*</span></label>
                        <textarea name="notulensi" rows="8"
                            class="mt-1 w-full border rounded-lg px-3 py-2">{{ old('notulensi', $laporan?->notulensi) }}</textarea>
                    </div>

                    <div>
                        <label class="{{ $label }}">Keputusan</label>
                        <textarea name="keputusan" rows="3"
                            class="mt-1 w-full border rounded-lg px-3 py-2">{{ old('keputusan', $laporan?->keputusan) }}</textarea>
                    </div>

                    <div>
                        <div class="{{ $label }} mb-1">Tindak Lanjut</div>
                        <div class="space-y-2">
                            <template x-for="(row, i) in rows" :key="i">
                                <div class="grid grid-cols-12 gap-2 items-start">
                                    <input type="text" x-model="row.uraian" :name="`tindak_lanjut[${i}][uraian]`"
                                        placeholder="Uraian tindak lanjut"
                                        class="col-span-12 md:col-span-6 border rounded-lg px-2 py-1.5">
                                    <input type="text" x-model="row.pic" :name="`tindak_lanjut[${i}][pic]`"
                                        placeholder="PIC" class="col-span-6 md:col-span-3 border rounded-lg px-2 py-1.5">
                                    <input type="date" x-model="row.tenggat" :name="`tindak_lanjut[${i}][tenggat]`"
                                        class="col-span-5 md:col-span-2 border rounded-lg px-2 py-1.5">
                                    <button type="button" @click="rows.splice(i, 1)" x-show="rows.length > 1"
                                        class="col-span-1 py-1.5" style="color:#dc2626;" title="Hapus baris">✕</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="rows.push({ uraian: '', pic: '', tenggat: '' })"
                            class="mt-2 text-xs" style="color:#2563eb;">+ Tambah tindak lanjut</button>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <button name="aksi" value="draf" class="px-4 py-2 rounded-lg border bg-white">Simpan
                            Draf</button>
                        <button name="aksi" value="kirim" class="px-4 py-2 rounded-lg"
                            style="background:#16a34a;color:#fff;"
                            onclick="return confirm('Kirim laporan? Setelah dikirim laporan dikunci, tugas peserta lain ditutup, dan ST ditandai selesai.')">Kirim
                            Laporan</button>
                    </div>
                </form>
            @elseif ($laporan)
                {{-- Hanya lihat --}}
                <div class="mt-4 space-y-4 leading-relaxed">
                    <div>
                        <div class="{{ $label }}">Notulensi / Hasil Rapat</div>
                        <p class="mt-1 whitespace-pre-line">{{ $laporan->notulensi ?: '-' }}</p>
                    </div>
                    <div>
                        <div class="{{ $label }}">Keputusan</div>
                        <p class="mt-1 whitespace-pre-line">{{ $laporan->keputusan ?: '-' }}</p>
                    </div>
                    <div>
                        <div class="{{ $label }} mb-1">Tindak Lanjut</div>
                        @forelse ($laporan->tindakLanjut as $t)
                            <div class="py-1.5 border-b last:border-0">
                                {{ $loop->iteration }}. {{ $t->uraian }}
                                <div class="text-xs text-gray-500">
                                    PIC: {{ $t->pic ?: '-' }} · Tenggat:
                                    {{ $t->tenggat ? $t->tenggat->locale('id')->translatedFormat('j F Y') : '-' }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">-</p>
                        @endforelse
                    </div>
                    @if ($laporan->isDikirim())
                        <p class="text-xs text-gray-500">Dikirim oleh {{ $laporan->dikirimOleh->name ?? '-' }}
                            pada {{ optional($laporan->dikirim_at)->format('d/m/Y H:i') }}.</p>
                    @endif
                </div>
            @else
                <p class="mt-3 text-xs text-gray-500">Laporan belum diisi.</p>
            @endif
        </div>
    @endif
</x-app-layout>