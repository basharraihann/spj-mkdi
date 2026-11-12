{{--
Live preview memo (2 halaman A4: Memorandum + Lampiran Surat), meniru template PDF.
Dipasang di kolom kanan form: @include('memo._preview')
Membaca nilai langsung dari field form (tanpa request ke server).
Halaman 3 (Lampiran Memorandum) tidak ditampilkan karena datanya dari agenda.
--}}

<aside id="memo-preview" class="hidden xl:block xl:sticky xl:top-4 self-start min-w-0">
    <div class="flex items-center justify-between gap-3 mb-3 px-1">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Preview Memo</h3>
            <p class="text-[11px] text-gray-400 flex items-center gap-1.5">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                Update otomatis saat mengisi
            </p>
        </div>
    </div>

    <div class="bg-gradient-to-b from-gray-100 to-gray-200/70 rounded-2xl border border-gray-200/70 p-3"
        style="max-height: calc(100vh - 6.5rem); overflow-y: auto; scrollbar-gutter: stable;">
        <div id="pv-stage" class="w-full relative">
            <div id="pv-pages" style="width:794px; transform-origin: top left;">

                {{-- ================= HALAMAN 1: MEMORANDUM ================= --}}
                <div class="pv-paper" data-page="0">
                    <table class="header-table">
                        <tr>
                            <td style="text-align:left;">
                                <table style="margin:0 0 0 40px; border-collapse:collapse;">
                                    <tr>
                                        <td class="logo">
                                            <img src="{{ asset('images/logo.png') }}" class="logo" alt="">
                                        </td>
                                        <td>
                                            <div class="org-name">KEMENTERIAN KOORDINATOR BIDANG PANGAN<br>REPUBLIK
                                                INDONESIA</div>
                                            <div class="org-address">Graha Mandiri, Jl. Imam Bonjol No. 61, Jakarta
                                                Pusat 10310<br>Email: kemenkopangan@kemenkopangan.go.id</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <div class="title">MEMORANDUM</div>
                    <div class="subtitle">Nomor : <span data-pv="nomor"></span></div>

                    <table class="info">
                        <tr>
                            <td class="label">Yth</td>
                            <td class="colon">:</td>
                            <td>Kuasa Pengguna Anggaran</td>
                        </tr>
                        <tr>
                            <td class="label">Dari</td>
                            <td class="colon">:</td>
                            <td>Pejabat Pembuat Komitmen Biro Manajemen Kinerja, Data dan Informasi</td>
                        </tr>
                        <tr>
                            <td class="label">Hal</td>
                            <td class="colon">:</td>
                            <td><span data-pv="hal"></span></td>
                        </tr>
                        <tr>
                            <td class="label">Lampiran</td>
                            <td class="colon">:</td>
                            <td>1. Surat Keputusan<br>2. Rincian Biaya Kegiatan<br>3. Daftar Nominatif</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal</td>
                            <td class="colon">:</td>
                            <td><span data-pv="tanggal"></span></td>
                        </tr>
                    </table>

                    <div class="divider"></div>

                    <div class="isi">
                        Sehubungan dengan <span data-pv="uraian"></span>, dengan ini kami mengajukan permohonan dana
                        sebesar
                        <span data-pv="total"></span> yang dibebankan pada APBN satuan kerja
                        Kementerian Koordinator Bidang Pangan tahun anggaran <span data-pv="tahun"></span>
                        dengan MAK.<span data-pv="mak"></span>
                    </div>

                    <div class="isi-closing">Atas perhatian dan kerjasamanya, kami ucapkan terimakasih.</div>

                    <div class="ttd-block">
                        <div class="jabatan">Pejabat Pembuat Komitmen,</div>
                        <div class="signer-name"><span data-pv="ppk"></span></div>
                    </div>
                </div>

                {{-- ================= HALAMAN 2: LAMPIRAN SURAT ================= --}}
                <div class="pv-paper" data-page="1">
                    <div style="font-weight:bold; font-size:14px; margin-bottom:10px;">LAMPIRAN SURAT</div>

                    <table class="info" style="margin-bottom: 6px;">
                        <tr>
                            <td class="label">Nomor</td>
                            <td class="colon">:</td>
                            <td><span data-pv="nomor"></span></td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal</td>
                            <td class="colon">:</td>
                            <td><span data-pv="tanggal"></span></td>
                        </tr>
                    </table>

                    <table class="lampiran">
                        <thead>
                            <tr>
                                <th style="width:110px;">Kegiatan, Output, Komponen, Sub</th>
                                <th>Uraian</th>
                                <th style="width:90px;">Jumlah (Rp.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span data-pv="kode_giat" data-ph=""></span></td>
                                <td><span data-pv="uraian_giat" data-ph=""></span></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><span data-pv="kode_komponen" data-ph=""></span></td>
                                <td><span data-pv="uraian_komponen" data-ph=""></span></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><span data-pv="kode_akun" data-ph=""></span></td>
                                <td><span data-pv="uraian_akun" data-ph=""></span></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><span data-pv="kode_belanja" data-ph=""></span></td>
                                <td><span data-pv="uraian_belanja" data-ph=""></span></td>
                                <td class="text-right"><strong data-pv="total_lamp" data-ph="Rp."></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="terbilang">
                        <tr>
                            <td class="label">Terbilang</td>
                            <td class="colon">:</td>
                            <td class="value"><strong data-pv="terbilang" data-ph=""></strong>&nbsp;</td>
                        </tr>
                    </table>

                    <table class="ttd-row">
                        <tr>
                            <td>
                                <div class="ttd-jabatan">Bendahara Pengeluaran,</div>
                                <div class="ttd-name"><span data-pv="bendahara"></span></div>
                                <div class="ttd-nip" data-pv-nip="bendahara"></div>
                            </td>
                            <td>
                                <div class="ttd-jabatan">Penanggung Jawab Kegiatan,</div>
                                <div class="ttd-name"><span data-pv="pj"></span></div>
                                <div class="ttd-nip" data-pv-nip="pj"></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="ttd-jabatan">Mengetahui/menyetujui<br>Pejabat Pembuat Komitmen</div>
                                <div class="ttd-name"><span data-pv="ppk"></span></div>
                                <div class="ttd-nip" data-pv-nip="ppk"></div>
                            </td>
                            <td>
                                <div class="ttd-jabatan">Petugas Verifikasi,</div>
                                <div class="ttd-name"><span data-pv="verifikasi"></span></div>
                                <div class="ttd-nip" data-pv-nip="verifikasi"></div>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>
</aside>

<style>
    /* Kertas A4 @ 96dpi, margin sama dengan @page di PDF (40px 60px) */
    #memo-preview .pv-paper {
        width: 794px;
        height: 1123px;
        box-sizing: border-box;
        padding: 40px 60px;
        background: #fff;
        color: #000;
        font-family: Arial, sans-serif;
        font-size: 13px;
        line-height: normal;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .08), 0 10px 30px rgba(0, 0, 0, .15);
        margin-bottom: 24px;
        overflow: hidden;
    }

    #memo-preview .pv-paper:last-child {
        margin-bottom: 0;
    }

    #memo-preview .pv-paper table {
        border-collapse: separate;
        border-spacing: 0;
    }

    #memo-preview .pv-paper img {
        max-width: none;
        display: inline;
    }

    /* ---- disalin dari template PDF ---- */
    #memo-preview .header-table {
        width: 100%;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        margin-bottom: 14px;
    }

    #memo-preview .header-table td {
        vertical-align: middle;
    }

    #memo-preview .logo {
        width: 110px;
        height: auto;
        padding-right: 30px;
    }

    #memo-preview .org-name {
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        line-height: 1.3;
    }

    #memo-preview .org-address {
        text-align: center;
        font-size: 13px;
        line-height: 1.5;
    }

    #memo-preview .title {
        text-align: center;
        font-weight: bold;
        font-size: 13px;
        margin: 14px 0 2px;
    }

    #memo-preview .subtitle {
        text-align: center;
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 14px;
    }

    #memo-preview table.info {
        width: 100%;
        margin-bottom: 10px;
    }

    #memo-preview .divider {
        border-bottom: 2px solid #000;
        margin: 4px 0 14px;
    }

    #memo-preview table.info td {
        vertical-align: top;
        padding: 1px 0;
    }

    #memo-preview table.info td.label {
        width: 90px;
    }

    #memo-preview table.info td.colon {
        width: 12px;
    }

    #memo-preview .isi {
        text-align: justify;
        text-indent: 2em;
        margin: 14px 0 6px;
        line-height: 1.6;
    }

    #memo-preview .isi-closing {
        text-indent: 2em;
        margin: 0 0 20px;
        line-height: 1.6;
    }

    #memo-preview .ttd-block {
        margin-top: 10px;
        width: 280px;
        margin-left: auto;
        margin-right: 0;
        text-align: left;
        line-height: 1.5;
    }

    #memo-preview .ttd-block .signer-name {
        padding-top: 80px;
        font-weight: bold;
        text-decoration: underline;
    }

    #memo-preview table.lampiran {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }

    #memo-preview table.lampiran th,
    #memo-preview table.lampiran td {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 13px;
        vertical-align: top;
    }

    #memo-preview table.lampiran th {
        background: #f0f0f0;
        text-align: center;
    }

    #memo-preview table.terbilang {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    #memo-preview table.terbilang td {
        border: none;
        padding: 6px 8px;
        font-size: 12px;
    }

    #memo-preview table.terbilang td.label {
        width: 140px;
    }

    #memo-preview table.terbilang td.colon {
        width: 14px;
    }

    #memo-preview table.terbilang td.value {
        background: #e0e0e0;
    }

    #memo-preview .text-right {
        text-align: right;
    }

    #memo-preview .text-center {
        text-align: center;
    }

    #memo-preview .ttd-row {
        width: 100%;
        margin-top: 30px;
        margin-right: 20px;
    }

    #memo-preview .ttd-row td {
        width: 50%;
        text-align: center;
        vertical-align: top;
        padding-top: 10px;
    }

    #memo-preview .ttd-row tr:nth-child(2) td {
        padding-top: 50px;
    }

    #memo-preview .ttd-jabatan {
        min-height: 32px;
    }

    #memo-preview .ttd-nip {
        margin-top: 2px;
    }

    #memo-preview .ttd-name {
        margin-top: 80px;
        text-decoration: underline;
    }

    /* Field kosong: placeholder abu-abu */
    #memo-preview .pv-empty {
        color: #9ca3af;
        font-weight: normal;
    }
