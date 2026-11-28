<x-app-layout title="Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Disposisi</h2>
    </x-slot>

    @php
        $C = \Illuminate\Support\Carbon::class;

        // Parameter yang ikut di setiap link
        $param = array_filter([
            'tampil' => 'jadwal',
            'pegawai' => $pegawai->id,
            'dari' => $dari,
            'sampai' => $sampai,
            'status' => $f['status'] ?: null,
            'sifat' => $f['sifat'] ?: null,
            'jenis' => $f['jenis'] ?: null,
            'q' => $f['q'] ?: null,
        ]);
        $jumlahFilter = count(array_filter([$f['status'], $f['sifat'], $f['jenis'], $f['q']]));
        $jumlahLanjutan = count(array_filter([$f['status'], $f['sifat'], $f['jenis']]));
        $bisaTim = $daftar->count() > 1;

        $resetUrl = $jumlahFilter
            ? route('disposisi.index', ['pegawai' => $pegawai->id, 'tampil' => 'jadwal', 'mode' => $mode, 'dari' => $dari, 'sampai' => $sampai])
            : null;
    @endphp

    @include('disposisi._gaya')
    @include('disposisi._gaya-kalender')
    @include('disposisi._gaya-jadwal')

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">

        <x-disposisi.kartu-atas tampil="jadwal" :q="$f['q']" placeholder="Cari perihal, nomor, asal, tempat…"
            :lanjut="$jumlahLanjutan > 0" :reset-url="$resetUrl" :boleh-catat="$bolehCatat ?? false">

            <x-slot name="hidden">
                <input type="hidden" name="tampil" value="jadwal">
                <input type="hidden" name="mode" value="{{ $mode }}">
            </x-slot>

            <x-slot name="tambahan">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @if ($bisaTim)
                        <x-disposisi.pilih name="pegawai" label="Pegawai" id="j-pegawai">
                            @foreach ($daftar as $p)
                                <option value="{{ $p->id }}" @selected($p->id === $pegawai->id)>
                                    {{ $p->name }}{{ $p->unit?->kode ? ' · ' . $p->unit->kode : '' }}
                                </option>
                            @endforeach
                        </x-disposisi.pilih>
                    @endif

                    <x-disposisi.kolom name="dari" label="Dari tanggal" type="date" id="j-dari" :value="$dari" />

                    @if ($mode === 'agenda')
                        <x-disposisi.kolom name="sampai" label="Sampai tanggal" type="date" id="j-sampai"
                            :value="$sampai" />
                    @endif
                </div>

                <div class="kd-seg">
                    <a href="{{ route('disposisi.index', $param + ['mode' => 'agenda']) }}"
                        class="kd-btn-{{ $mode === 'agenda' ? 'primary' : 'soft' }} kd-kecil">Aktivitas</a>
                    @if ($bisaTim)
                        <a href="{{ route('disposisi.index', $param + ['mode' => 'matriks']) }}"
                            class="kd-btn-{{ $mode === 'matriks' ? 'primary' : 'soft' }} kd-kecil">Matriks tim</a>
                    @endif
                </div>
            </x-slot>

            {{-- Filter lanjutan --}}
            <x-disposisi.pilih name="status" label="Status" id="j-status">
                <option value="">Semua status</option>
                <option value="perlu" @selected($f['status'] === 'perlu')>Perlu tindakan</option>
                @foreach (\App\Models\Disposisi::STATUS as $k => $v)
                    <option value="{{ $k }}" @selected($f['status'] === $k)>{{ $v }}</option>
                @endforeach
            </x-disposisi.pilih>

            @if ($adaJenis)
                <x-disposisi.pilih name="jenis" label="Jenis dokumen" id="j-jenis">
                    <option value="">Semua jenis</option>
                    @foreach ($jenisList as $k => $v)
                        <option value="{{ $k }}" @selected($f['jenis'] === $k)>{{ $v }}</option>
                    @endforeach
                </x-disposisi.pilih>
            @endif

            <x-disposisi.pilih name="sifat" label="Sifat surat" id="j-sifat">
                <option value="">Semua sifat</option>
                @foreach (\App\Models\DisposisiSurat::SIFAT as $k => $v)
                    <option value="{{ $k }}" @selected($f['sifat'] === $k)>{{ $v }}</option>
                @endforeach
            </x-disposisi.pilih>

            <x-slot name="bawah">
                <div class="grid gap-5 p-3 sm:p-4">

                    {{-- ================= TAMPILAN AKTIVITAS ================= --}}
                    @if ($mode === 'agenda')

                        <div class="jp-orang">
                            <x-avatar :user="$pegawai" class="h-11 w-11 text-sm"
                                style="height:2.75rem;width:2.75rem;font-size:.9rem" />
                            <div>
                                <p class="jp-nama">{{ $pegawai->name }}</p>
                            </div>
                        </div>

                        <div id="db-root" data-hari-ini="{{ $hariIni }}" class="grid gap-4">

                            {{-- Baris atas: kalender + daftar kegiatan --}}
                            <div class="db-atas">

                                {{-- Kalender bulanan (dibuat oleh JS dari data-tgl kegiatan) --}}
                                <section class="db-panel" aria-label="Kalender">
                                    <div class="db-panel-kepala">
                                        <h3 class="db-panel-judul"><span id="db-bulan"></span> <span id="db-tahun"
                                                class="db-thn"></span></h3>
                                        <div class="db-kontrol">
                                            <button type="button" class="db-ikon-btn" id="db-prev"
                                                aria-label="Bulan sebelumnya">
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path
                                                        d="M12.7 5.3a1 1 0 010 1.4L9.4 10l3.3 3.3a1 1 0 11-1.4 1.4l-4-4a1 1 0 010-1.4l4-4a1 1 0 011.4 0z" />
                                                </svg>
                                            </button>
                                            <button type="button" class="db-ikon-btn" id="db-hariini"
                                                aria-label="Ke hari ini" title="Hari ini">
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <circle cx="10" cy="10" r="3" />
                                                </svg>
                                            </button>
                                            <button type="button" class="db-ikon-btn" id="db-next"
                                                aria-label="Bulan berikutnya">
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path
                                                        d="M7.3 14.7a1 1 0 010-1.4l3.3-3.3-3.3-3.3a1 1 0 011.4-1.4l4 4a1 1 0 010 1.4l-4 4a1 1 0 01-1.4 0z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="db-kal" id="db-kal"></div>
                                </section>

                                {{-- Daftar kegiatan --}}
                                <section class="db-panel" aria-label="Kegiatan">
                                    <div class="db-panel-kepala">
                                        <h3 class="db-panel-judul">Kegiatan <small id="db-jumlah">0</small></h3>
                                        <div class="db-kontrol">
                                            <select id="db-filter" class="db-pilih" aria-label="Tampilkan kegiatan">
                                                <option value="mendatang">Mendatang</option>
                                                <option value="riwayat">Riwayat</option>
                                                <option value="semua">Semua</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="jp-sub" id="db-info" style="display:none;margin:-.5rem 0 .75rem"></p>

                                    <div class="db-gulung" id="db-events">
                                        @foreach ($hari->concat($riwayat) as $h)
                                            @foreach ($h['kartu'] as $k)
                                                @include('disposisi._item-dash', ['k' => $k])
                                            @endforeach
                                        @endforeach
                                    </div>
                                    <div class="db-kosong" id="db-kosong" style="display:none">
                                        Tidak ada kegiatan untuk tampilan ini.
                                        @if ($jumlahFilter)
                                            Coba reset filter.
                                        @else
                                            Coba perlebar rentang tanggal.
                                        @endif
                                    </div>
                                </section>
                            </div>

                            {{-- Baris bawah: telat + belum terjadwal --}}
                            @if (count($telat) || count($belum))
                                <div class="db-bawah">
                                    <section class="db-panel db-panel-telat" aria-label="Telat">
                                        <div class="db-panel-kepala">
                                            <h3 class="db-panel-judul">Telat <small>{{ count($telat) }}</small></h3>
                                        </div>
                                        @if (count($telat))
                                            <div class="db-gulung db-pendek">
                                                @foreach ($telat as $k)
                                                    @include('disposisi._item-dash', ['k' => $k])
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="db-kosong">Tidak ada disposisi yang melewati batas waktu.</div>
                                        @endif
                                    </section>

                                    <section class="db-panel db-panel-belum" aria-label="Belum terjadwal">
                                        <div class="db-panel-kepala">
                                            <h3 class="db-panel-judul">Belum terjadwal <small>{{ count($belum) }}</small></h3>
                                        </div>
                                        @if (count($belum))
                                            <div class="db-gulung db-pendek">
                                                @foreach ($belum as $k)
                                                    @include('disposisi._item-dash', ['k' => $k])
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="db-kosong">Semua disposisi sudah punya tanggal.</div>
                                        @endif
                                    </section>
                                </div>
                            @endif
                        </div>

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
                                                    <a
                                                        href="{{ route('disposisi.index', ['pegawai' => $p->id, 'mode' => 'agenda'] + $param) }}">{{ $p->name }}</a>
                                                    <small>{{ $p->unit?->kode ?? $p->unit?->nama }}</small>
                                                </td>
                                                @foreach ($hariKolom as $h)
                                                    @php $c = $sel[$p->id][$h->toDateString()] ?? null; @endphp
                                                    <td class="{{ $h->isWeekend() ? 'jp-libur' : '' }}">
                                                        @if ($c)
                                                            <span
                                                                class="jp-sel {{ $c['telat'] ? 'jp-sel-merah' : ($c['terbuka'] === 0 ? 'jp-sel-abu' : ($c['terbuka'] >= 3 ? 'jp-sel-kuning' : 'jp-sel-hijau')) }}">{{ $c['n'] }}</span>
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
                                <span>Angka di sel = jumlah disposisi pada tanggal itu · kolom Aktif/Telat = total saat ini
                                    (tanpa filter)</span>
                            </div>
                        </div>
                        @if ($terpotong)
                            <p class="jp-sub">Hanya {{ $baris->count() }} pegawai pertama yang ditampilkan. Pilih pegawai
                                tertentu untuk melihat aktivitas lengkap.</p>
                        @endif
                    @endif
                </div>
            </x-slot>
        </x-disposisi.kartu-atas>

    </div>

    @if ($mode === 'agenda')
        <script>
            (function () {
                var root = document.getElementById('db-root');
                if (!root) return;

                var hariIni = root.dataset.hariIni;

                // Chip link zoom: buka tab baru, jangan ikut membuka popup detail.
                // Capture + stopPropagation supaya jalan sebelum handler popup di kartu.
                function bukaZoom(z) { window.open(z.dataset.href, '_blank', 'noopener,noreferrer'); }
                root.addEventListener('click', function (e) {
                    var z = e.target.closest('.db-zoom');
                    if (!z) return;
                    e.preventDefault();
                    e.stopPropagation();
                    bukaZoom(z);
                }, true);
                root.addEventListener('keydown', function (e) {
                    if ((e.key === 'Enter' || e.key === ' ') && e.target.classList.contains('db-zoom')) {
                        e.preventDefault();
                        e.stopPropagation();
                        bukaZoom(e.target);
                    }
                }, true);
                var items = Array.prototype.slice.call(root.querySelectorAll('#db-events .db-item'));
                // Tandai kegiatan hari ini (orange) dan jadikan tiap kartu pembuka popup
                Array.prototype.forEach.call(root.querySelectorAll('.db-item'), function (el) {
                    if (el.dataset.tgl === hariIni) el.classList.add('db-hari-ini');
                    var id = el.dataset.buka || el.dataset.surat || '';
                    if (!id && el.getAttribute('href')) {
                        try {
                            var u = new URL(el.href, location.origin);
                            id = u.searchParams.get('buka') || ((u.pathname.match(/surat\/(\d+)/) || [])[1] || '');
                        } catch (e) { }
                    }
                    if (id) el.dataset.buka = id;
                });
                var NAMA_HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

                var perTgl = {};
                items.forEach(function (el) {
                    (perTgl[el.dataset.tgl] = perTgl[el.dataset.tgl] || []).push(el);
                });

                var kini = new Date(hariIni + 'T00:00:00');
                var tampil = new Date(kini.getFullYear(), kini.getMonth(), 1);
                var dipilih = null;

                function iso(d) {
                    var m = String(d.getMonth() + 1).padStart(2, '0');
                    var t = String(d.getDate()).padStart(2, '0');
                    return d.getFullYear() + '-' + m + '-' + t;
                }

                function adaTelat(tgl) {
                    return (perTgl[tgl] || []).some(function (el) {
                        return el.querySelector('.db-pil-merah') && tgl < hariIni && el.querySelector('.db-pil-merah').textContent.trim() === 'Telat';
                    });
                }

                function gambarKalender() {
                    var kal = document.getElementById('db-kal');
                    kal.innerHTML = '';

                    NAMA_HARI.forEach(function (n, i) {
                        var h = document.createElement('div');
                        h.className = 'db-kal-hari' + (i === kini.getDay() && iso(tampil).slice(0, 7) === hariIni.slice(0, 7) ? ' db-kini' : '');
                        h.textContent = n;
                        kal.appendChild(h);
                    });

                    document.getElementById('db-bulan').textContent = tampil.toLocaleDateString('id-ID', { month: 'long' });
                    document.getElementById('db-tahun').textContent = tampil.getFullYear();

                    var mulai = new Date(tampil);
                    mulai.setDate(1 - tampil.getDay());
                    var jumlahHari = new Date(tampil.getFullYear(), tampil.getMonth() + 1, 0).getDate();
                    var minggu = Math.ceil((tampil.getDay() + jumlahHari) / 7);

                    for (var i = 0; i < minggu * 7; i++) {
                        var d = new Date(mulai.getFullYear(), mulai.getMonth(), mulai.getDate() + i);
                        var k = iso(d);
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.textContent = d.getDate();
                        b.dataset.tgl = k;
                        b.className = 'db-sel'
                            + (d.getMonth() !== tampil.getMonth() ? ' db-luar' : '')
                            + (k === hariIni ? ' db-ini' : '')
                            + (perTgl[k] ? ' db-ada' : '')
                            + (perTgl[k] && adaTelat(k) ? ' db-ada-telat' : '')
                            + (k === dipilih ? ' db-dipilih' : '');
                        b.setAttribute('aria-label', d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) + (perTgl[k] ? ', ' + perTgl[k].length + ' kegiatan' : ''));
                        b.setAttribute('aria-pressed', k === dipilih ? 'true' : 'false');
                        kal.appendChild(b);
                    }
                }

                function gambarDaftar() {
                    var mode = document.getElementById('db-filter').value;
                    var n = 0;

                    items.forEach(function (el) {
                        var t = el.dataset.tgl;
                        var tampilkan = dipilih
                            ? t === dipilih
                            : (mode === 'semua' || (mode === 'mendatang' && t >= hariIni) || (mode === 'riwayat' && t < hariIni));
                        el.style.display = tampilkan ? '' : 'none';
                        if (tampilkan) n++;
                    });

                    document.getElementById('db-jumlah').textContent = n;
                    document.getElementById('db-kosong').style.display = n ? 'none' : '';
                    document.getElementById('db-events').style.display = n ? '' : 'none';

                    var info = document.getElementById('db-info');
                    if (dipilih) {
                        var d = new Date(dipilih + 'T00:00:00');
                        info.innerHTML = 'Tanggal ' + d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + ' · <button type="button" class="db-lepas" id="db-lepas">tampilkan semua</button>';
                        info.style.display = '';
                        document.getElementById('db-lepas').addEventListener('click', function () { pilih(null); });
                    } else {
                        info.style.display = 'none';
                    }
                }

                function pilih(tgl) {
                    dipilih = tgl;
                    gambarKalender();
                    gambarDaftar();
                }

                document.getElementById('db-kal').addEventListener('click', function (e) {
                    var b = e.target.closest('.db-sel');
                    if (!b) return;
                    var k = b.dataset.tgl;
                    var d = new Date(k + 'T00:00:00');
                    if (d.getMonth() !== tampil.getMonth()) tampil = new Date(d.getFullYear(), d.getMonth(), 1);
                    pilih(dipilih === k ? null : k);
                });

                document.getElementById('db-prev').addEventListener('click', function () {
                    tampil = new Date(tampil.getFullYear(), tampil.getMonth() - 1, 1);
                    gambarKalender();
                });
                document.getElementById('db-next').addEventListener('click', function () {
                    tampil = new Date(tampil.getFullYear(), tampil.getMonth() + 1, 1);
                    gambarKalender();
                });
                document.getElementById('db-hariini').addEventListener('click', function () {
                    tampil = new Date(kini.getFullYear(), kini.getMonth(), 1);
                    pilih(null);
                });
                document.getElementById('db-filter').addEventListener('change', function () { pilih(null); });

                gambarKalender();
                gambarDaftar();
            })();
        </script>
    @endif

    @include('disposisi._popup', ['dari' => 'jadwal'])

</x-app-layout>