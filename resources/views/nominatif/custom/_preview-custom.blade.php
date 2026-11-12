{{--
Live preview "Daftar Nominatif" (meniru template PDF nominatif).
Dipasang di kolom kanan form custom: @include('nominatif._preview-custom')
PENTING: harus berada DI DALAM elemen yang punya x-data="customForm(...)" supaya bisa baca `rows`.

Orientasi kertas: default landscape (tabel 8 kolom). Kalau PDF-nya portrait, pasang
@include('nominatif._preview-custom', ['landscape' => false])
--}}
@php
    $landscape = $landscape ?? true;
    $pw = $landscape ? 1123 : 794; // lebar kertas @ 96dpi
    $ph = $landscape ? 794 : 1123; // tinggi minimum kertas
@endphp

{{-- x-data bersarang: tambah helper format; `rows`, sub(), ppn(), sum(), dst. diwarisi dari customForm --}}
<aside id="nom-preview" class="hidden xl:block xl:col-span-3 xl:sticky xl:top-4 self-start min-w-0" x-data="{
        f(n) { n = Math.round(Number(n) || 0); return n ? n.toLocaleString('en-US') : '-'; },
        harga(r) { return Number(String(r.harga == null ? '' : r.harga).replace(/\D/g, '')) || 0; },
        jml(r) { const j = +(parseFloat(r.jumlah) || 0).toFixed(2); return (j + ' ' + (r.satuan || '')).trim(); }
    }">

    <div class="flex items-center justify-between gap-3 mb-3 px-1">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Preview Nominatif</h3>
            <p class="text-[11px] text-gray-400 flex items-center gap-1.5">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                Update otomatis saat mengisi
            </p>
        </div>
    </div>

    <div class="bg-gradient-to-b from-gray-100 to-gray-200/70 rounded-2xl border border-gray-200/70 p-3"
        style="max-height: calc(100vh - 6.5rem); overflow-y: auto; scrollbar-gutter: stable;">
        <div id="nom-stage" class="w-full relative">
            <div id="nom-pages" style="width: {{ $pw }}px; transform-origin: top left;">

                <div class="pv-paper" style="min-height: {{ $ph }}px;">
                    <h4>DAFTAR NOMINATIF</h4>
                    <h4><span data-pv="judul"></span></h4>

                    <table class="pv-grid">
                        <thead>
                            <tr>
                                <th style="width:4%">No</th>
                                <th>URAIAN</th>
                                <th style="width:10%">HARGA</th>
                                <th style="width:9%">JUMLAH SATUAN</th>
                                <th style="width:10%">SUB TOTAL</th>
                                <th style="width:9%">PPH 22</th>
                                <th style="width:9%">PPH 23</th>
                                <th style="width:11%">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in rows" :key="row.k">
                                <tr>
                                    <td x-text="i + 1"></td>
                                    <td x-text="row.uraian || 'Uraian item...'" :class="row.uraian ? '' : 'pv-empty'">
                                    </td>
                                    <td class="r" x-text="f(harga(row))"></td>
                                    <td class="c" x-text="jml(row)"></td>
                                    <td class="r" x-text="f(sub(row))"></td>
                                    <td class="r" x-text="f(pph22(row))"></td>
                                    <td class="r" x-text="f(pph23(row))"></td>
                                    <td class="r b" x-text="f(total(row))"></td>
                                </tr>
                            </template>

                            <tr>
                                <td colspan="5" class="gray">PPN</td>
                                <td colspan="3" class="r" x-text="f(sum('ppn'))"></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="gray">PPH 22 &amp; PPH 23</td>
                                <td class="r" x-text="f(sum('pph22'))"></td>
                                <td class="r" x-text="f(sum('pph23'))"></td>
                                <td class="r" x-text="f(sum('pph22') + sum('pph23'))"></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="gray">TOTAL</td>
                                <td class="r b" x-text="f(sum('total'))"></td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="pv-ttd">
                        <tr>
                            <td>Setuju dibayar,<br>Pejabat Pembuat Komitmen</td>
                            <td>Lunas dibayar,<br>Bendahara Pengeluaran</td>
                            <td>Jakarta, <span data-pv="tanggal"></span><br>Penanggungjawab Kegiatan</td>
                        </tr>
                        <tr>
                            <td class="ruang" colspan="3"></td>
                        </tr>
                        <tr>
                            <td><span data-pv="ppk"></span><br><span data-pv-nip="ppk"></span></td>
                            <td><span data-pv="bendahara"></span><br><span data-pv-nip="bendahara"></span></td>
                            <td><span data-pv="pj"></span><br><span data-pv-nip="pj"></span></td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>
