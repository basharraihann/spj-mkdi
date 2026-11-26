<x-app-layout title="Jadwal Pegawai">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kotak Tugas &amp; Kalender
        </h2>
    </x-slot>

    @php
        $C = \Illuminate\Support\Carbon::class;
        // Parameter yang ikut di setiap link
        $param = array_filter([
            'pegawai' => $pegawai->id,
            'dari' => $dari,
            'sampai' => $sampai,
            'status' => $f['status'] ?: null,
            'sifat' => $f['sifat'] ?: null,
            'jenis' => $f['jenis'] ?: null,
            'q' => $f['q'] ?: null,
        ]);
        $jumlahFilter = count(array_filter([$f['status'], $f['sifat'], $f['jenis'], $f['q']]));
        $bisaTim = $daftar->count() > 1;
    @endphp

    @include('disposisi._gaya')

    <style>
        .jp-wrap { max-width: 1100px; margin: 0 auto; padding: 1.5rem 1rem 3rem; display: grid; gap: 1.25rem; }
        .jp-atas { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
        .jp-judul-hal { font-size: 1rem; font-weight: 500; color: #374151; }
        .jp-sub { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }

        .jp-stat-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        @media (min-width: 768px) { .jp-stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .jp-filter { padding: 1rem; display: grid; gap: .75rem; }
        .jp-filter-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        @media (min-width: 768px) { .jp-filter-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .jp-filter-aksi { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; justify-content: space-between; }

        .jp-orang { display: flex; align-items: center; gap: .85rem; }
        .jp-avatar { height: 2.75rem; width: 2.75rem; border-radius: 999px; background: #eff6ff; color: #2563eb;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .jp-nama { font-size: 1.1rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
        .jp-ket { font-size: .8rem; color: #64748b; }

        /* Blok per tanggal */
        .jp-hari { overflow: hidden; }
        .jp-hari-kepala { display: flex; align-items: center; gap: .9rem; padding: .9rem 1.1rem; background: #f1f6fb; border-bottom: 1px solid #e8edf5; }
        .jp-hari-kepala.jp-hari-ini { background: #eaf2ff; box-shadow: inset 0 3px 0 #2563eb; }
        .jp-tgl { width: 3.2rem; height: 3.2rem; border-radius: .8rem; background: #fff; border: 1px solid #dbe4f0;
            display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.05; flex-shrink: 0; }
        .jp-tgl b { font-size: 1.15rem; color: #0f172a; font-variant-numeric: tabular-nums; }
        .jp-tgl span { font-size: .68rem; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .jp-hari-nama { font-weight: 700; color: #0f172a; }
        .jp-hari-tgl { font-size: .75rem; color: #64748b; }
        .jp-jml { margin-left: auto; font-size: .8rem; font-weight: 600; color: #475569; white-space: nowrap; }
        .jp-daftar { display: grid; }
        .jp-daftar > .jp-kartu + .jp-kartu { border-top: 1px solid #f1f5f9; }

        /* Seksi khusus (telat / belum terjadwal) */
        .jp-seksi-judul { display: flex; align-items: center; gap: .6rem; padding: .8rem 1.1rem; font-weight: 700; font-size: .9rem; }
        .jp-seksi-telat .jp-seksi-judul { background: #fef2f2; color: #b91c1c; border-bottom: 1px solid #fee2e2; }
        .jp-seksi-belum .jp-seksi-judul { background: #fffbeb; color: #92400e; border-bottom: 1px solid #fef3c7; }

        /* Kartu */
        .jp-kartu { position: relative; padding: .95rem 1.1rem .95rem 1.35rem; display: grid; gap: .4rem; cursor: pointer; transition: background .1s; }
        .jp-kartu:hover, .jp-kartu:focus-visible { background: #f8fbff; outline: none; }
        .jp-kartu::before { content: ""; position: absolute; left: 0; top: .6rem; bottom: .6rem; width: 4px; border-radius: 0 4px 4px 0; background: #94a3b8; }
        .jp-g-baru::before { background: #2563eb; }
        .jp-g-dibaca::before { background: #60a5fa; }
        .jp-g-diteruskan::before { background: #818cf8; }
        .jp-g-selesai::before { background: #22c55e; }
        .jp-g-telat::before { background: #ef4444; }
        .jp-badges { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
        .jp-chip-jenis { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
        .jp-chip-unit { background: #fae8ff; color: #a21caf; }
        .jp-judul { font-size: 1rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
        .jp-judul:hover { color: #1d4ed8; }
        .jp-baris { display: flex; flex-wrap: wrap; align-items: baseline; gap: .35rem; font-size: .8rem; color: #475569; }
        .jp-ik { height: .95rem; width: .95rem; color: #64748b; flex-shrink: 0; align-self: center; }
        .jp-tebal-biru { color: #1d4ed8; }
        .jp-merah { color: #b91c1c; font-weight: 600; }
        .jp-link { color: #1d4ed8; text-decoration: underline; word-break: break-all; }

        .jp-kosong { padding: 3.5rem 1.5rem; text-align: center; }
        .jp-kosong p:first-child { font-weight: 600; color: #1f2937; }
        .jp-kosong p:last-child { font-size: .85rem; color: #6b7280; margin-top: .25rem; }

        /* Matriks */
        .jp-scroll { overflow-x: auto; }
        .jp-matriks { border-collapse: separate; border-spacing: 0; width: 100%; font-size: .8rem; }
        .jp-matriks th { padding: .6rem .4rem; background: #f8fafc; color: #64748b; font-size: .68rem; font-weight: 600; text-align: center; border-bottom: 1px solid #e8edf5; white-space: nowrap; }
        .jp-matriks th.jp-libur, .jp-matriks td.jp-libur { background: #f8fafc; }
        .jp-matriks td { padding: .45rem .3rem; text-align: center; border-bottom: 1px solid #f1f5f9; }
        .jp-matriks .jp-nm { position: sticky; left: 0; z-index: 1; background: #fff; text-align: left; padding: .5rem .9rem; min-width: 11rem; border-right: 1px solid #f1f5f9; }
        .jp-matriks th.jp-nm { background: #f8fafc; }
        .jp-matriks .jp-nm a { font-weight: 600; color: #0f172a; }
        .jp-matriks .jp-nm a:hover { color: #1d4ed8; }
        .jp-matriks .jp-nm small { display: block; font-size: .68rem; color: #94a3b8; }
        .jp-sel { display: inline-flex; align-items: center; justify-content: center; min-width: 1.7rem; height: 1.7rem; padding: 0 .35rem;
            border-radius: .5rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .jp-sel-hijau { background: #dcfce7; color: #166534; }
        .jp-sel-kuning { background: #fef3c7; color: #92400e; }
        .jp-sel-merah { background: #fee2e2; color: #b91c1c; }
        .jp-sel-abu { background: #f1f5f9; color: #64748b; }
        .jp-legenda { display: flex; flex-wrap: wrap; gap: .9rem; font-size: .75rem; color: #64748b; padding: .8rem 1rem; }
        .jp-legenda span { display: inline-flex; align-items: center; gap: .35rem; }
        .jp-legenda i { display: inline-block; width: .8rem; height: .8rem; border-radius: .25rem; }
    </style>

    <div class="kd-wrap min-h-full">
        <div class="jp-wrap">

            {{-- Judul + tab --}}
            <div class="jp-atas">
                <div>
                    <p class="jp-judul-hal">Jadwal disposisi per pegawai</p>
                    <p class="jp-sub">Tanggal mengikuti jadwal kegiatan / batas waktu · klik kartu untuk membuka detail surat</p>
                </div>
                <nav class="kd-tabs">
                    <a href="{{ route('kalender.index', ['tab' => 'hari-ini']) }}" class="kd-tab">Disposisi Hari Ini</a>
                    <a href="{{ route('kalender.index', ['tab' => 'kalender']) }}" class="kd-tab">Kalender</a>
                    <a href="{{ route('kalender.jadwal') }}" class="kd-tab aktif">Jadwal Pegawai</a>
                </nav>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('kalender.jadwal') }}" class="kd-card jp-filter">
                <input type="hidden" name="tampil" value="{{ $tampil }}">
                <div class="jp-filter-grid">
                    @if ($bisaTim)
                        <div>
                            <label class="kd-label" for="j-pegawai">Pegawai</label>
                            <select id="j-pegawai" name="pegawai" class="kd-input" onchange="this.form.submit()">
                                @foreach ($daftar as $p)
                                    <option value="{{ $p->id }}" @selected($p->id === $pegawai->id)>
                                        {{ $p->name }}{{ $p->unit?->kode ? ' · ' . $p->unit->kode : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="kd-label" for="j-dari">Dari tanggal</label>
                        <input id="j-dari" type="date" name="dari" value="{{ $dari }}" class="kd-input">
                    </div>
                    @if ($tampil === 'agenda')
                        <div>
                            <label class="kd-label" for="j-sampai">Sampai tanggal</label>
                            <input id="j-sampai" type="date" name="sampai" value="{{ $sampai }}" class="kd-input">
                        </div>
                    @endif
                    <div>
                        <label class="kd-label" for="j-status">Status</label>
                        <select id="j-status" name="status" class="kd-input" onchange="this.form.submit()">
                            <option value="">Semua status</option>
                            <option value="perlu" @selected($f['status'] === 'perlu')>Perlu tindakan</option>
                            @foreach (\App\Models\Disposisi::STATUS as $k => $v)
                                <option value="{{ $k }}" @selected($f['status'] === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($adaJenis)
                        <div>
                            <label class="kd-label" for="j-jenis">Jenis dokumen</label>
                            <select id="j-jenis" name="jenis" class="kd-input" onchange="this.form.submit()">
                                <option value="">Semua jenis</option>
                                @foreach ($jenisList as $k => $v)
                                    <option value="{{ $k }}" @selected($f['jenis'] === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="kd-label" for="j-sifat">Sifat surat</label>
                        <select id="j-sifat" name="sifat" class="kd-input" onchange="this.form.submit()">
                            <option value="">Semua sifat</option>
                            @foreach (\App\Models\DisposisiSurat::SIFAT as $k => $v)
                                <option value="{{ $k }}" @selected($f['sifat'] === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="kd-label" for="j-q">Cari</label>
                        <input id="j-q" type="search" name="q" value="{{ $f['q'] }}" maxlength="100" class="kd-input" placeholder="Perihal, nomor, asal, tempat">
                    </div>
                </div>

                <div class="jp-filter-aksi">
                    <div class="kd-seg">
                        <a href="{{ route('kalender.jadwal', $param + ['tampil' => 'agenda']) }}" class="kd-btn-{{ $tampil === 'agenda' ? 'primary' : 'soft' }} kd-kecil">Agenda</a>
                        @if ($bisaTim)
                            <a href="{{ route('kalender.jadwal', $param + ['tampil' => 'matriks']) }}" class="kd-btn-{{ $tampil === 'matriks' ? 'primary' : 'soft' }} kd-kecil">Matriks tim</a>
                        @endif
                    </div>
                    <div style="display:flex; gap:.5rem; align-items:center">
                        @if ($jumlahFilter)
                            <a href="{{ route('kalender.jadwal', ['pegawai' => $pegawai->id, 'tampil' => $tampil, 'dari' => $dari, 'sampai' => $sampai]) }}" class="kd-btn-ghost kd-kecil">Reset filter</a>
                        @endif
                        <button class="kd-btn-primary kd-kecil">Terapkan</button>
                    </div>
                </div>
            </form>

            {{-- ================= TAMPILAN AGENDA ================= --}}
            @if ($tampil === 'agenda')

                <div class="jp-orang">
                    <div class="jp-avatar">
                        <svg style="height:1.3rem;width:1.3rem" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <div>
                        <p class="jp-nama">{{ $pegawai->name }}</p>
                        <p class="jp-ket">
                            Jadwal pegawai · {{ $ringkasan['mendatang'] }} mendatang · {{ $ringkasan['belum'] }} belum terjadwal
                            @if ($ringkasan['telat'] > 0)
                                · <b class="jp-merah">{{ $ringkasan['telat'] }} telat</b>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="jp-stat-grid">
                    @foreach ([
                        ['Mendatang', $ringkasan['mendatang'], '#2563eb'],
                        ['Telat', $ringkasan['telat'], '#dc2626'],
                        ['Belum terjadwal', $ringkasan['belum'], '#d97706'],
                        ['Selesai (dalam rentang)', $ringkasan['selesai'], '#16a34a'],
                    ] as [$label, $angka, $warna])
                        <div class="kd-card kd-stat">
                            <p>{{ $label }}</p>
                            <p style="color: {{ $warna }}">{{ $angka }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Telat --}}
                @if (count($telat))
                    <section class="kd-card jp-hari jp-seksi-telat">
                        <div class="jp-seksi-judul">Telat · {{ count($telat) }} disposisi melewati batas waktu</div>
                        <div class="jp-daftar">
                            @foreach ($telat as $k)
                                @include('kalender._kartu-jadwal', ['k' => $k])
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Per tanggal --}}
                @forelse ($hari as $h)
                    @php $c = $C::parse($h['tanggal']); @endphp
                    <section class="kd-card jp-hari">
                        <header class="jp-hari-kepala {{ $h['tanggal'] === $hariIni ? 'jp-hari-ini' : '' }}">
                            <div class="jp-tgl"><b>{{ $c->format('d') }}</b><span>{{ $c->translatedFormat('M') }}</span></div>
                            <div>
                                <p class="jp-hari-nama">
                                    {{ $c->translatedFormat('l') }}
                                    @if ($h['tanggal'] === $hariIni)
                                        <span class="kd-chip" style="background:#2563eb;color:#fff;margin-left:.35rem">Hari ini</span>
                                    @endif
                                </p>
                                <p class="jp-hari-tgl">{{ $c->translatedFormat('d F Y') }}</p>
                            </div>
                            <span class="jp-jml">{{ count($h['kartu']) }} kegiatan</span>
                        </header>
                        <div class="jp-daftar">
                            @foreach ($h['kartu'] as $k)
                                @include('kalender._kartu-jadwal', ['k' => $k])
                            @endforeach
                        </div>
                    </section>
                @empty
                    @if (!count($telat) && !count($belum))
                        <div class="kd-card jp-kosong">
                            <p>Tidak ada jadwal disposisi</p>
                            <p>{{ $jumlahFilter ? 'Coba reset filter, atau' : 'Coba' }} perlebar rentang tanggal.</p>
                        </div>
                    @endif
                @endforelse

                {{-- Belum terjadwal --}}
                @if (count($belum))
                    <section class="kd-card jp-hari jp-seksi-belum">
                        <div class="jp-seksi-judul">Belum terjadwal · {{ count($belum) }} disposisi tanpa tanggal kegiatan / batas waktu</div>
                        <div class="jp-daftar">
                            @foreach ($belum as $k)
                                @include('kalender._kartu-jadwal', ['k' => $k])
                            @endforeach
                        </div>
                    </section>
                @endif

            {{-- ================= TAMPILAN MATRIKS ================= --}}
            @else
                <div class="kd-card" style="overflow:hidden">
                    <div class="jp-scroll">
                        <table class="jp-matriks">
                            <thead>
                                <tr>
                                    <th class="jp-nm">Pegawai</th>
                                    @foreach ($hariKolom as $h)
                                        <th class="{{ $h->isWeekend() ? 'jp-libur' : '' }}" @if ($h->toDateString() === $hariIni) style="color:#2563eb" @endif>
                                            {{ $h->translatedFormat('D') }}<br>{{ $h->format('d/m') }}
                                        </th>
                                    @endforeach
                                    <th>Aktif</th>
                                    <th>Telat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($baris as $p)
                                    <tr>
                                        <td class="jp-nm">
                                            <a href="{{ route('kalender.jadwal', $param + ['pegawai' => $p->id, 'tampil' => 'agenda']) }}">{{ $p->name }}</a>
                                            <small>{{ $p->unit?->kode ?? $p->unit?->nama }}</small>
                                        </td>
                                        @foreach ($hariKolom as $h)
                                            @php $c = $sel[$p->id][$h->toDateString()] ?? null; @endphp
                                            <td class="{{ $h->isWeekend() ? 'jp-libur' : '' }}">
                                                @if ($c)
                                                    <span class="jp-sel {{ $c['telat'] ? 'jp-sel-merah' : ($c['terbuka'] === 0 ? 'jp-sel-abu' : ($c['terbuka'] >= 3 ? 'jp-sel-kuning' : 'jp-sel-hijau')) }}">{{ $c['n'] }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td><b>{{ $aktif[$p->id] ?? 0 }}</b></td>
                                        <td>
                                            @if (($telatTotal[$p->id] ?? 0) > 0)
                                                <span class="jp-sel jp-sel-merah">{{ $telatTotal[$p->id] }}</span>
                                            @else
                                                <span style="color:#cbd5e1">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="jp-legenda">
                        <span><i style="background:#dcfce7"></i> 1-2 tugas terbuka</span>
                        <span><i style="background:#fef3c7"></i> 3+ tugas terbuka (padat)</span>
                        <span><i style="background:#fee2e2"></i> ada yang telat</span>
                        <span><i style="background:#f1f5f9"></i> semua selesai</span>
                        <span>Angka di sel = jumlah disposisi pada tanggal itu · kolom Aktif/Telat = total saat ini (tanpa filter)</span>
                    </div>
                </div>
                @if ($terpotong)
                    <p class="jp-sub">Hanya {{ $baris->count() }} pegawai pertama yang ditampilkan. Pilih pegawai tertentu untuk melihat agenda lengkap.</p>
                @endif
            @endif

        </div>
    </div>

    <script>
        // Klik di mana saja pada kartu membuka detail; link Zoom/judul tetap berfungsi sendiri
        document.querySelectorAll('.jp-kartu[data-href]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                if (e.target.closest('a')) return;
                window.location.href = el.dataset.href;
            });
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.target.closest('a')) window.location.href = el.dataset.href;
            });
        });
    </script>

</x-app-layout>
