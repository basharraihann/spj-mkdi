<div id="pengeluaran-riil-page">
    <style>
        #pengeluaran-riil-page {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
        }

        #pengeluaran-riil-page .kop {
            text-align: center;
            margin-bottom: 15px;
        }

        #pengeluaran-riil-page .kop .l1 {
            font-size: 13px;
            font-weight: bold;
        }

        #pengeluaran-riil-page .kop .l2 {
            font-size: 13px;
        }

        #pengeluaran-riil-page .kop .l3 {
            font-size: 13px;
            font-weight: bold;
            margin-top: 6px;
        }

        #pengeluaran-riil-page .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        #pengeluaran-riil-page .info-table td {
            padding: 1px 0;
        }

        #pengeluaran-riil-page .info-table .lbl {
            width: 100px;
        }

        #pengeluaran-riil-page .info-table .colon {
            width: 15px;
        }

        #pengeluaran-riil-page .isi {
            text-align: justify;
            margin-bottom: 8px;
        }

        #pengeluaran-riil-page table.rincian {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        #pengeluaran-riil-page table.rincian th,
        #pengeluaran-riil-page table.rincian td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        #pengeluaran-riil-page table.rincian th {
            text-align: center;
            background: #eee;
        }

        #pengeluaran-riil-page .no-col {
            width: 5%;
            text-align: center;
        }

        #pengeluaran-riil-page .jml-col {
            width: 20%;
            text-align: right;
        }

        #pengeluaran-riil-page .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        #pengeluaran-riil-page .ttd-table td {
            border: none;
            vertical-align: top;
            padding: 0 10px;
        }

        #pengeluaran-riil-page .ttd-table td:nth-child(2) {
            padding-left: 100px;
        }

        #pengeluaran-riil-page .sign-gap {
            height: 80px;
        }
    </style>

    @php
        // ==== Rincian Peng. Riil: satu baris per entry ====
        // peng_riil_detail disimpan sebagai JSON array [{mode, value, rate_id,
        // tujuan, keterangan}, ...] — lihat AgendaController@pesertaStore.
        // Tiap entry SBM sudah punya keterangan otomatis ("Transport Jakarta –
        // ... PP" / "Transport Daerah (...) PP"), entry manual pakai keterangan
        // yang diketik user di form.
        $pengRiilEntries = [];
        $pengRiilDetailRaw = $pivot->peng_riil_detail ?? null;

        if ($pengRiilDetailRaw) {
            $decoded = is_string($pengRiilDetailRaw) ? json_decode($pengRiilDetailRaw, true) : $pengRiilDetailRaw;
            if (is_array($decoded)) {
                $pengRiilEntries = $decoded;
            }
        }

        // Fallback data lama: peserta yang rincian biayanya diisi sebelum fitur
        // multi-entry ada (jadi peng_riil_detail masih kosong), tapi peng_riil
        // total-nya udah kesimpen — tampilkan sebagai satu baris generik biar
        // datanya tetap muncul, bukan hilang jadi Rp 0.
        if (empty($pengRiilEntries) && ($pivot->peng_riil ?? 0) > 0) {
            $pengRiilEntries = [
                [
                    'keterangan' => 'Transport ' . ($agenda->asal ?? 'Jakarta') . ' - ' . $agenda->tujuan . ' (PP)',
                    'value' => (int) $pivot->peng_riil,
                ]
            ];
        }

        $pengRiilTotal = collect($pengRiilEntries)->sum(fn($e) => (int) ($e['value'] ?? 0));
    @endphp

    <div class="kop">
        <div class="l1">KEMENTERIAN KOORDINATOR BIDANG PANGAN</div>
        <div class="l2">Gedung Graha Mandiri Jln. Imam Bonjol No. 61 Jakarta Pusat</div>
        <div class="l3">DAFTAR PENGELUARAN RIIL</div>
    </div>

    <p>Yang bertanda tangan di bawah ini :</p>

    <table class="info-table">
        <tr>
            <td class="lbl">Nama</td>
            <td class="colon">:</td>
            <td>{{ $pegawai->nama_gelar ?? $pegawai->nama }}</td>
        </tr>
        <tr>
            <td class="lbl">NIP</td>
            <td class="colon">:</td>
            <td>{{ $pegawai->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Jabatan</td>
            <td class="colon">:</td>
            <td>{{ $pegawai->jabatan ?? '-' }}</td>
        </tr>
    </table>

    <p class="isi">
        Berdasarkan Surat Perjalanan Dinas (SPD) Nomor {{ $agenda->nomor_st ?? '-' }} tanggal
        {{ $agenda->tanggal_mulai->translatedFormat('d F Y') }} dengan ini, kami menyatakan dengan sesungguhnya bahwa:
    </p>

    <p>1. Biaya transpor pegawai dan/atau biaya penginapan di bawah ini yang dapat diperoleh bukti-bukti pengeluarannya,
        meliputi :</p>

    <table class="rincian">
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th>Uraian</th>
                <th class="jml-col">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengRiilEntries as $i => $entry)
                <tr>
                    <td class="no-col">{{ $i + 1 }}</td>
                    <td>{{ $entry['keterangan'] ?: 'Peng. Riil (Manual)' }}</td>
                    <td class="jml-col">Rp {{ number_format($entry['value'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td class="no-col">1</td>
                    <td>Transport {{ $agenda->asal ?? 'Jakarta' }} - {{ $agenda->tujuan }} (PP)</td>
                    <td class="jml-col">Rp 0</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="2" style="text-align:right;"><strong>Jumlah</strong></td>
                <td class="jml-col"><strong>Rp {{ number_format($pengRiilTotal, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <p class="isi">
        2. Jumlah uang tersebut pada angka 1 di atas benar-benar dikeluarkan untuk pelaksanaan Perjalanan Dinas dimaksud
        dan apabila di kemudian hari terdapat kelebihan atas pembayaran, kami bersedia untuk menyetorkan kelebihan
        tersebut ke Kas Negara
    </p>

    <p>Demikian pernyataan ini kami buat dengan sebenarnya, untuk dipergunakan sebagaimana mestinya.</p>

    <table class="ttd-table">
        <tr>
            <td width="50%">
                Mengetahui/Menyetujui<br>
                Pejabat Pembuat Komitmen
                <div class="sign-gap"></div>
                <strong>{{ $ppk->nama_gelar ?? $ppk->nama ?? '(...........................)' }}</strong><br>
                NIP. {{ $ppk->nip ?? '-' }}
            </td>
            <td width="50%">
                Jakarta,&nbsp; {{ now()->translatedFormat('d F Y') }}<br>
                Pelaksana SPD,
                <div class="sign-gap"></div>
                <strong>{{ $pegawai->nama_gelar ?? $pegawai->nama }}</strong><br>
                NIP.&nbsp; {{ $pegawai->nip ?? '-' }}
            </td>
        </tr>
    </table>
</div>