</aside>

<style>
    /* Kertas @ 96dpi, margin sama dengan @page di PDF (40px 45px) */
    #nom-preview .pv-paper {
        width:
            {{ $pw }}
            px;
        box-sizing: border-box;
        padding: 40px 45px;
        background: #fff;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px;
        line-height: normal;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .08), 0 10px 30px rgba(0, 0, 0, .15);
    }

    /* ---- disalin dari template PDF (class .grid diganti .pv-grid agar tidak bentrok dengan Tailwind) ---- */
    #nom-preview h4 {
        text-align: center;
        margin: 0;
        font-size: 13px;
        font-weight: bold;
    }

    #nom-preview table.pv-grid {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    #nom-preview table.pv-grid td,
    #nom-preview table.pv-grid th {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 13px;
        vertical-align: top;
    }

    #nom-preview table.pv-grid th {
        text-align: center;
        padding: 12px 6px;
    }

    #nom-preview .r {
        text-align: right;
    }

    #nom-preview .c {
        text-align: center;
    }

    #nom-preview .b {
        font-weight: bold;
    }

    #nom-preview .gray {
        background: #d9d9d9;
    }

    #nom-preview table.pv-ttd {
        width: 100%;
        margin-top: 25px;
        border-collapse: collapse;
    }

    #nom-preview table.pv-ttd td {
        width: 33%;
        vertical-align: top;
        padding: 0 6px;
        font-size: 13px;
    }

    #nom-preview .ruang {
        height: 70px;
    }

    #nom-preview .pv-empty {
        color: #9ca3af;
    }
</style>

<script>
    (function () {
        const root = document.getElementById('nom-preview');
        if (!root) return;
        const form = root.parentElement.querySelector('form');
        if (!form) return;

        const PW = @json($pw);
        const DOTS = '.....................';
        const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
            'September', 'Oktober', 'November', 'Desember'];

        function set(key, text) {
            root.querySelectorAll('[data-pv="' + key + '"]').forEach(function (el) {
                const kosong = !text;
                el.textContent = kosong ? DOTS : text;
                el.classList.toggle('pv-empty', kosong);
            });
        }

        function setNip(key, nip) {
            root.querySelectorAll('[data-pv-nip="' + key + '"]').forEach(function (el) {
                el.textContent = 'NIP. ' + (nip || '');
            });
        }

        function field(name) {
            const el = form.elements[name];
            return el ? el.value : '';
        }

        // Nama = teks option (nama_gelar ?? nama, sama dengan PDF). NIP dari data-nip kalau ada.
        function pegawai(name) {
            const el = form.elements[name];
            if (!el || !el.value) return { nama: '', nip: '' };
            const opt = el.selectedOptions && el.selectedOptions[0];
            if (!opt) return { nama: '', nip: '' };
            return { nama: opt.textContent.trim(), nip: opt.dataset.nip || '' };
        }

        // Sama dengan translatedFormat('j F Y')
        function tanggalIndo(iso) {
            const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
            if (!m) return '';
            return parseInt(m[3], 10) + ' ' + BULAN[parseInt(m[2], 10) - 1] + ' ' + m[1];
        }

        function render() {
            set('judul', (field('uraian_kegiatan') || '').trim());
            set('tanggal', tanggalIndo(field('tanggal')));
            [['ppk', 'ppk_id'], ['bendahara', 'bendahara_id'], ['pj', 'penanggung_jawab_id']].forEach(function (p) {
                const o = pegawai(p[1]);
                set(p[0], o.nama);
                setNip(p[0], o.nip);
            });
        }

        // ----- Skala kertas -> lebar kolom; tinggi mengikuti isi (baris item bertambah) -----
        const stage = document.getElementById('nom-stage');
        const pages = document.getElementById('nom-pages');
        function fit() {
            const w = stage.clientWidth;
            if (!w) return;
            const s = w / PW;
            pages.style.transform = 'scale(' + s + ')';
            stage.style.height = Math.ceil(pages.offsetHeight * s) + 'px';
        }
        if (window.ResizeObserver) {
            const ro = new ResizeObserver(fit);
            ro.observe(stage);
            ro.observe(pages); // tinggi berubah saat item ditambah/dihapus (Alpine)
        }
        window.addEventListener('resize', fit);

        form.addEventListener('input', render);
        form.addEventListener('change', render);
        render();
        fit();
    })();
</script>