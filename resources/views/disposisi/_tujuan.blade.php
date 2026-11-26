{{--
    Pilih penerima + instruksi + catatan + batas waktu.
    Dipakai di form teruskan (show), dan form catat surat bila memanggilnya.

    Variabel:
      $penerima : collection of array
                  wajib : id, nama, unit
                  opsional : role, user (model User, untuk avatar), status ('pns' | 'non_pns')
                  Bila tidak ada satu pun item yang punya 'status', daftar tampil tanpa pemisahan PNS / Non-PNS.
      $idForm   : string unik per form
--}}
@php
    $idForm = $idForm ?? 'tujuan';
    $dipilih = collect(old('kepada', []))->map(fn($v) => (int) $v)->all();
    $instruksiLama = old('instruksi', []);

    $daftar = collect($penerima);
    $adaStatus = $daftar->contains(fn($p) => array_key_exists('status', $p));

    $perBagian = $daftar->groupBy(fn($p) => $adaStatus ? ($p['status'] ?? 'non_pns') : 'semua');
    $bagian = $adaStatus ? ['pns' => 'PNS', 'non_pns' => 'Non-PNS'] : ['semua' => 'Semua'];

    $dasar = 'block w-full rounded-lg bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 border shadow-none outline-none transition focus:ring-4';
    $normal = 'border-slate-300 hover:border-slate-400 focus:border-blue-500 focus:ring-blue-100';
    $salah = 'border-red-400 focus:border-red-500 focus:ring-red-100';
    $lbl = 'mb-1.5 block text-sm font-medium text-slate-700';
    $galat = 'mt-1.5 text-xs text-red-600';
@endphp

