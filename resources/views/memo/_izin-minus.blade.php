{{--
spj-mkdi: info sisa efektif MAK + pengajuan izin minus.
Dipasang di form memo (create & edit) tepat di bawah field Nominal:
    @include('memo._izin-minus', ['izinMinus' => $izinMinus ?? null])

Data dari controller:
- $makSisaEfektif : [kode MAK => sisa efektif] (sisa FA SAKTI - outstanding memo semua unit)
- $izinMinus      : baris izin terakhir memo ini (khusus edit), boleh null

Field yang dikirim (tidak disimpan di memo_entries, diproses MemoController):
- ajukan_izin_minus (1/0), alasan_minus, dasar_minus
JS membaca #mak-input dan #nominal-input yang sudah ada di form.
--}}

@php
    $makSisaEfektif = $makSisaEfektif ?? [];
    $izinMinus = $izinMinus ?? null;
    if ($izinMinus && $izinMinus->status === 'gugur') {
        $izinMinus = null;
    }
    $menunggu = $izinMinus && $izinMinus->status === 'menunggu';
    $centangAwal = (bool) old('ajukan_izin_minus', false)
        || $errors->has('alasan_minus')
        || ($izinMinus && $izinMinus->status === 'menunggu');
@endphp

<div id="izin-minus-box" class="space-y-3">

    {{-- Info sisa efektif MAK terpilih (diisi JS) --}}
    <div id="sisa-mak-info"
        class="hidden rounded-xl border px-3.5 py-2.5 text-xs leading-relaxed"></div>

    @if ($izinMinus)
        @php
            $warna = match ($izinMinus->status) {
                'disetujui' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'ditolak' => 'border-red-200 bg-red-50 text-red-700',
                default => 'border-amber-200 bg-amber-50 text-amber-800',
            };
        @endphp
        <div class="rounded-xl border px-3.5 py-2.5 text-xs {{ $warna }}">
            <p class="font-semibold">Status izin minus terakhir: {{ ucfirst($izinMinus->status) }}</p>
            <p class="mt-0.5">Kurang Rp {{ number_format((int) $izinMinus->kekurangan, 0, ',', '.') }} ·
                Alasan: {{ $izinMinus->alasan }}</p>
            @if ($izinMinus->status === 'ditolak' && filled($izinMinus->catatan_putusan))
                <p class="mt-0.5">Catatan: {{ $izinMinus->catatan_putusan }}</p>
            @endif
            @if ($izinMinus->status === 'disetujui')
                <p class="mt-0.5 opacity-80">Izin tetap berlaku selama MAK sama dan nominal tidak dinaikkan.</p>
            @endif
        </div>
    @endif

    {{-- Pengajuan izin minus --}}
    <div id="izin-minus-form"
        class="{{ $centangAwal ? '' : 'hidden' }} rounded-xl border border-amber-200 bg-amber-50/60 p-3.5 space-y-3">
        <label class="flex items-start gap-2.5 cursor-pointer">
            <input type="hidden" name="ajukan_izin_minus" value="0">
            <input type="checkbox" name="ajukan_izin_minus" value="1" id="izin-minus-cek" @checked($centangAwal)
                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
            <span>
                <span class="block text-sm font-semibold text-amber-900">Ajukan izin minus</span>
                <span class="block text-xs text-amber-800">
                    Nominal melebihi sisa anggaran MAK. Memo tetap tersimpan sebagai draft, tapi
                    <b>tidak bisa dikirim ke Keuangan</b> sebelum izin disetujui PPK / Keuangan.
                </span>
            </span>
        </label>

        <div id="izin-minus-isian" class="{{ $centangAwal ? '' : 'hidden' }} space-y-3">
            <div>
                <label for="alasan_minus" class="block text-xs font-semibold text-gray-600 mb-1">
                    Alasan <span class="text-red-400">*</span>
                </label>
                <textarea id="alasan_minus" name="alasan_minus" rows="2"
                    placeholder="mis. Kegiatan prioritas pimpinan, kekurangan ditutup dari revisi POK"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none">{{ old('alasan_minus', $menunggu ? $izinMinus->alasan : '') }}</textarea>
                @error('alasan_minus')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="dasar_minus" class="block text-xs font-semibold text-gray-600 mb-1">
                    Dasar / dokumen pendukung <span class="font-normal text-gray-400">(opsional)</span>
                </label>
                <input id="dasar_minus" type="text" name="dasar_minus" value="{{ old('dasar_minus', $menunggu ? $izinMinus->dasar : '') }}"
                    placeholder="mis. ND Deputi No. 12/2026, usulan revisi anggaran, disposisi pimpinan"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-100 outline-none">
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const SISA = @json($makSisaEfektif);
        const mak = document.getElementById('mak-input');
        const nom = document.getElementById('nominal-input');
        const info = document.getElementById('sisa-mak-info');
        const box = document.getElementById('izin-minus-form');
        const cek = document.getElementById('izin-minus-cek');
        const isian = document.getElementById('izin-minus-isian');
        if (!mak || !nom || !info || !box) return;

        const rp = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');
        const angka = (v) => Number(String(v || '').replace(/\./g, '').replace(/[^0-9]/g, '')) || 0;
        const tetapTampil = box.classList.contains('hidden') === false; // izin menunggu / ada error

        function hitung() {
            const kode = (mak.value || '').trim();
            const n = angka(nom.value);
            if (!kode || !(kode in SISA)) {
                info.classList.add('hidden');
                if (!tetapTampil) box.classList.add('hidden');
                return;
            }
            const efektif = Number(SISA[kode]) || 0;
            const kurang = n - efektif;
            info.classList.remove('hidden');
            if (kurang > 0) {
                info.className = 'rounded-xl border px-3.5 py-2.5 text-xs leading-relaxed border-red-200 bg-red-50 text-red-700';
                info.innerHTML = 'Sisa efektif MAK ini <b>' + rp(efektif) + '</b>. Nominal melebihi sisa, kurang <b>' + rp(kurang) + '</b>.';
                box.classList.remove('hidden');
            } else {
                info.className = 'rounded-xl border px-3.5 py-2.5 text-xs leading-relaxed border-emerald-200 bg-emerald-50 text-emerald-800';
                info.innerHTML = 'Sisa efektif MAK ini <b>' + rp(efektif) + '</b>' + (n ? ', setelah memo ini tersisa <b>' + rp(efektif - n) + '</b>.' : '.');
                if (!tetapTampil) { box.classList.add('hidden'); if (cek) cek.checked = false; }
            }
        }

        if (cek && isian) cek.addEventListener('change', () => isian.classList.toggle('hidden', !cek.checked));

        // MAK bisa berubah lewat dropdown custom (tanpa event input), jadi cek ulang setelah setiap interaksi.
        ['input', 'change', 'click', 'keyup'].forEach((ev) =>
            document.addEventListener(ev, () => setTimeout(hitung, 0)));
        hitung();
    })();
</script>
