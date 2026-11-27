<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\DisposisiSurat;
use App\Models\DisposisiFeedback;
use App\Models\User;
use App\Services\GoogleKalenderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Modul Disposisi (Persuratan).
 *
 * Alur:
 * 1. TU (TU Unit / TU Sekretariat) atau admin HANYA MENCATAT surat masuk dan memilih Eselon II tujuan.
 *    Sistem membuat disposisi pertama "TU -> Eselon II" tanpa instruksi (label: "Mohon disposisi").
 * 2. Eselon II membuka disposisi (otomatis "Sudah dibaca"), lalu:
 *    - Teruskan ke bawahan dengan instruksi, catatan, batas waktu, atau
 *    - Selesaikan dengan laporan tindak lanjut (+ lampiran opsional).
 * 3. Surat otomatis "Selesai" kalau semua cabang disposisinya tuntas.
 *
 * Tidak memakai global scope unit, akses dicek lewat DisposisiSurat::scopeTerlihatOleh().
 *
 * Halaman Disposisi punya tiga tab (?tampil=): daftar, kalender, jadwal.
 * - Daftar: hanya untuk pengelola (TU Unit, Eselon II, admin).
 * - Kalender, Jadwal Pegawai: semua akun yang terlibat.
 *
 * Hak akses:
 * - Catat surat, semua disposisi, teruskan, hapus: TU Unit, Eselon II (+ admin).
 * - Semua akun yang terlibat: lihat detail, beri feedback, selesaikan (lewat popup di tab Kalender).
 * - Penerima yang sudah menghubungkan Google Calendar otomatis dapat event + notifikasi.
 */
class DisposisiController extends Controller
{
    private const FILE_RULE = ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'];

    // ================================================================ halaman (3 tab)

    public function index(Request $request)
    {
        $user = $request->user();
        $bolehCatat = $this->bolehCatat($user);
        $pengelola = DisposisiSurat::bolehKelola($user);

        $tampil = (string) $request->query('tampil', $pengelola ? 'daftar' : 'kalender');
        if ($tampil === 'hari-ini') {
            $tampil = 'kalender';   // tab Hari Ini sudah dihapus; link lama masuk ke Kalender
        }
        if (!in_array($tampil, ['daftar', 'kalender', 'jadwal'], true)) {
            $tampil = 'daftar';
        }
        // Akun non-pengelola tidak punya tab Daftar
        if ($tampil === 'daftar' && !$pengelola) {
            return redirect()->route('disposisi.index', ['tampil' => 'kalender']);
        }

        if ($tampil === 'kalender') {
            $data = app(KalenderDisposisiController::class)->dataKalender($request);

            return view('disposisi.kalender', $data + [
                'tampil' => 'kalender',
                'bolehKelola' => $pengelola,
                'bolehCatat' => $bolehCatat,
            ]);
        }

        if ($tampil === 'jadwal') {
            $data = app(JadwalPegawaiController::class)->data($request);

            return view('disposisi.jadwal', $data);
        }

        // ---- tab Daftar ----
        $q = trim((string) $request->query('q', ''));
        $filter = (string) $request->query('status', '');

        $cariSurat = function ($w) use ($q) {
            $w->where('nomor_surat', 'like', "%{$q}%")
                ->orWhere('perihal', 'like', "%{$q}%")
                ->orWhere('asal_surat', 'like', "%{$q}%")
                ->orWhere('nomor_agenda', 'like', "%{$q}%");
        };

        $surat = DisposisiSurat::query()
            ->terlihatOleh($user)
            ->with(['unit', 'disposisi.kepada.unit'])
            ->when($q !== '', fn($x) => $x->where($cariSurat))
            ->when(in_array($filter, ['proses', 'selesai'], true), fn($x) => $x->where('status', $filter))
            ->latest('tanggal_terima')->latest('id')
            ->paginate(15)->withQueryString();

        return view('disposisi.index', compact('q', 'filter', 'surat', 'bolehCatat') + ['tampil' => 'daftar']);
    }

    // ================================================================ catat surat