<div class="space-y-5">

    {{-- ================= Penerima ================= --}}
    <div
        x-data="{
            q: '',
            tab: 'semua',
            pilih: [],
            cocok(el) { return el.dataset.cari.includes(this.q.toLowerCase().trim()); },
            sync() {
                this.pilih = Array.from($root.querySelectorAll('input[data-orang]:checked')).map(i => ({
                    id: i.value,
                    nama: i.dataset.nama,
                    av: i.closest('label').querySelector('[data-av]').innerHTML
                }));
            },
            hapus(id) {
                const el = Array.from($root.querySelectorAll('input[data-orang]')).find(i => i.value === String(id));
                if (el) { el.checked = false; this.sync(); }
            },
            semua(grp) {
                const kotak = Array.from(grp.querySelectorAll('input[data-orang]')).filter(i => i.closest('label').offsetParent !== null);
                const semuaOn = kotak.length > 0 && kotak.every(i => i.checked);
                kotak.forEach(i => { i.checked = !semuaOn; });
                this.sync();
            }
        }"
        x-init="sync()">

        <div class="mb-1.5 flex items-center justify-between gap-2">
            <label class="block text-sm font-medium text-slate-700">
                Kepada <span class="text-red-500" aria-hidden="true">*</span>
                <span class="font-normal text-slate-400">(boleh lebih dari satu)</span>
            </label>
            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700" x-text="pilih.length + ' dipilih'">0 dipilih</span>
        </div>

        {{-- Yang sudah dipilih --}}
        <div x-show="pilih.length" x-cloak class="mb-2 flex flex-wrap gap-1.5">
            <template x-for="o in pilih" :key="o.id">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 py-0.5 pl-0.5 pr-2 text-xs text-blue-800 ring-1 ring-inset ring-blue-100">
                    <span class="flex-none [&>*]:!h-5 [&>*]:!w-5 [&>*]:!text-[8px]" x-html="o.av"></span>
                    <span x-text="o.nama"></span>
                    <button type="button" @click="hapus(o.id)" class="text-blue-400 hover:text-blue-700" aria-label="Hapus dari pilihan">&times;</button>
                </span>
            </template>
        </div>

        {{-- Cari + tab PNS / Non-PNS --}}
        <div class="mb-2 flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" /></svg>
                <input type="search" x-model="q" placeholder="Cari nama, unit, atau jabatan…"
                    class="{{ $dasar }} {{ $normal }} !py-2 pl-9">
            </div>

            @if ($adaStatus)
                <div class="inline-flex flex-none gap-0.5 rounded-lg bg-slate-100 p-0.5 text-xs font-medium" role="tablist" aria-label="Status kepegawaian">
                    @foreach ([['semua', 'Semua', $daftar->count()], ['pns', 'PNS', $perBagian->get('pns', collect())->count()], ['non_pns', 'Non-PNS', $perBagian->get('non_pns', collect())->count()]] as [$k, $t, $n])
                        <button type="button" role="tab" @click="tab = '{{ $k }}'"
                            :class="tab === '{{ $k }}' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="rounded-md px-3 py-1.5 transition focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100">
                            {{ $t }} <span class="text-slate-400">{{ $n }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Daftar orang --}}
        <div class="max-h-72 overflow-y-auto rounded-xl border border-slate-200" @change="sync()">
            @if ($daftar->isEmpty())
                <p class="px-3 py-8 text-center text-sm text-slate-400">Belum ada user lain.</p>
            @else
                @foreach ($bagian as $kunci => $namaBagian)
                    @php
                        $isi = $perBagian->get($kunci, collect());
                        $perUnit = $isi->groupBy('unit');
                    @endphp
                    @continue($isi->isEmpty())

                    <div data-cari="{{ strtolower($isi->pluck('unit')->unique()->implode(' ') . ' ' . $isi->pluck('nama')->implode(' ') . ' ' . $isi->pluck('role')->filter()->implode(' ')) }}"
                        x-show="(tab === 'semua' || tab === '{{ $kunci }}') && cocok($el)">

                        @if ($adaStatus)
                            <p class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-slate-100 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                {{ $namaBagian }}
                                <span class="font-normal normal-case tracking-normal text-slate-400">{{ $isi->count() }} orang</span>
                            </p>
                        @endif

                        @foreach ($perUnit as $unit => $anggota)
                            <div data-unit
                                data-cari="{{ strtolower($unit . ' ' . $anggota->pluck('nama')->implode(' ') . ' ' . $anggota->pluck('role')->filter()->implode(' ')) }}"
                                x-show="cocok($el)">
                                <div class="flex items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-3 py-1.5">
                                    <p class="min-w-0 truncate text-xs font-medium text-slate-500">{{ $unit }}</p>
                                    <button type="button" @click="semua($el.closest('[data-unit]'))"
                                        class="flex-none text-xs font-medium text-blue-600 hover:text-blue-800 focus:outline-none focus-visible:underline">Pilih semua</button>
                                </div>

                                <div class="divide-y divide-slate-100">
                                    @foreach ($anggota as $p)
                                        <label data-cari="{{ strtolower($p['nama'] . ' ' . ($p['role'] ?? '') . ' ' . $unit) }}"
                                            x-show="cocok($el)"
                                            class="flex cursor-pointer items-center gap-3 px-3 py-2 transition hover:bg-slate-50 has-[:checked]:bg-blue-50/60">
                                            <input type="checkbox" name="kepada[]" value="{{ $p['id'] }}" data-orang data-nama="{{ $p['nama'] }}"
                                                @checked(in_array($p['id'], $dipilih, true))
                                                class="h-4 w-4 flex-none rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                            <span data-av class="flex-none">
                                                @if (!empty($p['user']))
                                                    <x-avatar :user="$p['user']" class="h-8 w-8 text-[10px]" />
                                                @else
                                                    <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600">{{ strtoupper(mb_substr($p['nama'], 0, 1)) }}</span>
                                                @endif
                                            </span>

                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-medium text-slate-900">{{ $p['nama'] }}</span>
                                                @if (!empty($p['role']))
                                                    <span class="block truncate text-xs text-slate-400">{{ $p['role'] }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>

        @error('kepada')
            <p class="{{ $galat }}">{{ $message }}</p>
        @enderror
        @error('kepada.*')
            <p class="{{ $galat }}">{{ $message }}</p>
        @enderror
    </div>

    {{-- ================= Instruksi ================= --}}
    <div>
        <label class="{{ $lbl }}">Instruksi <span class="text-red-500" aria-hidden="true">*</span></label>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Models\Disposisi::INSTRUKSI as $key => $label)
                <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-slate-200 px-3 py-2 text-sm transition hover:border-blue-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/60">
                    <input type="checkbox" name="instruksi[]" value="{{ $key }}" @checked(in_array($key, $instruksiLama, true))
                        class="h-4 w-4 flex-none rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-slate-700">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('instruksi')
            <p class="{{ $galat }}">{{ $message }}</p>
        @enderror
    </div>

    {{-- ================= Catatan + batas waktu ================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="sm:col-span-2">
            <label for="{{ $idForm }}-catatan" class="{{ $lbl }}">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
            <textarea id="{{ $idForm }}-catatan" name="catatan" rows="2" maxlength="2000" placeholder="Arahan tambahan"
                class="{{ $dasar }} {{ $errors->has('catatan') ? $salah : $normal }}">{{ old('catatan') }}</textarea>
            @error('catatan')
                <p class="{{ $galat }}">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="{{ $idForm }}-batas" class="{{ $lbl }}">Batas waktu <span class="font-normal text-slate-400">(opsional)</span></label>
            <input id="{{ $idForm }}-batas" type="date" name="batas_waktu" value="{{ old('batas_waktu') }}"
                class="{{ $dasar }} {{ $errors->has('batas_waktu') ? $salah : $normal }}">
            @error('batas_waktu')
                <p class="{{ $galat }}">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>