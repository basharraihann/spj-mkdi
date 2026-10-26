{{--
    Partial form Surat Keluar (dipakai create & edit).
    Variabel: $surat (SuratKeluar|null)
    Nama field TIDAK diubah dari versi sebelumnya (isi_payload[...]); ditambah: template, kode_klasifikasi,
    dengan_lampiran, perlu_es1, penandatangan, tembusan.
--}}
@php
    $p = fn ($k, $d = null) => old('isi_payload.' . $k, data_get($surat?->isi_payload, $k, $d));
    $jenisAwal = old('jenis_dokumen', $surat->jenis_dokumen ?? 'nota_dinas');
    $tplNdAwal = $jenisAwal === 'nota_dinas' && $p('template') ? $p('template') : 'es2';
    $tplUdAwal = $jenisAwal === 'undangan' && $p('template') ? $p('template') : 'internal';
    $tembusanVal = old('tembusan', $surat ? implode("\n", (array) ($surat->tembusan ?? [])) : '');
    if (is_array($tembusanVal)) {
        $tembusanVal = implode("\n", $tembusanVal);
    }
    $inp = 'w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500';
    $lbl = 'block text-xs font-semibold text-slate-700 mb-1';
    $sect = 'space-y-4 pt-4 border-t border-slate-200';
@endphp

