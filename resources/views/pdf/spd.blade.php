<div id="spd-page">
    <style>
        #spd-page {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            line-height: 1.4;
        }

        #spd-page .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        #spd-page .header-table td {
            vertical-align: top;
            padding: 0;
        }

        #spd-page .kop-title {
            text-align: center;
            font-weight: bold;
        }

        #spd-page .kop-title .l1 {
            font-size: 13px;
        }

        #spd-page .kop-title .l2 {
            font-size: 13px;
        }

        #spd-page .top-right-table {
            border-collapse: collapse;
            margin-left: 140px;
        }

        #spd-page .top-right-table td {
            padding: 1px 0;
            font-size: 13px;
        }

        #spd-page .top-right-table .lbl {
            width: 85px;
        }

        #spd-page .top-right-table .colon {
            width: 10px;
        }

        #spd-page .judul {
            text-align: center;
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 18px;
        }

        #spd-page .judul .l1 {
            font-size: 13px;
        }

        #spd-page .judul .l2 {
            font-size: 13px;
        }

        #spd-page .garis-atas {
            border-top: 1.5px solid #000;
            margin-top: 3px;
        }

        #spd-page table.main {
            width: 100%;
            border-collapse: collapse;
        }

        #spd-page table.main>tbody>tr>td {
            padding: 9px 10px;
            vertical-align: top;
            border-bottom: 1px solid #000;
        }

        #spd-page table.main>tbody>tr:last-child>td {
            border-bottom: none;
        }

        #spd-page .col-no {
            width: 3%;
        }

        #spd-page .col-label {
            width: 50%;
        }

        #spd-page .col-colon {
            width: 2%;
        }

        #spd-page .col-value {
            width: 63%;
        }

        #spd-page .sub-table {
            width: 100%;
            border-collapse: collapse;
        }

        #spd-page .sub-table td {
            padding: 1px 0;
            border: none;
            vertical-align: top;
        }

        #spd-page .sub-abc {
            width: 16px;
        }

        #spd-page .pengikut-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        #spd-page .pengikut-table td {
            padding: 2px 4px;
            border: none;
        }

        #spd-page .pengikut-table .p-no {
            width: 4%;
        }

        #spd-page .pengikut-table .p-nama {
            width: 40%;
        }

        #spd-page .pengikut-table .p-tgl {
            width: 30%;
        }

        #spd-page .pengikut-table .p-ket {
            width: 26%;
        }

        #spd-page .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        #spd-page .ttd-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        #spd-page .ttd-info-table {
            border-collapse: collapse;
        }

        #spd-page .ttd-info-table td {
            border: none;
            padding: 1px 0;
            font-size: 13px;
            white-space: nowrap;
        }

        #spd-page .ttd-info-table .lbl {
            width: 100px;
        }

        #spd-page .ttd-info-table .colon {
            width: 10px;
        }
    </style>

    @php
        // Nomor ST: Kepala Biro pakai nomornya sendiri (dipatok di AgendaPdfController@generateSpd,
        // dikirim sbg $nomorSt) — peserta lain pakai nomor_st biasa. Fallback ke nomor_st
        // biasa kalau $nomorSt entah kenapa gak dikirim (jaga-jaga, bukan behavior normal).
        $nomorStTampil = $nomorSt ?? $agenda->nomor_st;

        $alatAngkutLabel = match ($agenda->alat_angkut ?? null) {
            'darat' => 'Angkutan Darat',
            'udara' => 'Angkutan Udara',
            'laut' => 'Angkutan Laut',
            'darat_udara' => 'Angkutan Darat dan Udara',
            default => '-',
        };
    @endphp

    <table class="header-table">
        <tr>
            <td style="width:50%">
                <div class="kop-title">
                    <div class="l1">KEMENTERIAN KOORDINATOR BIDANG PANGAN</div>
                    <div class="l2">REPUBLIK INDONESIA</div>
                </div>
            </td>
            <td style="width:50%">
                <table class="top-right-table">
                    <tr>
                        <td class="lbl">Lembar ke</td>
                        <td class="colon">:</td>
                        <td>{{ $lembarKe ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Nomor</td>
                        <td class="colon">:</td>
                        <td>{{ $nomorStTampil ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="judul">
        <div class="l1">SURAT PERJALANAN DINAS (SPD)</div>
        <div class="l2">DALAM NEGERI</div>
    </div>

    <div class="garis-atas"></div>

    <table class="main">
        <tr>
            <td class="col-no">1</td>
            <td class="col-label">
                Pejabat Pembuat Komitmen<br>
                {{ $ppk->unit_kerja ?? 'Biro Manajemen Kinerja, Data, dan Informasi' }}
            </td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $ppk->nama_gelar ?? $ppk->nama ?? '-' }}</td>
        </tr>

        <tr>
            <td class="col-no">2</td>
            <td class="col-label">Nama / NIP Pegawai yang melaksanakan perjalanan dinas</td>
            <td class="col-colon">:</td>
            <td class="col-value">
                {{ $pegawai->nama_gelar ?? $pegawai->nama }}<br>
                NIP&nbsp;&nbsp;{{ $pegawai->nip ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="col-no">3</td>
            <td class="col-label">
                <table class="sub-table">
                    <tr>
                        <td class="sub-abc">a</td>
                        <td>Pangkat dan Golongan</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">b</td>
                        <td>Jabatan / Instansi</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">c</td>
                        <td>Tingkat Biaya Perjalanan Dinas</td>
                    </tr>
                </table>
            </td>
            <td class="col-colon">:</td>
            <td class="col-value">
                <table class="sub-table">
                    <tr>
                        <td class="sub-abc">a</td>
                        <td>{{ $pegawai->golongan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">b</td>
                        <td>{{ $pegawai->jabatan }}</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">c</td>
                        <td>Tingkat: {{ $tingkatBiaya ?? '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="col-no">4</td>
            <td class="col-label">Maksud Perjalanan Dinas</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $agenda->uraian_kegiatan }}</td>
        </tr>

        <tr>
            <td class="col-no">5</td>
            <td class="col-label">Alat angkutan yang dipergunakan</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $alatAngkutLabel }}</td>
        </tr>

        <tr>
            <td class="col-no">6</td>
            <td class="col-label">
                <table class="sub-table">
                    <tr>
                        <td class="sub-abc">a.</td>
                        <td>Tempat berangkat</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">b.</td>
                        <td>Tempat tujuan</td>
                    </tr>
                </table>
            </td>
            <td class="col-colon">:</td>
            <td class="col-value">
                <table class="sub-table">
                    <tr>
                        <td class="sub-abc">a.</td>
                        <td>Jakarta</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">b.</td>
                        <td>
                            {{ $agenda->tujuan }}{{ $agenda->kota_tujuan ? ' - ' . $agenda->kota_tujuan : '' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="col-no">7</td>
            <td class="col-label">
                <table class="sub-table">
                    <tr>
                        <td class="sub-abc">a</td>
                        <td>Lamanya perjalanan dinas</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">b</td>
                        <td>Tanggal berangkat</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">c</td>
                        <td>Tanggal harus kembali</td>
                    </tr>
                </table>
            </td>
            <td class="col-colon">:</td>
            <td class="col-value">
                <table class="sub-table">
                    <tr>
                        <td class="sub-abc">a.</td>
                        <td>{{ $agenda->tanggal_mulai->diffInDays($agenda->tanggal_selesai) + 1 }} hari</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">b.</td>
                        <td>{{ $agenda->tanggal_mulai->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">c.</td>
                        <td>{{ $agenda->tanggal_selesai->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="col-no">8</td>
            <td colspan="3">
                Pengikut :
                <table class="pengikut-table">
                    <tr>
                        <td class="p-no"></td>
                        <td class="p-nama">Nama</td>
                        <td class="p-tgl">Tanggal Lahir</td>
                        <td class="p-ket">Keterangan</td>
                    </tr>
                    <tr>
                        <td class="p-no">1</td>
                        <td class="p-nama">&nbsp;</td>
                        <td class="p-tgl">:</td>
                        <td class="p-ket"></td>
                    </tr>
                    <tr>
                        <td class="p-no">2</td>
                        <td class="p-nama">&nbsp;</td>
                        <td class="p-tgl">:</td>
                        <td class="p-ket"></td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="col-no" style="border-bottom:none; padding-bottom:0;">9</td>
            <td colspan="3" style="border-bottom:none; padding-bottom:0;">Pembebanan anggaran :</td>
        </tr>
        <tr>
            <td class="col-no" style="padding-top:0;"></td>
            <td class="col-label" style="padding-top:0;">
                <table class="sub-table">
                    <tr>
                        <td class="sub-abc">a</td>
                        <td>Instansi</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">b</td>
                        <td>Akun</td>
                    </tr>
                </table>
            </td>
            <td class="col-colon" style="padding-top:0;">:</td>
            <td class="col-value" style="padding-top:0;">
                <table class="sub-table">
                    <tr>
                        <td class="sub-abc">a.</td>
                        <td>Kementerian Koordinator Bidang Pangan</td>
                    </tr>
                    <tr>
                        <td class="sub-abc">b.</td>
                        <td>{{ $agenda->kode_belanja ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="col-no" style="border-bottom: 1px solid #000;">10</td>
            <td class="col-label" style="border-bottom: 1px solid #000;">Keterangan lain - lain</td>
            <td class="col-colon" style="border-bottom: 1px solid #000;">:</td>
            <td class="col-value" style="border-bottom: 1px solid #000;">
                {{ $nomorStTampil ?? '-' }} tanggal
                {{ $agenda->tanggal_mulai->translatedFormat('d F Y') }}
            </td>
        </tr>
    </table>
    <table class="ttd-table">
        <tr>
            <td style="width:35%"></td>
            <td style="width:65%; padding-left: 220px;">
                <table class="ttd-info-table">
                    <tr>
                        <td class="lbl">Dikeluarkan di</td>
                        <td class="colon">:</td>
                        <td>Jakarta</td>
                    </tr>
                    <tr>
                        <td class="lbl">Pada Tanggal</td>
                        <td class="colon">:</td>
                        <td>{{ now()->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>

                <div style="margin-top:6px;">Pejabat Pembuat Komitmen</div>
                <div style="height:80px;"></div>
                <div style="text-decoration: underline; font-weight: bold;">
                    {{ $ppk->nama_gelar ?? $ppk->nama ?? '(...........................)' }}
                </div>
                <div>NIP {{ $ppk->nip ?? '-' }}</div>
            </td>
        </tr>
    </table>
</div>