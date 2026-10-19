{{--
Dropdown "Pilih Surat Tugas" untuk form agenda (create & edit).
Variabel:
$suratTugasList : [['id' => 1, 'label' => '208/KA.00.04/2026 — ...'], ...]
$agenda : model Agenda (edit) atau null (create)
$isiPeserta : true (create) = centang peserta sesuai ST; false (edit) = peserta tidak disentuh,
karena rincian biaya peserta di agenda yang sudah jalan tidak boleh ke-reset.

Data disalin ke form saat dipilih (snapshot). Semua field tetap bisa diedit manual.
--}}
@php
    $suratTugasList = $suratTugasList ?? [];
    $selectedSt = old('surat_tugas_id', $agenda->surat_tugas_id ?? '');
    $isiPeserta = $isiPeserta ?? true;
@endphp

<div id="pilih-st-wrap" class="rounded-xl border border-blue-100 bg-blue-50/50 p-4"
    data-url="{{ route('surat-tugas.data', ['suratTugas' => '__ID__']) }}"
    data-isi-peserta="{{ $isiPeserta ? '1' : '0' }}">
    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Ambil dari Surat Tugas</label>
    <select name="surat_tugas_id" id="pilih-st-select"
        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
        <option value="">— Tanpa Surat Tugas (isi manual) —</option>
        @foreach ($suratTugasList as $st)
            <option value="{{ $st['id'] }}" @selected((string) $selectedSt === (string) $st['id'])>{{ $st['label'] }}</option>
        @endforeach
    </select>
    <p class="text-xs text-gray-400 mt-1">
        Memilih surat tugas mengisi nomor ST, uraian, tanggal, provinsi,
        kab/kota{{ $isiPeserta ? ', dan peserta' : '' }}.
        Setelah terisi, semua field masih bisa diubah. Alat angkut, MAK, dan anggaran tetap diisi manual.
    </p>
    <p id="pilih-st-status" class="hidden text-xs mt-2"></p>
    @if ($suratTugasList === [])
        <p class="text-xs text-amber-600 mt-1">Belum ada surat tugas bernomor yang tersedia.</p>
    @endif
</div>

<script>
    (function () {
        const wrap = document.getElementById('pilih-st-wrap');
        const select = document.getElementById('pilih-st-select');
        const statusEl = document.getElementById('pilih-st-status');
        if (!wrap || !select) return;

        const urlTemplate = wrap.dataset.url;
        const isiPeserta = wrap.dataset.isiPeserta === '1';

        function q(name) { return document.querySelector('[name="' + name + '"]'); }

        function fire(el, types) {
            (types || ['input', 'change']).forEach(function (t) {
                el.dispatchEvent(new Event(t, { bubbles: true }));
            });
        }

        function setText(name, value) {
            const el = q(name);
            if (!el || value === null || value === undefined) return;
            el.value = value;
            fire(el);
        }

        // Set nilai <select>, termasuk memperbarui kotak teks "searchable" kalau select ini sudah diubah
        // jadi input yang bisa diketik (class js-searchable). Return false kalau opsinya tidak ada.
        function setSelect(el, value) {
            if (!el || !value) return false;
            const opt = Array.from(el.options).find(function (o) { return o.value === value; });
            if (!opt) return false;
            el.value = value;
            fire(el, ['change']);
            const display = el.parentElement ? el.parentElement.querySelector('input[type="text"]') : null;
            if (display && el.dataset.searchEnhanced) display.value = opt.textContent.trim();
            return true;
        }

        function showStatus(lines, ok) {
            if (!statusEl) return;
            statusEl.innerHTML = '';
            lines.forEach(function (t) {
                const p = document.createElement('div');
                p.textContent = t;
                statusEl.appendChild(p);
            });
            statusEl.className = 'text-xs mt-2 ' + (ok ? 'text-blue-600' : 'text-amber-600');
            statusEl.classList.toggle('hidden', lines.length === 0);
        }

        function isiForm(d) {
            const pesan = [];
            const peringatan = [];

            setText('nomor_st', d.nomor);
            setText('uraian_kegiatan', d.uraian_kegiatan);
            setText('tanggal_mulai', d.tanggal_mulai);
            setText('tanggal_selesai', d.tanggal_selesai);

            // Provinsi (= field "tujuan" di agenda), lalu Kab/Kota yang opsinya dibangun ulang setelah provinsi berubah
            const prov = q('tujuan');
            if (d.tujuan) {
                if (setSelect(prov, d.tujuan)) {
                    const kota = q('kota_tujuan');
                    if (d.kota_tujuan && !setSelect(kota, d.kota_tujuan)) {
                        peringatan.push('Kab/kota "' + d.kota_tujuan + '" tidak ada di daftar agenda, pilih manual.');
                    }
                } else {
                    peringatan.push('Provinsi "' + d.tujuan + '" tidak ada di daftar tujuan agenda (master SBM), pilih manual.');
                }
            }

            if (isiPeserta) {
                const ids = (d.pegawai_ids || []).map(Number);
                const boxes = Array.from(document.querySelectorAll('input[name="pegawai_id[]"]'));
                let terisi = 0;
                boxes.forEach(function (cb) {
                    cb.checked = ids.includes(Number(cb.value));
                    if (cb.checked) terisi++;
                });
                if (typeof updatePesertaCount === 'function') updatePesertaCount();
                pesan.push(terisi + ' peserta dicentang dari surat tugas.');
                if (terisi < ids.length) {
                    peringatan.push((ids.length - terisi) + ' peserta ST tidak ditemukan di daftar pegawai.');
                }
                if (d.peserta_manual && d.peserta_manual.length) {
                    peringatan.push('Peserta yang diisi manual di ST tidak bisa dipindah otomatis: ' + d.peserta_manual.join(', ') + '. Tambahkan lewat Data Pegawai.');
                }
                const karoWrap = document.getElementById('nomor-st-karo-wrap');
                if (karoWrap && !karoWrap.classList.contains('hidden')) {
                    peringatan.push('Ada Kepala Biro di peserta: isi Nomor ST Kepala Biro secara manual.');
                }
            }

            showStatus(pesan.concat(peringatan), peringatan.length === 0);
        }

        select.addEventListener('change', async function () {
            const id = select.value;
            if (!id) { showStatus([], true); return; }

            const nomorSt = q('nomor_st');
            if (nomorSt && nomorSt.value && !confirm('Isian yang sudah ada akan diganti dengan data dari surat tugas. Lanjutkan?')) {
                select.value = '';
                return;
            }

            showStatus(['Mengambil data surat tugas...'], true);
            try {
                const res = await fetch(urlTemplate.replace('__ID__', encodeURIComponent(id)), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Surat tugas tidak ditemukan atau belum bernomor.');
                isiForm(await res.json());
            } catch (e) {
                showStatus([e.message || 'Gagal mengambil data surat tugas.'], false);
            }
        });
    })();
</script>