<div class="space-y-6" x-data="{ jenis: '{{ $jenisAwal }}', tplNd: '{{ $tplNdAwal }}', tplUd: '{{ $tplUdAwal }}', lampiran: {{ $p('dengan_lampiran', 0) ? 'true' : 'false' }} }">

    {{-- Pilihan Jenis Dokumen --}}
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
        <label class="block text-sm font-bold text-slate-800">Pilih Jenis Dokumen Surat <span class="text-red-600">*</span></label>
        <select name="jenis_dokumen" x-model="jenis" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 font-semibold">
            @foreach (\App\Models\SuratKeluar::JENIS as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
        </select>

        {{-- Varian template: Nota Dinas --}}
        <fieldset x-show="jenis === 'nota_dinas'" :disabled="jenis !== 'nota_dinas'" x-cloak class="pt-2">
            <label class="{{ $lbl }}">Template Nota Dinas</label>
            <select name="isi_payload[template]" x-model="tplNd" class="{{ $inp }}">
                @foreach (\App\Models\SuratKeluar::TEMPLATE['nota_dinas'] as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <p class="text-[11px] text-slate-500 mt-1">Tanda tangan hanya Karo dan Sesdep. Lebih dari 2 lembar dibuat surat bersambung. Output dicetak untuk tanda tangan basah.</p>
        </fieldset>

        {{-- Varian template: Undangan / Surat Dinas --}}
        <fieldset x-show="jenis === 'undangan'" :disabled="jenis !== 'undangan'" x-cloak class="pt-2">
            <label class="{{ $lbl }}">Template Undangan / Surat Dinas</label>
            <select name="isi_payload[template]" x-model="tplUd" class="{{ $inp }}">
                @foreach (\App\Models\SuratKeluar::TEMPLATE['undangan'] as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </fieldset>
    </div>

    {{-- Header Umum --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="{{ $lbl }}">Nomor Surat / Dokumen</label>
            <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $surat->nomor_surat ?? '') }}" placeholder="Contoh: B-12/HM.02/2026 atau kosongi jika Draf" class="{{ $inp }}">
        </div>
        <div>
            <label class="{{ $lbl }}">Kode Klasifikasi Surat</label>
            <input type="text" name="isi_payload[kode_klasifikasi]" value="{{ $p('kode_klasifikasi') }}" placeholder="Contoh: HM.02 (diisi oleh penginput)" class="{{ $inp }}">
        </div>
        <div>
            <label class="{{ $lbl }}">Tanggal Surat <span class="text-red-600">*</span></label>
            <input type="date" name="tanggal_surat" required
                value="{{ old('tanggal_surat', optional($surat?->tanggal_surat)->format('Y-m-d') ?? date('Y-m-d')) }}" class="{{ $inp }}">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $lbl }}">Hal / Perihal / Judul <span class="text-red-600">*</span></label>
            <input type="text" name="hal" value="{{ old('hal', $surat->hal ?? '') }}" required placeholder="Subjek atau perihal dokumen..." class="{{ $inp }}">
        </div>
        <div>
            <label class="{{ $lbl }}">Derajat Klasifikasi / Sifat <span class="text-red-600">*</span></label>
            <select name="sifat" required class="{{ $inp }}">
                @foreach (['biasa' => 'Biasa (B)', 'terbatas' => 'Terbatas (T) - anggaran, temuan BPK', 'rahasia' => 'Rahasia (R) - pelantikan'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('sifat', $surat->sifat ?? 'biasa') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tujuan & Dari (Nota Dinas, Undangan, Laporan) --}}
    <fieldset x-show="['nota_dinas','undangan','laporan'].includes(jenis)" :disabled="!['nota_dinas','undangan','laporan'].includes(jenis)" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $lbl }}">Yth. (Tujuan)</label>
            <input type="text" name="tujuan" value="{{ old('tujuan', $surat->tujuan ?? '') }}"
                :placeholder="jenis === 'nota_dinas' ? (tplNd === 'es1_deputi2' ? 'Sekretaris Kementerian Koordinator' : 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian') : 'Tanpa \'di Tempat\' atau \'di Jakarta\''"
                class="{{ $inp }}">
        </div>
        <div>
            <label class="{{ $lbl }}">Dari</label>
            <input type="text" name="dari" value="{{ old('dari', $surat->dari ?? '') }}"
                :placeholder="(jenis === 'nota_dinas' && tplNd === 'es1_deputi2') ? 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian' : 'Sekretaris Deputi Bidang Koordinasi Usaha Pangan dan Pertanian'"
                class="{{ $inp }}">
        </div>
    </fieldset>

    {{-- Dengan / tanpa lampiran --}}
    <fieldset x-show="['nota_dinas','undangan'].includes(jenis)" :disabled="!['nota_dinas','undangan'].includes(jenis)" x-cloak class="space-y-2">
        <input type="hidden" name="isi_payload[dengan_lampiran]" value="0">
        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
            <input type="checkbox" name="isi_payload[dengan_lampiran]" value="1" x-model="lampiran" class="rounded border-slate-300 text-blue-600">
            Dokumen dengan lampiran
        </label>
        <div x-show="lampiran && jenis === 'nota_dinas'" x-cloak>
            <label class="{{ $lbl }}">Keterangan Lampiran</label>
            <input type="text" name="isi_payload[lampiran]" value="{{ $p('lampiran') }}" placeholder="Contoh: 1 (Satu) Berkas" class="{{ $inp }}">
        </div>
    </fieldset>

    {{-- 1. NOTA DINAS --}}
    <fieldset x-show="jenis === 'nota_dinas'" :disabled="jenis !== 'nota_dinas'" x-cloak class="{{ $sect }}">
        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Nota Dinas</h4>
        <div>
            <label class="{{ $lbl }}">Isi Nota Dinas</label>
            <textarea name="isi_payload[isi_ringkasan]" rows="7" placeholder="Sehubungan dengan ... bersama ini kami sampaikan laporan sebagai berikut: ..." class="{{ $inp }}">{{ $p('isi_ringkasan') }}</textarea>
        </div>
    </fieldset>

    {{-- 2. UNDANGAN --}}
    <fieldset x-show="jenis === 'undangan'" :disabled="jenis !== 'undangan'" x-cloak class="{{ $sect }}">
        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Surat Undangan</h4>
        <div>
            <label class="{{ $lbl }}">Dalam rangka (kalimat pembuka)</label>
            <input type="text" name="isi_payload[pembuka]" value="{{ $p('pembuka') }}" placeholder="Dalam rangka ..., bersama ini kami mengundang Saudara untuk hadir dalam rapat yang akan dilaksanakan pada:" class="{{ $inp }}">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="{{ $lbl }}">Hari / Tanggal Acara</label>
                <input type="text" name="isi_payload[hari_tanggal]" value="{{ $p('hari_tanggal') }}" placeholder="Senin / 26 Oktober 2026" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Waktu / Pukul</label>
                <input type="text" name="isi_payload[waktu]" value="{{ $p('waktu', '09.00 WIB s.d. Selesai') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Tempat</label>
                <input type="text" name="isi_payload[tempat]" value="{{ $p('tempat') }}" placeholder="Ruang Rapat lantai 3..." class="{{ $inp }}">
            </div>
        </div>
        <div>
            <label class="{{ $lbl }}">Acara / Agenda</label>
            <input type="text" name="isi_payload[acara]" value="{{ $p('acara') }}" placeholder="Rapat Koordinasi Evaluasi Kinerja..." class="{{ $inp }}">
        </div>
        <div x-show="lampiran" x-cloak>
            <label class="{{ $lbl }}">Daftar Pejabat / Pegawai yang Diundang (satu per baris, untuk lembar lampiran)</label>
            <textarea name="isi_payload[daftar_undangan]" rows="5" class="{{ $inp }}">{{ $p('daftar_undangan') }}</textarea>
        </div>
        <div>
            <input type="hidden" name="isi_payload[perlu_es1]" value="0">
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                <input type="checkbox" name="isi_payload[perlu_es1]" value="1" @checked($p('perlu_es1', 0)) class="rounded border-slate-300 text-blue-600">
                Undangan dari Deputi (pemeriksaan 2 tahap: Eselon 2 lalu Eselon 1)
            </label>
        </div>
    </fieldset>

    {{-- 3. TELAAH STAF --}}
    <fieldset x-show="jenis === 'telaah_staf'" :disabled="jenis !== 'telaah_staf'" x-cloak class="{{ $sect }}">
        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Telaah Staf</h4>
        @foreach ([['persoalan', 'I. Persoalan', 2], ['praanggapan', 'II. Praanggapan', 2], ['fakta', 'III. Fakta yang Mempengaruhi', 2], ['analisis', 'IV. Analisis', 3], ['kesimpulan', 'V. Kesimpulan', 2], ['saran', 'VI. Saran', 2]] as [$k, $label, $rows])
            <div>
                <label class="{{ $lbl }}">{{ $label }}</label>
                <textarea name="isi_payload[{{ $k }}]" rows="{{ $rows }}" class="{{ $inp }}">{{ $p($k) }}</textarea>
            </div>
        @endforeach
    </fieldset>

    {{-- 4. NOTULA --}}
    <fieldset x-show="jenis === 'notula'" :disabled="jenis !== 'notula'" x-cloak class="{{ $sect }}">
        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Notula Rapat</h4>
        <p class="text-[11px] text-slate-500">Judul notula diambil dari kolom Hal / Judul di atas.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $lbl }}">Dasar</label>
                <input type="text" name="isi_payload[dasar]" value="{{ $p('dasar') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Agenda</label>
                <input type="text" name="isi_payload[agenda]" value="{{ $p('agenda') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Waktu</label>
                <input type="text" name="isi_payload[waktu]" value="{{ $p('waktu') }}" placeholder="Senin, 26 Oktober 2026, pukul 09.00 WIB" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Tempat</label>
                <input type="text" name="isi_payload[tempat]" value="{{ $p('tempat') }}" class="{{ $inp }}">
            </div>
        </div>
        <div>
            <label class="{{ $lbl }}">Peserta Rapat (satu per baris)</label>
            <textarea name="isi_payload[peserta]" rows="3" class="{{ $inp }}">{{ $p('peserta') }}</textarea>
        </div>
        <div>
            <label class="{{ $lbl }}">Pelaksanaan / Poin Pembahasan (satu poin per baris)</label>
            <textarea name="isi_payload[pelaksanaan]" rows="5" class="{{ $inp }}">{{ $p('pelaksanaan') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="{{ $lbl }}">Mengetahui: Jabatan Atasan Langsung</label>
                <input type="text" name="isi_payload[mengetahui_jabatan]" value="{{ $p('mengetahui_jabatan') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Mengetahui: Nama Lengkap</label>
                <input type="text" name="isi_payload[mengetahui_nama]" value="{{ $p('mengetahui_nama') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Notulis (Nama Lengkap)</label>
                <input type="text" name="isi_payload[notulis]" value="{{ $p('notulis') }}" class="{{ $inp }}">
            </div>
        </div>
    </fieldset>

    {{-- 5. SURAT PERNYATAAN --}}
    <fieldset x-show="jenis === 'surat_pernyataan'" :disabled="jenis !== 'surat_pernyataan'" x-cloak class="{{ $sect }}">
        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Surat Pernyataan</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $lbl }}">Nama Yang Menyatakan</label>
                <input type="text" name="isi_payload[nama_menyatakan]" value="{{ $p('nama_menyatakan') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">NIP Yang Menyatakan</label>
                <input type="text" name="isi_payload[nip_menyatakan]" value="{{ $p('nip_menyatakan') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Pangkat / Golongan</label>
                <input type="text" name="isi_payload[pangkat_golongan]" value="{{ $p('pangkat_golongan') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Jabatan</label>
                <input type="text" name="isi_payload[jabatan_menyatakan]" value="{{ $p('jabatan_menyatakan') }}" class="{{ $inp }}">
            </div>
        </div>
        <div>
            <label class="{{ $lbl }}">Isi Pernyataan (dengan ini menyatakan bahwa ...)</label>
            <textarea name="isi_payload[isi_pernyataan]" rows="3" class="{{ $inp }}">{{ $p('isi_pernyataan') }}</textarea>
        </div>
        <div>
            <label class="{{ $lbl }}">Untuk keperluan</label>
            <input type="text" name="isi_payload[keperluan]" value="{{ $p('keperluan') }}" class="{{ $inp }}">
        </div>
    </fieldset>

    {{-- 6. LAPORAN --}}
    <fieldset x-show="jenis === 'laporan'" :disabled="jenis !== 'laporan'" x-cloak class="{{ $sect }}">
        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Laporan</h4>
        <p class="text-[11px] text-slate-500">Laporan ditujukan kepada Eselon 2 dan berkaitan dengan proses SPJ. Judul diambil dari kolom Hal / Judul di atas.</p>
        <div>
            <label class="{{ $lbl }}">Nomor Surat Tugas yang menjadi dasar</label>
            <input type="text" name="isi_payload[nomor_st]" value="{{ $p('nomor_st') }}" class="{{ $inp }}">
        </div>
        @foreach ([['pendahuluan', 'A. Pendahuluan'], ['kegiatan', 'B. Kegiatan yang Dilaksanakan'], ['hasil', 'C. Hasil yang Dicapai'], ['kesimpulan_saran', 'D. Kesimpulan dan Saran'], ['penutup', 'E. Penutup']] as [$k, $label])
            <div>
                <label class="{{ $lbl }}">{{ $label }}</label>
                <textarea name="isi_payload[{{ $k }}]" rows="3" class="{{ $inp }}">{{ $p($k) }}</textarea>
            </div>
        @endforeach
    </fieldset>

    {{-- Penandatangan & Tembusan (semua jenis) --}}
    <div class="{{ $sect }}">
        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Penandatangan &amp; Tembusan</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="{{ $lbl }}">Jabatan Penandatangan</label>
                <input type="text" name="isi_payload[jabatan_penandatangan]" value="{{ $p('jabatan_penandatangan') }}" placeholder="Contoh: Sekretaris Deputi Bidang Koordinasi ..." class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Nama Pejabat Penandatangan</label>
                <input type="text" name="isi_payload[nama_penandatangan]" value="{{ $p('nama_penandatangan') }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">NIP Pejabat (opsional)</label>
                <input type="text" name="isi_payload[nip_penandatangan]" value="{{ $p('nip_penandatangan') }}" class="{{ $inp }}">
            </div>
        </div>
        <div>
            <label class="{{ $lbl }}">Tembusan (satu per baris, tanpa kata "sebagai laporan")</label>
            <textarea name="tembusan" rows="2" class="{{ $inp }}">{{ $tembusanVal }}</textarea>
        </div>
    </div>
</div>