    public function create(Request $request)
    {
        abort_unless($this->bolehCatat($request->user()), 403);

        return view('disposisi.create', [
            'es2' => $this->daftarEs2($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($this->bolehCatat($user), 403);

        $data = $request->validate([
            'nomor_agenda' => ['nullable', 'string', 'max:100'],
            'nomor_surat' => ['required', 'string', 'max:150'],
            'tanggal_surat' => ['nullable', 'date'],
            'tanggal_terima' => ['required', 'date'],
            'asal_surat' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string', 'max:2000'],
            'isi_ringkas' => ['nullable', 'string', 'max:5000'],
            'sifat' => ['required', Rule::in(array_keys(DisposisiSurat::SIFAT))],
            'lampiran' => ['nullable', 'string', 'max:150'],
            'tanggal_kegiatan' => ['nullable', 'date'],
            'waktu_mulai' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'waktu_selesai' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'tempat' => ['nullable', 'string', 'max:255'],
            'link_berkas' => ['nullable', 'url', 'starts_with:http://,https://', 'max:500'],
            'jenis' => ['nullable', Rule::in(array_keys(JadwalPegawaiController::JENIS))],
            'link_daring' => ['nullable', 'url', 'starts_with:http://,https://', 'max:500'],
        ] + $this->aturanCatat($user));

        [$surat, $baru] = DB::transaction(function () use ($request, $data, $user) {
            $surat = DisposisiSurat::create([
                'unit_id' => $user->unit_id,
                'nomor_agenda' => $data['nomor_agenda'] ?? null,
                'nomor_surat' => $data['nomor_surat'],
                'tanggal_surat' => $data['tanggal_surat'] ?? null,
                'tanggal_terima' => $data['tanggal_terima'],
                'asal_surat' => $data['asal_surat'],
                'perihal' => $data['perihal'],
                'isi_ringkas' => $data['isi_ringkas'] ?? null,
                'sifat' => $data['sifat'],
                'lampiran' => $data['lampiran'] ?? null,
                'tanggal_kegiatan' => $data['tanggal_kegiatan'] ?? null,
                'waktu_mulai' => !empty($data['tanggal_kegiatan']) ? ($data['waktu_mulai'] ?? null) : null,
                'waktu_selesai' => !empty($data['tanggal_kegiatan']) ? ($data['waktu_selesai'] ?? null) : null,
                'tempat' => $data['tempat'] ?? null,
                'status' => 'proses',
                'dibuat_oleh' => $user->id,
                'jenis' => $data['jenis'] ?? 'surat_masuk',
                'link_daring' => $data['link_daring'] ?? null,
                'link_berkas' => $data['link_berkas'] ?? null,
            ]);

            // Disposisi pertama TU -> Eselon II (tanpa instruksi)
            $baru = $this->kirim($surat, null, $user, $data);

            return [$surat, $baru];
        });

        $g = $this->sinkronGoogle($baru);

        return redirect()->route('disposisi.show', $surat)
            ->with('success', 'Surat dicatat dan diteruskan ke Eselon II untuk didisposisikan.' . $g);
    }

    // ================================================================ ubah surat

    public function edit(Request $request, DisposisiSurat $surat)
    {
        abort_unless($this->bolehUbah($request->user(), $surat), 403, 'Anda tidak berhak mengubah surat ini.');

        return view('disposisi.edit', compact('surat'));
    }

    /**
     * Ubah data surat / jadwal kegiatan. Penerima disposisi tidak diubah di sini.
     * Perubahan yang memengaruhi event (jadwal, tempat, link, judul, isi, sifat, jenis)
     * otomatis diteruskan ke Google Calendar: event unit dan event tiap penerima.
     */
    public function update(Request $request, DisposisiSurat $surat)
    {
        abort_unless($this->bolehUbah($request->user(), $surat), 403, 'Anda tidak berhak mengubah surat ini.');

        $data = $request->validate([
            'nomor_surat' => ['required', 'string', 'max:150'],
            'tanggal_surat' => ['nullable', 'date'],
            'tanggal_terima' => ['required', 'date'],
            'asal_surat' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string', 'max:2000'],
            'isi_ringkas' => ['nullable', 'string', 'max:5000'],
            'sifat' => ['required', Rule::in(array_keys(DisposisiSurat::SIFAT))],
            'lampiran' => ['nullable', 'string', 'max:150'],
            'tanggal_kegiatan' => ['nullable', 'date'],
            'waktu_mulai' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'waktu_selesai' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'tempat' => ['nullable', 'string', 'max:255'],
            'link_berkas' => ['nullable', 'url', 'starts_with:http://,https://', 'max:500'],
            'jenis' => ['nullable', Rule::in(array_keys(JadwalPegawaiController::JENIS))],
            'link_daring' => ['nullable', 'url', 'starts_with:http://,https://', 'max:500'],
        ]);

        // Jam disimpan "HH:MM:SS" (sama dengan isi database) supaya perubahan terdeteksi dengan benar
        $jam = fn($v) => $v ? substr((string) $v, 0, 5) . ':00' : null;
        $berjadwal = !empty($data['tanggal_kegiatan']);

        $isi = [
            'nomor_surat' => $data['nomor_surat'],
            'tanggal_surat' => $data['tanggal_surat'] ?? null,
            'tanggal_terima' => $data['tanggal_terima'],
            'asal_surat' => $data['asal_surat'],
            'perihal' => $data['perihal'],
            'isi_ringkas' => $data['isi_ringkas'] ?? null,
            'sifat' => $data['sifat'],
            'lampiran' => $data['lampiran'] ?? null,
            'tanggal_kegiatan' => $data['tanggal_kegiatan'] ?? null,
            'waktu_mulai' => $berjadwal ? $jam($data['waktu_mulai'] ?? null) : null,
            'waktu_selesai' => $berjadwal ? $jam($data['waktu_selesai'] ?? null) : null,
            'tempat' => $data['tempat'] ?? null,
            'jenis' => $data['jenis'] ?? 'surat_masuk',
            'link_daring' => $data['link_daring'] ?? null,
            'link_berkas' => $data['link_berkas'] ?? null,
        ];

        $berubah = DB::transaction(function () use ($surat, $isi) {
            $surat->fill($isi);
            $kunci = array_keys($surat->getDirty());
            $surat->save();

            return $kunci;
        });

        if (!$berubah) {
            return redirect()->route('disposisi.show', $surat)->with('success', 'Tidak ada perubahan.');
        }

        // Hanya kalau ada yang tampil di event Google
        $memengaruhiEvent = array_intersect($berubah, [
            'nomor_surat',
            'asal_surat',
            'perihal',
            'isi_ringkas',
            'sifat',
            'tanggal_kegiatan',
            'waktu_mulai',
            'waktu_selesai',
            'tempat',
            'jenis',
            'link_daring',
            'link_berkas',
        ]);

        $pesan = 'Perubahan surat disimpan.';
        if ($memengaruhiEvent && GoogleKalenderService::aktif()) {
            $g = GoogleKalenderService::perbaruiSurat($surat);

            $bagian = [];
            if ($g['unit']) {
                $bagian[] = 'event unit ' . $g['unit'];
            }
            if ($g['disposisi']) {
                $bagian[] = "{$g['disposisi']} event penerima diperbarui";
            }
            if ($bagian) {
                $pesan .= ' Google Calendar: ' . implode(', ', $bagian) . '.';
            }
            if ($g['gagal']) {
                $pesan .= " {$g['gagal']} event Google gagal diperbarui, coba simpan ulang atau cek log.";
            }
        }

        return redirect()->route('disposisi.show', $surat)->with('success', $pesan);
    }

    // ================================================================ detail

    public function show(Request $request, DisposisiSurat $surat)
    {
        $user = $request->user();
        abort_unless($surat->bolehDilihat($user), 403, 'Anda tidak terlibat dalam disposisi surat ini.');

        // Disposisi untuk saya yang belum dibaca -> tandai dibaca
        Disposisi::where('surat_id', $surat->id)
            ->where('kepada_user_id', $user->id)
            ->where('status', 'baru')
            ->update(['status' => 'dibaca', 'dibaca_at' => now(), 'updated_at' => now()]);

        $surat->load(['unit', 'pembuat', 'disposisi.dari.unit', 'disposisi.kepada.unit', 'feedback.user.unit']);
        $semua = $surat->disposisi;

        // Disposisi saya yang masih bisa ditindaklanjuti (bisa lebih dari satu)
        $milikSaya = $semua->filter(fn($d) => (int) $d->kepada_user_id === (int) $user->id && $d->status !== 'selesai')->values();

        return view('disposisi.show', [
            'surat' => $surat,
            'pohon' => $this->susunPohon($semua),
            'posisi' => $this->posisiSekarang($semua),
            'milikSaya' => $milikSaya,
            'penerima' => $milikSaya->isNotEmpty() && DisposisiSurat::bolehKelola($user) ? $this->daftarPenerima($user) : collect(),
            'bolehHapus' => $this->bolehHapus($user, $surat),
            'bolehUbah' => $this->bolehUbah($user, $surat),
            'bolehTeruskan' => DisposisiSurat::bolehKelola($user),
        ]);
    }

    // ================================================================ aksi

    /** Teruskan disposisi saya ke user lain. */
    public function teruskan(Request $request, Disposisi $disposisi)
    {
        $user = $request->user();
        abort_unless((int) $disposisi->kepada_user_id === (int) $user->id, 403);

        if ($disposisi->status === 'selesai') {
            return back()->with('error', 'Disposisi ini sudah selesai, tidak bisa diteruskan lagi.');
        }

        // Yang sudah diteruskan diubah lewat ubahTeruskan(), bukan dikirim ulang.
        if ($disposisi->status === 'diteruskan') {
            return back()->with('error', 'Disposisi ini sudah diteruskan. Gunakan tombol Ubah disposisi.');
        }

        $data = $request->validate(
            $this->aturanTujuan($user) + ['ikut' => ['nullable', 'boolean']],
            [
                'kepada.required' => 'Pilih minimal satu penerima.',
                'instruksi.required' => 'Pilih minimal satu instruksi.',
            ]
        );

        // Buang penerima yang sudah punya disposisi di surat ini (pengaman server)
        $sudah = Disposisi::where('surat_id', $disposisi->surat_id)
            ->pluck('kepada_user_id')
            ->map(fn($i) => (int) $i)
            ->all();
        $data['kepada'] = array_values(array_diff(array_map('intval', $data['kepada']), $sudah));

        if (empty($data['kepada'])) {
            return back()->withInput()->with('error', 'Semua penerima yang dipilih sudah mendapat disposisi untuk surat ini.');
        }

        $baru = DB::transaction(function () use ($request, $disposisi, $user, $data) {
            $baru = $this->kirim($disposisi->surat, $disposisi, $user, $data);
            $disposisi->update([
                'status' => 'diteruskan',
                'diteruskan_at' => now(),
                'dibaca_at' => $disposisi->dibaca_at ?? now(),
                'ikut' => $user->role === User::ROLE_ESELON2 && $request->boolean('ikut'),
            ]);
            $disposisi->surat->perbaruiStatus();

            return $baru;
        });

        $g = $this->sinkronGoogle($baru);

        return redirect()->route('disposisi.show', $disposisi->surat_id)
            ->with('success', 'Disposisi diteruskan ke ' . count($data['kepada']) . ' penerima.' . $g);
    }

    /**
     * Ubah disposisi yang sudah saya teruskan:
     * tambah atau tarik penerima, ganti instruksi/catatan/batas waktu, dan tanda "ikut".
     *
     * Penerima yang sudah meneruskan atau menyelesaikan tidak disentuh.
     */
    public function ubahTeruskan(Request $request, Disposisi $disposisi)
    {
        $user = $request->user();
        abort_unless((int) $disposisi->kepada_user_id === (int) $user->id, 403);

        if ($disposisi->status !== 'diteruskan') {
            return back()->with('error', 'Disposisi ini belum diteruskan, gunakan tombol Teruskan.');
        }

        // Penerima boleh kosong: mis. hanya mengubah tanda "ikut" atau menarik semua penerima.
        $data = $request->validate(
            array_merge($this->aturanTujuan($user), ['kepada' => ['nullable', 'array']]),
            ['instruksi.required' => 'Pilih minimal satu instruksi.']
        );

        $minta = collect($data['kepada'] ?? [])->map(fn($i) => (int) $i)->unique()->values();
        $surat = $disposisi->surat;
        $ikut = $user->role === User::ROLE_ESELON2 && $request->boolean('ikut');

        $baru = DB::transaction(function () use ($disposisi, $surat, $user, $data, $minta, $ikut) {
            $bisaUbah = Disposisi::where('parent_id', $disposisi->id)
                ->whereIn('status', ['baru', 'dibaca'])
                ->get();

            foreach ($bisaUbah as $a) {
                if ($minta->contains((int) $a->kepada_user_id)) {
                    // Tetap: perbarui isinya
                    $a->forceFill([
                        'instruksi' => $data['instruksi'] ?? [],
                        'catatan' => $data['catatan'] ?? null,
                        'batas_waktu' => $data['batas_waktu'] ?? null,
                    ])->save();
                } else {
                    // Dilepas: tarik kembali
                    $a->delete();
                }
            }

            // Tambah penerima baru, yang belum punya disposisi di surat ini
            $sudah = Disposisi::where('surat_id', $surat->id)
                ->pluck('kepada_user_id')
                ->map(fn($i) => (int) $i);
            $tambah = $minta->diff($sudah)->values()->all();

            $baru = $tambah
                ? $this->kirim($surat, $disposisi, $user, array_merge($data, ['kepada' => $tambah]))
                : null;

            // Tanda ikut + status disposisi saya
            $masihAda = Disposisi::where('parent_id', $disposisi->id)->exists();
            $disposisi->forceFill([
                'ikut' => $ikut,
                'status' => $masihAda ? 'diteruskan' : 'dibaca',
                'diteruskan_at' => $masihAda ? $disposisi->diteruskan_at : null,
            ])->save();

            $surat->perbaruiStatus();

            return $baru;
        });

        $g = $baru ? $this->sinkronGoogle($baru) : '';

        return redirect()->route('disposisi.show', $disposisi->surat_id)
            ->with('success', 'Disposisi diperbarui.' . $g);
    }

    /** Selesaikan disposisi saya dengan laporan tindak lanjut. */
    public function selesai(Request $request, Disposisi $disposisi)
    {
        $user = $request->user();
        abort_unless((int) $disposisi->kepada_user_id === (int) $user->id, 403);

        if ($disposisi->status === 'selesai') {
            return back()->with('error', 'Disposisi ini sudah selesai.');
        }

        $data = $request->validate([
            'laporan' => ['required', 'string', 'max:3000'],
            'file' => self::FILE_RULE,
        ]);

        DB::transaction(function () use ($request, $disposisi, $data) {
            $isi = [
                'status' => 'selesai',
                'selesai_at' => now(),
                'dibaca_at' => $disposisi->dibaca_at ?? now(),
                'laporan' => $data['laporan'],
            ];
            if ($request->hasFile('file')) {
                $f = $request->file('file');
                $isi['laporan_file_path'] = $f->store("disposisi/{$disposisi->surat_id}/laporan", 'local');
                $isi['laporan_file_name'] = $f->getClientOriginalName();
            }
            $disposisi->update($isi);
            $disposisi->surat->perbaruiStatus();
        });

        // Event di Google Calendar diberi tanda selesai, pengingat dimatikan
        GoogleKalenderService::sinkron($disposisi->fresh());

        return $this->kembali($request, $disposisi->surat_id, 'Disposisi ditandai selesai.');
    }

    /** Tarik kembali disposisi yang saya kirim, selama belum diteruskan atau diselesaikan penerima. */
    public function batalkan(Request $request, Disposisi $disposisi)
    {
        $user = $request->user();
        abort_unless((int) $disposisi->dari_user_id === (int) $user->id, 403);

        if (!in_array($disposisi->status, ['baru', 'dibaca'], true)) {
            return back()->with('error', 'Penerima sudah meneruskan atau menyelesaikan, disposisi tidak bisa dibatalkan.');
        }

        $nama = $disposisi->kepada?->name ?? 'penerima';
        $surat = $disposisi->surat;
        $induk = $disposisi->parent_id ? Disposisi::find($disposisi->parent_id) : null;

        DB::transaction(function () use ($disposisi, $induk, $surat) {
            $disposisi->delete();

            // Semua anak sudah ditarik: disposisi pengirim kembali terbuka
            if ($induk && !Disposisi::where('parent_id', $induk->id)->exists()) {
                $induk->update(['status' => 'dibaca', 'diteruskan_at' => null, 'ikut' => false]);
            }

            $surat->perbaruiStatus();
        });

        return back()->with('success', "Disposisi ke {$nama} dibatalkan.");
    }

    /**
     * Feedback / tanggapan disposisi. Boleh oleh siapa pun yang terlibat di surat itu.
     * Penerima bisa sekalian menandai disposisinya selesai (feedback jadi laporan).
     */
    public function feedback(Request $request, DisposisiSurat $surat)
    {
        $user = $request->user();
        abort_unless($surat->bolehDilihat($user), 403);

        $data = $request->validate([
            'isi' => ['required', 'string', 'max:2000'],
            'tandai_selesai' => ['nullable', 'boolean'],
        ]);

        // Disposisi milik saya di surat ini (yang masih terbuka diutamakan)
        $milik = Disposisi::where('surat_id', $surat->id)
            ->where('kepada_user_id', $user->id)
            ->orderByRaw("CASE WHEN status = 'selesai' THEN 1 ELSE 0 END")
            ->latest('id')
            ->first();

        $selesai = DB::transaction(function () use ($surat, $user, $data, $milik) {
            DisposisiFeedback::create([
                'surat_id' => $surat->id,
                'disposisi_id' => $milik?->id,
                'user_id' => $user->id,
                'isi' => $data['isi'],
            ]);

            if (!empty($data['tandai_selesai']) && $milik && $milik->status !== 'selesai') {
                $milik->update([
                    'status' => 'selesai',
                    'selesai_at' => now(),
                    'dibaca_at' => $milik->dibaca_at ?? now(),
                    'laporan' => $milik->laporan ?: $data['isi'],
                ]);
                $surat->perbaruiStatus();

                return true;
            }

            if ($milik && $milik->status === 'baru') {
                $milik->update(['status' => 'dibaca', 'dibaca_at' => now()]);
            }

            return false;
        });

        if ($selesai) {
            GoogleKalenderService::sinkron($milik->fresh());
        }

        return $this->kembali($request, $surat->id, $selesai ? 'Feedback terkirim dan disposisi ditandai selesai.' : 'Feedback terkirim.');
    }

    /** Hapus surat beserta alurnya (pencatat / admin, selama belum ditindaklanjuti). */
    public function destroy(Request $request, DisposisiSurat $surat)
    {
        abort_unless(
            $this->bolehHapus($request->user(), $surat),
            403,
            'Surat tidak bisa dihapus karena disposisinya sudah dibaca atau ditindaklanjuti.'
        );

        // Hapus semua event Google dulu (penerima dapat email pembatalan dari Google).
        // Yang gagal dicatat lengkap dengan id event-nya supaya bisa dihapus manual.
        $gagal = [];
        foreach ($surat->disposisi()->whereNotNull('google_event_id')->get() as $d) {
            if (!GoogleKalenderService::hapus($d)) {
                $gagal[] = $d->google_event_id;
            }
        }
        if (!GoogleKalenderService::hapusUnit($surat)) {
            $gagal[] = $surat->google_event_id_unit;
        }
        // Sudah ditangani di atas: kosongkan di memori supaya hook deleting tidak memanggil Google lagi
        $surat->google_event_id_unit = null;

        if ($gagal) {
            \Illuminate\Support\Facades\Log::warning(
                "Surat #{$surat->id} dihapus tapi event Google belum terhapus, hapus manual di kalender akun pusat: " . implode(', ', $gagal)
            );
        }

        $folder = "disposisi/{$surat->id}";
        $surat->delete();
        Storage::disk('local')->deleteDirectory($folder);

        $pesan = 'Surat dihapus.';
        if ($gagal) {
            $pesan .= ' Peringatan: ' . count($gagal) . ' event Google Calendar belum terhapus (id tercatat di log), penerimanya mungkin belum dapat email pembatalan.';
        }

        return redirect()->route('disposisi.index')->with('success', $pesan);
    }

    // ================================================================ berkas & PDF

    public function berkas(Request $request, DisposisiSurat $surat)
    {
        abort_unless($surat->bolehDilihat($request->user()), 403);
        abort_unless($surat->file_path && Storage::disk('local')->exists($surat->file_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->response($surat->file_path, $surat->file_name);
    }

    public function berkasLaporan(Request $request, Disposisi $disposisi)
    {
        abort_unless($disposisi->surat->bolehDilihat($request->user()), 403);
        abort_unless(
            $disposisi->laporan_file_path && Storage::disk('local')->exists($disposisi->laporan_file_path),
            404,
            'Berkas tidak ditemukan.'
        );

        return Storage::disk('local')->response($disposisi->laporan_file_path, $disposisi->laporan_file_name);
    }

    /** Lembar disposisi (PDF). */
    public function lembar(Request $request, DisposisiSurat $surat)
    {
        abort_unless($surat->bolehDilihat($request->user()), 403);

        $surat->load(['unit', 'pembuat', 'disposisi.dari.unit', 'disposisi.kepada.unit']);

        $pdf = Pdf::loadView('pdf.lembar-disposisi', [
            'surat' => $surat,
            'baris' => $this->ratakanPohon($this->susunPohon($surat->disposisi)),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Lembar-Disposisi-' . str_replace(['/', '\\'], '-', $surat->nomor_surat) . '.pdf');
    }

    // ================================================================ helper

    private function bolehCatat(User $user): bool
    {
        return DisposisiSurat::bolehKelola($user);
    }

    /** Kirim event ke Google Calendar penerima. Return keterangan singkat untuk pesan sukses. */
    private function sinkronGoogle(array $daftar): string
    {
        if (!GoogleKalenderService::aktif() || !$daftar) {
            return '';
        }

        $n = 0;
        foreach ($daftar as $d) {
            $n += GoogleKalenderService::sinkron($d) ? 1 : 0;
        }

        return $n ? " {$n} penerima mendapat notifikasi di Google Calendar." : '';
    }

    /** Setelah aksi dari popup (tab Kalender), kembali ke kalender; selain itu ke detail surat. */
    private function kembali(Request $request, int $suratId, string $pesan)
    {
        if ($request->input('_dari') === 'kalender') {
            return redirect()->route('disposisi.index', array_filter([
                'tampil' => 'kalender',
                'tanggal' => $request->input('_tanggal'),
            ]))->with('success', $pesan);
        }

        return redirect()->route('disposisi.show', $suratId)->with('success', $pesan);
    }

    /** Ubah surat: admin, pencatat, atau TU Unit di unit yang sama. Boleh kapan saja (jadwal bisa berubah setelah dibaca). */
    private function bolehUbah(User $user, DisposisiSurat $surat): bool
    {
        if (!$this->bolehCatat($user)) {
            return false;
        }

        return $user->isAdmin()
            || (int) $surat->dibuat_oleh === (int) $user->id
            || ($user->role === User::ROLE_TU_UNIT && $user->unit_id && (int) $user->unit_id === (int) $surat->unit_id);
    }

    private function bolehHapus(User $user, DisposisiSurat $surat): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $surat->dibuat_oleh === (int) $user->id
            && !$surat->disposisi()->where('status', '!=', 'baru')->exists();
    }

    /** Validasi saat MENERUSKAN disposisi: tujuan bebas + instruksi wajib. */
    private function aturanTujuan(User $user): array
    {
        return [
            'kepada' => ['required', 'array', 'min:1', 'max:30'],
            'kepada.*' => ['integer', 'distinct', Rule::exists('users', 'id'), Rule::notIn([$user->id])],
            'instruksi' => ['required', 'array', 'min:1'],
            'instruksi.*' => [Rule::in(array_keys(Disposisi::INSTRUKSI))],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'batas_waktu' => ['nullable', 'date'],
        ];
    }

    /** Validasi saat TU MENCATAT surat: tujuan hanya Eselon II, tanpa instruksi. */
    private function aturanCatat(User $user): array
    {
        return [
            'kepada' => ['required', 'array', 'min:1', 'max:5'],
            'kepada.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('role', User::ROLE_ESELON2),
                Rule::notIn([$user->id]),
            ],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'batas_waktu' => ['nullable', 'date'],
        ];
    }

    /**
     * Buat satu disposisi untuk tiap penerima.
     * Instruksi boleh kosong (disposisi pertama dari TU ke Eselon II).
     *
     * @return Disposisi[]
     */
    private function kirim(DisposisiSurat $surat, ?Disposisi $induk, User $dari, array $data): array
    {
        $baru = [];
        foreach (array_unique($data['kepada']) as $kepada) {
            $baru[] = Disposisi::create([
                'surat_id' => $surat->id,
                'parent_id' => $induk?->id,
                'dari_user_id' => $dari->id,
                'kepada_user_id' => (int) $kepada,
                'instruksi' => array_values($data['instruksi'] ?? []),
                'catatan' => $data['catatan'] ?? null,
                'batas_waktu' => $data['batas_waktu'] ?? null,
                'status' => 'baru',
            ]);
        }

        if ($surat->status === 'selesai') {
            $surat->update(['status' => 'proses', 'selesai_at' => null]);
        }

        return $baru;
    }

    /** Daftar Eselon II untuk tujuan surat; yang satu unit dengan pencatat ditaruh paling atas. */
    private function daftarEs2(User $user): Collection
    {
        return User::query()
            ->with('unit')
            ->where('role', User::ROLE_ESELON2)
            ->whereKeyNot($user->id)
            ->orderByRaw('CASE WHEN unit_id = ? THEN 0 ELSE 1 END', [$user->unit_id])
            ->orderBy('name')
            ->get(['id', 'name', 'unit_id'])
            ->map(fn($u) => [
                'id' => $u->id,
                'nama' => $u->name,
                'unit' => $u->unit?->nama ?? $u->unit?->kode ?? 'Tanpa unit',
                'satu_unit' => $u->unit_id === $user->unit_id,
            ]);
    }

    /** Penerima yang bisa dipilih saat meneruskan: semua user selain diri sendiri, dikelompokkan per unit. */
    private function daftarPenerima(User $user): Collection
    {
        return User::query()
            ->with('unit')
            ->whereKeyNot($user->id)
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                $status = strtoupper(trim((string) $u->status_kepegawaian));
                return [
                    'id' => $u->id,
                    'nama' => $u->name,
                    'role' => User::ROLE_LABELS[$u->role] ?? $u->role,
                    'unit' => $u->unit?->nama ?? $u->unit?->kode ?? 'Tanpa unit',
                    'status' => in_array($status, ['PNS', 'CPNS'], true) ? 'pns' : 'non_pns',
                    'user' => $u,
                ];
            })
            ->sortBy([['unit', 'asc'], ['nama', 'asc']])
            ->values();
    }

    /**
     * Pohon disposisi: [['d' => Disposisi, 'anak' => [...]], ...]
     */
    private function susunPohon(Collection $semua): array
    {
        $byParent = $semua->groupBy(fn($d) => $d->parent_id ?? 0);
        $bangun = function ($parentId) use (&$bangun, $byParent) {
            return ($byParent[$parentId] ?? collect())
                ->map(fn($d) => ['d' => $d, 'anak' => $bangun($d->id)])
                ->values()->all();
        };

        return $bangun(0);
    }

    /** Pohon -> daftar datar dengan level (untuk PDF). */
    private function ratakanPohon(array $pohon, int $level = 0): array
    {
        $hasil = [];
        foreach ($pohon as $n) {
            $hasil[] = ['d' => $n['d'], 'level' => $level];
            array_push($hasil, ...$this->ratakanPohon($n['anak'], $level + 1));
        }

        return $hasil;
    }

    /** Posisi surat sekarang = penerima yang disposisinya masih terbuka (belum diteruskan / selesai). */
    private function posisiSekarang(Collection $semua): Collection
    {
        return $semua->filter(fn($d) => in_array($d->status, Disposisi::TERBUKA, true))->values();
    }
}