</style>

<script>
    (function () {
        const root = document.getElementById('memo-preview');
        const nomorEl = document.getElementById('nomor-memo-input');
        if (!root || !nomorEl) return;
        const form = nomorEl.closest('form');
        if (!form) return;

        const DOTS = '.....................';
        const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
            'September', 'Oktober', 'November', 'Desember'];
        const SATUAN = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        const SKALA = [[1e12, 'triliun'], [1e9, 'miliar'], [1e6, 'juta'], [1e3, 'ribu']];

        function terbilang(n) {
            if (n < 12) return SATUAN[n];
            if (n < 20) return terbilang(n - 10) + ' belas';
            if (n < 100) return terbilang(Math.floor(n / 10)) + ' puluh' + (n % 10 ? ' ' + terbilang(n % 10) : '');
            if (n < 200) return 'seratus' + (n - 100 ? ' ' + terbilang(n - 100) : '');
            if (n < 1000) return terbilang(Math.floor(n / 100)) + ' ratus' + (n % 100 ? ' ' + terbilang(n % 100) : '');
            for (const [nilai, nama] of SKALA) {
                if (n >= nilai) {
                    const depan = Math.floor(n / nilai);
                    const sisa = n % nilai;
                    const kata = (nilai === 1e3 && depan === 1) ? 'seribu' : terbilang(depan) + ' ' + nama;
                    return kata + (sisa ? ' ' + terbilang(sisa) : '');
                }
            }
            return '';
        }

        // Sama dengan PDF: tiap kata huruf besar di depan ("Satu Juta Lima Ratus Ribu Rupiah")
        function terbilangRupiah(n) {
            return (terbilang(n) + ' rupiah').split(' ').map(function (w) {
                return w.charAt(0).toUpperCase() + w.slice(1);
            }).join(' ');
        }

        function ribuan(n) {
            return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Isi semua elemen [data-pv=key]; kosong -> placeholder (data-ph kalau ada, kalau tidak titik-titik)
        function set(key, text) {
            root.querySelectorAll('[data-pv="' + key + '"]').forEach(function (el) {
                const ph = el.hasAttribute('data-ph') ? el.getAttribute('data-ph') : DOTS;
                const kosong = !text;
                el.textContent = kosong ? ph : text;
                el.classList.toggle('pv-empty', kosong && ph !== '');
            });
        }

        function setNip(key, nip) {
            root.querySelectorAll('[data-pv-nip="' + key + '"]').forEach(function (el) {
                el.textContent = nip ? 'NIP ' + nip : '';
            });
        }

        function field(name) {
            const el = form.elements[name];
            return el ? el.value : '';
        }

        // Nama & NIP pegawai dari <option>. Pakai data-nama / data-nip kalau ada, kalau tidak teks option.
        function pegawai(name) {
            const el = form.elements[name];
            if (!el || !el.value) return { nama: '', nip: '' };
            const opt = el.selectedOptions && el.selectedOptions[0];
            if (!opt) return { nama: '', nip: '' };
            return { nama: opt.dataset.nama || opt.textContent.trim(), nip: opt.dataset.nip || '' };
        }

        function tanggalIndo(iso) {
            const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
            if (!m) return '';
            return m[3] + ' ' + BULAN[parseInt(m[2], 10) - 1] + ' ' + m[1];
        }

        function val(id) {
            const el = document.getElementById(id);
            return el ? (el.value || '') : '';
        }
        function txt(id) {
            const el = document.getElementById(id);
            return el ? (el.textContent || '').trim() : '';
        }

        function render() {
            const nominal = parseInt((field('nominal') || '').replace(/\D/g, ''), 10) || 0;
            // sama dengan PDF: buang awalan "Sehubungan dengan"
            const uraian = (field('uraian_kegiatan') || '').trim().replace(/^sehubungan\s+dengan\s+/i, '');

            set('nomor', field('nomor_memo'));
            set('hal', txt('preview-hal'));
            set('tanggal', tanggalIndo(field('tanggal_memo')));
            set('tahun', String(new Date().getFullYear()));
            set('uraian', uraian);
            set('mak', field('mak'));
            set('total', nominal ? 'Rp' + ribuan(nominal) : '');
            set('total_lamp', nominal ? 'Rp.' + ribuan(nominal) : '');
            set('terbilang', nominal ? terbilangRupiah(nominal) : '');

            // Tabel lampiran: kode dari kolom "Kode ..." di form, uraian dari teks di bawahnya
            set('kode_giat', val('preview-kode-giat'));
            set('uraian_giat', txt('preview-uraian-giat'));
            set('kode_komponen', val('preview-kode-komponen'));
            set('uraian_komponen', txt('preview-uraian-komponen'));
            set('kode_akun', val('preview-kode-akun-ap'));
            set('uraian_akun', txt('preview-uraian-akun-ap'));
            set('kode_belanja', val('preview-kode-belanja'));
            set('uraian_belanja', txt('preview-uraian-belanja'));

            const orang = {
                ppk: pegawai('ppk_id'),
                bendahara: pegawai('bendahara_id'),
                pj: pegawai('penanggung_jawab_id'),
                verifikasi: pegawai('petugas_verifikasi_id')
            };
            Object.keys(orang).forEach(function (k) {
                set(k, orang[k].nama);
                setNip(k, orang[k].nip);
            });
            fit();
        }

        // ----- Skala kertas 794px -> lebar kolom -----
        const stage = document.getElementById('pv-stage');
        const pages = document.getElementById('pv-pages');
        function fit() {
            const w = stage.clientWidth;
            if (!w) return;
            // selalu penuh selebar kolom; tinggi mengikuti, scroll di dalam kotak
            const s = w / 794;
            pages.style.transform = 'scale(' + s + ')';
            stage.style.height = Math.ceil(pages.offsetHeight * s) + 'px';
        }
        if (window.ResizeObserver) new ResizeObserver(fit).observe(stage);
        window.addEventListener('resize', fit);


        // Event 'input' & 'change' dari semua field naik (bubble) sampai ke form
        form.addEventListener('input', render);
        form.addEventListener('change', render);
        render();
        fit();
    })();
</script>