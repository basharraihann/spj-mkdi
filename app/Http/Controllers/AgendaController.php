<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Pegawai;
use App\Models\DokumenUpload;
use App\Models\SbmRate;
use App\Models\SbmFlatRate;
use App\Models\PengRiilRate;
use App\Models\KabupatenKota;
use App\Models\MakOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AgendaController extends Controller
{
    /**
     * Peta jenis uang harian (UH) ke pasangan kolom hari/rate di pivot agenda_pegawai.
     * Dipakai bersama oleh store() (default) dan pesertaStore() (simpan aktual).
     */
    private const UH_FIELD_MAP = [
        'uh_biasa' => ['hari' => 'hari_dinas_biasa', 'rate' => 'rate_dinas_biasa'],
        'uh_biasa_60' => ['hari' => 'hari_biasa_60', 'rate' => 'rate_biasa_60'],
        'uh_fullday' => ['hari' => 'hari_fullday', 'rate' => 'rate_fullday'],
        'uh_fullboard' => ['hari' => 'hari_fullboard', 'rate' => 'rate_fullboard'],
    ];

    /**
     * Daftar komponen biaya "simple" (satu nilai per peserta, di luar lumpsum)
     * yang bisa dipilih di chip "Komponen Biaya".
     */
    private const SIMPLE_KOMPONEN_FIELDS = [
        'tiket',
        'dukungan_transportasi',
        'transportasi_darat',
        'transportasi_lokal',
        'peng_riil',
        'hotel',
        'penginapan_30',
        'representatif',
        'belanja_bahan',
        'honor_narsum',
    ];

    /**
     * Nilai default kolom biaya pas peserta baru ter-attach ke agenda (baik dari
     * store() step 1, maupun dari update() kalau nambah peserta lewat edit).
     * Rinciannya baru diisi user di halaman Rincian Biaya.
     */
    private const DEFAULT_PIVOT_BIAYA = [
        'tiket' => null,
        'dukungan_transportasi' => null,
        'transportasi_darat' => null,
        'transportasi_lokal' => null,
        'peng_riil' => null,
        'peng_riil_mode' => 'manual',
        'peng_riil_rate_id' => null,
        'peng_riil_detail' => null,
        'hotel' => null,
        'penginapan_30' => null,
        'hari_dinas_biasa' => 0,
        'rate_dinas_biasa' => 0,
        'hari_biasa_60' => 0,
        'rate_biasa_60' => 0,
        'hari_fullday' => 0,
        'rate_fullday' => 0,
        'hari_fullboard' => 0,
        'rate_fullboard' => 0,
        'lumpsum' => 0,
        'representatif' => null,
        'belanja_bahan' => null,
        'honor_narsum' => null,
    ];

    public function index(Request $request)
    {
        $agendas = Agenda::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('uraian_kegiatan', 'like', '%' . $request->q . '%');
            })
            ->when($request->filled('tujuan'), function ($query) use ($request) {
                $query->where('tujuan', 'like', '%' . $request->tujuan . '%');
            })
            ->when($request->filled('bulan'), function ($query) use ($request) {
                $query->whereMonth('tanggal_mulai', $request->bulan);
            })
            ->with('pegawai')
            ->withCount('dokumenUploads')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalDokumenKategori = count(Agenda::KATEGORI_DOKUMEN);

        return view('agendas.index', compact('agendas', 'totalDokumenKategori'));
    }

    public function create()
    {
        $pegawaiList = Pegawai::orderBy('nama')->get();

        // Daftar provinsi buat dropdown "Tujuan" — sumbernya sama persis dengan
        // master rate SBM (sbm_rates), biar provinsi yg dipilih di sini otomatis
        // punya rate yg bisa dipatok nanti di halaman Rincian Biaya.
        $provinsiList = SbmRate::orderBy('provinsi')->pluck('provinsi');

        // Map provinsi -> list kab/kota, dikirim sekali sbg JSON buat dropdown
        // cascading di client (JS doang, gak perlu AJAX pas ganti provinsi).
        $kabKotaMap = KabupatenKota::orderBy('nama')->get()->groupBy('provinsi')
            ->map(fn($g) => $g->pluck('nama')->values());

        $makOptions = MakOption::orderBy('mak')->get();

        return view('agendas.create', compact('pegawaiList', 'provinsiList', 'kabKotaMap', 'makOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_st' => 'required|string',
            'nomor_st_karo' => 'nullable|string',
            'uraian_kegiatan' => 'required|string',
            'tujuan' => 'required|string',
            'kota_tujuan' => 'nullable|string',
            'alat_angkut' => 'nullable|in:darat,udara,laut,darat_udara',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'ppk_id' => 'nullable|exists:pegawai,id',
            'bendahara_id' => 'nullable|exists:pegawai,id',
            'penanggung_jawab_id' => 'nullable|exists:pegawai,id',
            'pic_id' => 'required|exists:pegawai,id',
            'pegawai_id' => 'required|array|min:1',
            'pegawai_id.*' => 'exists:pegawai,id',
        ], [
            'pegawai_id.required' => 'Pilih minimal satu peserta.',
        ]);

        // Field administrasi/anggaran (MAK, klasifikasi, dst) — dipakai bersama
        // PNS & Non-PNS, opsional, divalidasi & di-parse terpisah (lihat method-nya).
        $adminData = $this->validateAdministrasiFields($request, $validated['uraian_kegiatan']);

        // Catatan: komponen_biaya & jenis_uang_harian SENGAJA tidak divalidasi/diterima
        // di sini lagi. Pemilihan komponen sekarang dilakukan di halaman "Rincian Biaya"
        // (step 2, lihat pesertaStore()), bukan di step 1 ini.

        $pegawaiIds = $validated['pegawai_id'];
        unset($validated['pegawai_id']);

        // Nomor ST Kepala Biro wajib kalau salah satu peserta yang dipilih jabatannya
        // Kepala Biro (sama persis logic deteksi yg dipakai AgendaPdfController@generateSpd).
        $this->ensureNomorStKaroFilled($pegawaiIds, $validated['nomor_st_karo'] ?? null);

        // Nomor Memo PNS/Non PNS wajib sesuai status peserta yang dipilih.
        $this->ensureNomorMemoFilled($pegawaiIds, $adminData['nomor_memo_pns'] ?? null, $adminData['nomor_memo_non_pns'] ?? null);

        $agenda = Agenda::create(array_merge($validated, $adminData));

        // Attach peserta terpilih dengan biaya kosong dulu (default 0/null),
        // rinciannya baru diisi di halaman berikutnya (input biaya), sekalian
        // komponen biaya & rate-nya dipatok di sana.
        $syncData = [];
        foreach ($pegawaiIds as $pegawaiId) {
            $syncData[$pegawaiId] = self::DEFAULT_PIVOT_BIAYA;
        }
        $agenda->pegawai()->sync($syncData);

        return redirect()->route('agendas.peserta', $agenda)
            ->with('success', 'Agenda berhasil dibuat, lanjut pilih komponen & isi rincian biaya peserta.');
    }

    public function edit(Agenda $agenda)
    {
        $agenda->load('pegawai');

        $pegawaiList = Pegawai::orderBy('nama')->get();
        $ppkList = $pegawaiList;
        $bendaharaList = $pegawaiList;
        $pjList = $pegawaiList;
        $picList = $pegawaiList;

        $provinsiList = SbmRate::orderBy('provinsi')->pluck('provinsi');
        $kabKotaMap = KabupatenKota::orderBy('nama')->get()->groupBy('provinsi')
            ->map(fn($g) => $g->pluck('nama')->values());

        $makOptions = MakOption::orderBy('mak')->get();

        $selectedPesertaIds = $agenda->pegawai->pluck('id');
        $simpleFieldsCek = array_diff(self::SIMPLE_KOMPONEN_FIELDS, ['peng_riil']);
        $pesertaHasBiayaIds = $agenda->pegawai->filter(function ($p) use ($simpleFieldsCek) {
            // ...(isi tetap sama, tidak berubah)
        })->pluck('id');

        return view('agendas.edit', compact(
            'agenda',
            'pegawaiList',
            'ppkList',
            'bendaharaList',
            'pjList',
            'picList',
            'provinsiList',
            'kabKotaMap',
            'makOptions',
            'selectedPesertaIds',
            'pesertaHasBiayaIds'
        ));
    }

    public function update(Request $request, Agenda $agenda)
    {
        $validated = $request->validate([
            'nomor_st' => 'required|string',
            'nomor_st_karo' => 'nullable|string',
            'uraian_kegiatan' => 'required|string',
            'tujuan' => 'required|string',
            'kota_tujuan' => 'nullable|string',
            'alat_angkut' => 'nullable|in:darat,udara,laut,darat_udara',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'ppk_id' => 'nullable|exists:pegawai,id',
            'bendahara_id' => 'nullable|exists:pegawai,id',
            'penanggung_jawab_id' => 'nullable|exists:pegawai,id',
            'pic_id' => 'required|exists:pegawai,id',
            'pegawai_id' => 'required|array|min:1',
            'pegawai_id.*' => 'exists:pegawai,id',
        ], [
            'pegawai_id.required' => 'Pilih minimal satu peserta.',
        ]);

        // Field administrasi/anggaran (MAK, klasifikasi, dst) — sama kayak store().
        $adminData = $this->validateAdministrasiFields($request, $validated['uraian_kegiatan']);

        // komponen_biaya & jenis_uang_harian juga gak lagi diedit dari sini (lihat
        // catatan yg sama di store()) — tetap dipertahankan apa adanya di $agenda.

        $pegawaiIds = collect($validated['pegawai_id'])->map(fn($id) => (int) $id);
        unset($validated['pegawai_id']);

        $this->ensureNomorStKaroFilled($pegawaiIds->all(), $validated['nomor_st_karo'] ?? null);
        $this->ensureNomorMemoFilled($pegawaiIds->all(), $adminData['nomor_memo_pns'] ?? null, $adminData['nomor_memo_non_pns'] ?? null);

        $agenda->update(array_merge($validated, $adminData));

        // Peserta lama yg sudah ter-attach TIDAK disentuh pivot-nya di sini (biar
        // rincian biaya yg udah diisi gak ke-reset) — cuma yg beneran nambah/kurang
        // yg diproses. Ini sengaja gak pakai sync() polos, karena sync() akan nulis
        // ulang kolom pivot yg gak disebutkan jadi default/null.
        $currentPegawaiIds = $agenda->pegawai()->pluck('pegawai.id');
        $toDetach = $currentPegawaiIds->diff($pegawaiIds);
        $toAttach = $pegawaiIds->diff($currentPegawaiIds);

        if ($toDetach->isNotEmpty()) {
            $agenda->pegawai()->detach($toDetach);
        }

        foreach ($toAttach as $pegawaiId) {
            $agenda->pegawai()->attach($pegawaiId, self::DEFAULT_PIVOT_BIAYA);
        }

        if ($toAttach->isNotEmpty()) {
            return redirect()->route('agendas.peserta', $agenda)
                ->with('success', 'Agenda berhasil diperbarui. Lengkapi rincian biaya untuk peserta baru.');
        }

        return redirect()->route('agendas.show', $agenda)
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    public function show(Agenda $agenda)
    {
        $agenda->load(['pegawai', 'dokumenUploads']);
        $kategoriList = Agenda::KATEGORI_DOKUMEN;

        return view('agendas.show', compact('agenda', 'kategoriList'));
    }

    public function pesertaForm(Agenda $agenda)
    {
        $agenda->load('pegawai');

        // Hanya pegawai yang sudah dipilih sebagai peserta di step 1 (Buat Agenda)
        // yang ditampilkan di sini. Kalau kosong, artinya belum ada peserta terpilih.
        $pegawaiList = $agenda->pegawai;

        // Rate SBM sesuai provinsi tujuan agenda ini — dipakai buat patok rate UH
        // (readonly) & opsi "SBM" di toggle Peng. Riil. Null kalau provinsinya
        // belum ada di master data (blade & pesertaStore() sudah jaga2 utk ini).
        $sbmRate = SbmRate::forProvinsi($agenda->tujuan);
        $sbmFlat = SbmFlatRate::current();

        // Daftar rate transportasi buat popup Peng. Riil — dropdown-nya BEBAS
        // pilih tujuan (gak terikat Tujuan agenda), dikelompokkan per kategori
        // biar bisa dipisah jadi <optgroup> Provinsi / Jabodetabek di Blade.
        $pengRiilRates = PengRiilRate::orderBy('tujuan')->get()->groupBy('kategori');

        return view('agendas.peserta', compact('agenda', 'pegawaiList', 'sbmRate', 'sbmFlat', 'pengRiilRates'));
    }

    public function pesertaStore(Request $request, Agenda $agenda)
    {
        // Komponen biaya & jenis UH sekarang dipilih di halaman ini (chip di atas
        // tabel peserta), bukan lagi di step 1 — makanya divalidasi & disimpan
        // di sini, bukan dibaca dari $agenda->komponen_biaya lama.
        $validated = $request->validate([
            'komponen_biaya' => 'required|array|min:1',
            'komponen_biaya.*' => 'string',
            'jenis_uang_harian' => 'nullable|array',
            'jenis_uang_harian.*' => 'string',
        ], [
            'komponen_biaya.required' => 'Pilih minimal satu komponen biaya.',
        ]);

        // Jenis uang harian cuma relevan kalau komponen "lumpsum" dicentang.
        if (!in_array('lumpsum', $validated['komponen_biaya'])) {
            $validated['jenis_uang_harian'] = [];
        }

        $agenda->update([
            'komponen_biaya' => $validated['komponen_biaya'],
            'jenis_uang_harian' => $validated['jenis_uang_harian'] ?? [],
        ]);

        // Peserta sudah ditentukan di step 1 (Buat Agenda). Di sini kita hanya
        // meng-update rincian biaya untuk peserta yang sudah ter-attach itu,
        // bukan menerima ulang daftar pegawai_id dari form.
        $pegawaiIds = $agenda->pegawai->pluck('id');

        // Ambil hanya digit dari input (input sudah dibersihkan di JS sebelum submit,
        // ini jaga-jaga tambahan di server)
        $toInt = fn($v) => $v === null || $v === '' ? 0 : (int) preg_replace('/\D/', '', (string) $v);

        $komponenAktif = $validated['komponen_biaya'];
        $activeUhFields = in_array('lumpsum', $komponenAktif)
            ? array_intersect_key(self::UH_FIELD_MAP, array_flip($validated['jenis_uang_harian']))
            : [];

        // peng_riil diproses khusus (multi-entry, mode SBM/manual per entry), jadi
        // dikeluarkan dari daftar field "generic" yang tinggal disalin apa adanya
        // dari input.
        $genericSimpleFields = array_diff(
            array_intersect(self::SIMPLE_KOMPONEN_FIELDS, $komponenAktif),
            ['peng_riil']
        );
        $prosesPengRiil = in_array('peng_riil', $komponenAktif);

        // Rate UH dipatok dari SBM sesuai provinsi tujuan agenda ini. Rate yang
        // dikirim dari client (kalaupun ada) SENGAJA diabaikan di sini — sumber
        // kebenarannya cuma dari master sbm_rates/sbm_flat_rates, supaya gak
        // bisa dimanipulasi lewat request dan konsisten kalau provinsinya sama.
        $sbmRate = SbmRate::forProvinsi($agenda->tujuan);
        $sbmFlat = SbmFlatRate::current();

        $uhRates = [
            'uh_biasa' => (int) ($sbmRate->uh_biasa ?? 0),
            'uh_biasa_60' => (int) ($sbmRate->uh_biasa_60 ?? 0),
            'uh_fullday' => (int) ($sbmFlat->uh_fullday ?? 0),
            'uh_fullboard' => (int) ($sbmFlat->uh_fullboard ?? 0),
        ];

        $syncData = [];
        foreach ($pegawaiIds as $pegawaiId) {
            $rowData = [];

            foreach ($genericSimpleFields as $field) {
                $rowData[$field] = $request->input("$field.$pegawaiId");
            }

            if ($prosesPengRiil) {
                // ==== Peng. Riil multi-entry ====
                // Modal client kirim satu field peng_riil_detail[{pegawaiId}] berisi
                // JSON array entry [{mode, value, rate_id, keterangan}, ...]. Tiap
                // entry SBM dihitung ULANG di sini dari master peng_riil_rates
                // (nominal & keterangan dari client diabaikan, gak dipercaya begitu
                // saja) — entry manual dipercaya nilainya dari client (at cost,
                // sama seperti perilaku lama).
                $rawDetail = $request->input("peng_riil_detail.$pegawaiId");
                $entriesInput = [];

                if ($rawDetail) {
                    $decoded = json_decode($rawDetail, true);
                    if (is_array($decoded)) {
                        $entriesInput = $decoded;
                    }
                }

                $entries = [];
                $total = 0;

                foreach ($entriesInput as $entryInput) {
                    if (!is_array($entryInput)) {
                        continue;
                    }

                    $entryMode = in_array($entryInput['mode'] ?? null, ['sbm', 'manual'], true)
                        ? $entryInput['mode']
                        : 'manual';

                    if ($entryMode === 'sbm') {
                        $rate = PengRiilRate::find($entryInput['rate_id'] ?? null);

                        if (!$rate) {
                            // Tujuan gak dipilih / gak valid -> skip entry ini,
                            // jangan diam-diam kesimpen nilai gak tervalidasi.
                            continue;
                        }

                        $nilai = (int) $rate->rate_pp;
                        $keterangan = $rate->kategori === 'jabodetabek'
                            ? 'Transport Jakarta – ' . $rate->tujuan . ' PP'
                            : 'Transport Daerah (' . $rate->tujuan . ') PP';

                        $entries[] = [
                            'mode' => 'sbm',
                            'value' => $nilai,
                            'rate_id' => $rate->id,
                            'tujuan' => $rate->tujuan,
                            'keterangan' => $keterangan,
                        ];
                        $total += $nilai;
                    } else {
                        $nilai = $toInt($entryInput['value'] ?? null);
                        $keterangan = trim((string) ($entryInput['keterangan'] ?? ''));

                        if ($nilai === 0 && $keterangan === '') {
                            continue; // entry manual kosong total, gak usah disimpan
                        }

                        $entries[] = [
                            'mode' => 'manual',
                            'value' => $nilai,
                            'rate_id' => null,
                            'tujuan' => null,
                            'keterangan' => $keterangan,
                        ];
                        $total += $nilai;
                    }
                }

                $rowData['peng_riil'] = $total;
                $rowData['peng_riil_detail'] = $entries ? json_encode($entries) : null;

                // Kolom lama dipertahankan sebagai metadata ringkas dari entry
                // PERTAMA saja — hanya buat kompatibilitas kode/laporan lama yang
                // mungkin masih membaca peng_riil_mode/peng_riil_rate_id. Sumber
                // kebenaran biaya sekarang di peng_riil (total) & peng_riil_detail.
                $rowData['peng_riil_mode'] = $entries[0]['mode'] ?? 'manual';
                $rowData['peng_riil_rate_id'] = $entries[0]['rate_id'] ?? null;
            }

            $lumpsumTotal = 0;
            foreach ($activeUhFields as $uhKey => $f) {
                $hari = $toInt($request->input("{$f['hari']}.$pegawaiId"));
                $rate = $uhRates[$uhKey];

                $rowData[$f['hari']] = $hari;
                $rowData[$f['rate']] = $rate;
                $lumpsumTotal += $hari * $rate;
            }

            if (in_array('lumpsum', $komponenAktif)) {
                $rowData['lumpsum'] = $lumpsumTotal;
            }

            $syncData[$pegawaiId] = $rowData;
        }

        $agenda->pegawai()->sync($syncData);

        return redirect()->route('agendas.show', $agenda)
            ->with('success', 'Peserta & biaya berhasil disimpan.');
    }

    public function dokumenForm(Agenda $agenda)
    {
        $agenda->load('dokumenUploads');
        $kategoriList = Agenda::KATEGORI_DOKUMEN;

        return view('agendas.dokumen', compact('agenda', 'kategoriList'));
    }

    public function dokumenStore(Request $request, Agenda $agenda)
    {
        $validated = $request->validate([
            'kategori' => 'required|in:' . implode(',', array_keys(Agenda::KATEGORI_DOKUMEN)),
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $existing = DokumenUpload::where('agenda_id', $agenda->id)
            ->where('kategori', $validated['kategori'])
            ->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->file_path);
            $existing->delete();
        }

        $path = $request->file('file')->store('dokumen-spj', 'public');

        DokumenUpload::create([
            'agenda_id' => $agenda->id,
            'kategori' => $validated['kategori'],
            'nama_file' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
        ]);

        return redirect()->back()
            ->with('success', 'Dokumen berhasil diupload.');
    }

    /**
     * Upload beberapa kategori dokumen sekaligus dalam satu submit.
     * Dipakai oleh kartu "Dokumen Pendukung" di halaman show agenda,
     * biar user gak perlu submit satu-satu per kategori.
     * Input yang diharapkan: files[kategori_key] => UploadedFile (opsional per kategori).
     */
    public function dokumenStoreAll(Request $request, Agenda $agenda)
    {
        $kategoriValid = array_keys(Agenda::KATEGORI_DOKUMEN);

        $validated = $request->validate([
            'files' => 'nullable|array',
            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $tersimpan = 0;

        foreach ($request->file('files', []) as $kategori => $file) {
            if (!$file || !$file->isValid() || !in_array($kategori, $kategoriValid, true)) {
                continue;
            }

            $existing = DokumenUpload::where('agenda_id', $agenda->id)
                ->where('kategori', $kategori)
                ->first();

            if ($existing) {
                Storage::disk('public')->delete($existing->file_path);
                $existing->delete();
            }

            $path = $file->store('dokumen-spj', 'public');

            DokumenUpload::create([
                'agenda_id' => $agenda->id,
                'kategori' => $kategori,
                'nama_file' => $file->getClientOriginalName(),
                'file_path' => $path,
            ]);

            $tersimpan++;
        }

        $pesan = $tersimpan > 0
            ? "{$tersimpan} dokumen berhasil diupload."
            : 'Tidak ada file yang dipilih untuk diupload.';

        return redirect()->back()->with('success', $pesan);
    }

    public function dokumenDestroy(Agenda $agenda, DokumenUpload $dokumen)
    {
        Storage::disk('public')->delete($dokumen->file_path);
        $dokumen->delete();

        return redirect()->back()
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    // generateSpd(), generateNominatif(), validateMemoStatus() DIPINDAH ke
    // AgendaPdfController — jangan ditambahin lagi di sini, biar gak dobel.

    /**
     * Pecah string MAK "7458.ABR.006.075.EE.524119" jadi 4 komponen:
     * kode_giat (3 segmen pertama), kode_komponen, kode_akun_ap, kode_belanja.
     * Return null kalau formatnya gak sesuai (bukan 6 segmen).
     */
    private function parseMak(string $mak): ?array
    {
        $parts = explode('.', $mak);

        if (count($parts) !== 6) {
            return null;
        }

        return [
            'kode_giat' => implode('.', array_slice($parts, 0, 3)), // 7458.ABR.006
            'kode_komponen' => $parts[3],                                // 075
            'kode_akun_ap' => $parts[4],                                // EE
            'kode_belanja' => $parts[5],                                // 524119
        ];
    }

    /**
     * Field administrasi/anggaran yang dipakai bersama PNS & Non-PNS (dulu diisi
     * di halaman Memorandum tiap generate PDF per status, sekarang diisi sekali
     * di form Buat/Edit Agenda biar gak double pengisian). Semua opsional — boleh
     * dikosongin dulu pas bikin agenda, dilengkapi belakangan lewat edit.
     *
     * $uraianKegiatan dipakai buat nyusun otomatis uraian_memo_pns/non_pns —
     * gak ada input terpisah buat itu, biar gak diketik ulang.
     *
     * Dipakai bareng oleh store() & update().
     */
    /**
     * Nomor Memo PNS cuma wajib kalau ada peserta berstatus PNS di antara yang
     * dipilih; Nomor Memo Non PNS cuma wajib kalau ada peserta Non PNS. Kalau
     * dua-duanya ada, dua-duanya wajib. Sama kayak ensureNomorStKaroFilled(),
     * dicek manual karena aturannya tergantung data peserta, bukan field lain.
     */
    private function ensureNomorMemoFilled(array $pegawaiIds, ?string $nomorMemoPns, ?string $nomorMemoNonPns): void
    {
        $statusList = Pegawai::whereIn('id', $pegawaiIds)->pluck('status_kepegawaian');

        $adaPns = $statusList->contains('PNS');
        $adaNonPns = $statusList->contains('Non PNS');

        $errors = [];
        if ($adaPns && empty($nomorMemoPns)) {
            $errors['nomor_memo_pns'] = 'Nomor Memo (PNS) wajib diisi karena ada peserta PNS di agenda ini.';
        }
        if ($adaNonPns && empty($nomorMemoNonPns)) {
            $errors['nomor_memo_non_pns'] = 'Nomor Memo (Non PNS) wajib diisi karena ada peserta Non PNS di agenda ini.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Nomor ST Kepala Biro wajib diisi kalau salah satu peserta yang dipilih
     * jabatannya Kepala Biro — deteksinya sama persis kayak yang dipakai
     * AgendaPdfController@generateSpd (jabatan mengandung "kepala biro").
     * Divalidasi manual di sini (bukan lewat $request->validate() biasa) karena
     * aturannya tergantung data peserta yang dipilih, bukan cuma field lain.
     */
    private function ensureNomorStKaroFilled(array $pegawaiIds, ?string $nomorStKaro): void
    {
        $adaKaro = Pegawai::whereIn('id', $pegawaiIds)
            ->get()
            ->contains(fn($p) => str_contains(strtolower($p->jabatan ?? ''), 'kepala biro'));

        if ($adaKaro && empty($nomorStKaro)) {
            throw ValidationException::withMessages([
                'nomor_st_karo' => 'Nomor ST Kepala Biro wajib diisi karena ada peserta Kepala Biro di agenda ini.',
            ]);
        }
    }

    private function validateAdministrasiFields(Request $request, string $uraianKegiatan): array
    {
        $validated = $request->validate([
            // Wajib format 6 segmen dipisah titik: 7458.ABR.006.075.EE.524119
            'mak' => ['required', 'string', 'regex:/^[^.\s]+\.[^.\s]+\.[^.\s]+\.[^.\s]+\.[^.\s]+\.[^.\s]+$/'],

            'uraian_giat' => 'required|string',
            'uraian_komponen' => 'required|string',
            'uraian_akun_ap' => 'required|string',
            'uraian_belanja' => 'required|string',

            'petugas_verifikasi_id' => 'required|exists:pegawai,id',

            // Nomor memo diisi manual (nomor surat resmi, gak di-generate otomatis).
            // Beda per status karena biasanya emang beda nomor urut (mis. PNS 323,
            // Non PNS 324) — bukan berarti sama.
            // Wajib/enggaknya tergantung status peserta yang dipilih (PNS/Non PNS) —
            // dicek terpisah di ensureNomorMemoFilled(), bukan di sini.
            'nomor_memo_pns' => 'nullable|string',
            'nomor_memo_non_pns' => 'nullable|string',

            // Klasifikasi/Nilai %/Pagu/Pengajuan SENGAJA gak divalidasi/diminta lagi —
            // belum kepake buat memorandum sementara ini (kolomnya tetap ada di DB,
            // cuma gak diisi lewat form).
        ], [
            'mak.regex' => 'Format MAK harus 6 bagian dipisah titik, contoh: 7458.ABR.006.075.EE.524119',
        ]);

        // Kode giat/komponen/akun/belanja SELALU diturunkan dari MAK di server —
        // preview di form itu readonly & gak dikirim (gak ada atribut "name").
        if (!empty($validated['mak'])) {
            $kodeParts = $this->parseMak($validated['mak']);

            if (!$kodeParts) {
                throw ValidationException::withMessages([
                    'mak' => 'Format MAK tidak valid.',
                ]);
            }

            $validated = array_merge($validated, $kodeParts);
        }

        // Uraian memo bukan input manual — selalu disusun ulang dari uraian_kegiatan
        // tiap kali agenda disimpan, biar selalu sinkron kalau uraian_kegiatan diedit.
        $validated['uraian_memo_pns'] = 'Sehubungan dengan Perjalanan ASN ' . $uraianKegiatan;
        $validated['uraian_memo_non_pns'] = 'Sehubungan dengan Perjalanan NON ASN ' . $uraianKegiatan;

        return $validated;
    }

    // memorandumForm() & memorandumStore() DIHAPUS — halaman "Isi Data Memorandum"
    // gak dipakai lagi. Nomor Memo (PNS/Non-PNS) sekarang diisi manual di form
    // Buat/Edit Agenda (lihat partial administrasi-anggaran), Uraian Memo disusun
    // otomatis dari uraian_kegiatan (lihat validateAdministrasiFields() di atas).
    // memorandumPdf() di bawah TETAP ADA — itu yang beneran generate PDF-nya,
    // dipanggil langsung dari halaman show agenda.
    //
    // CATATAN: hapus juga 2 route ini dari routes/web.php kalau masih ada:
    //   - GET  agendas/{agenda}/memorandum/{status}        (agendas.memorandum)
    //   - POST agendas/{agenda}/memorandum/{status}         (agendas.memorandum.store)
    // Route agendas.memorandum.pdf (GET) TETAP DIPAKAI, tapi sekarang controller-nya
    // AgendaPdfController@memorandumPdf, bukan di sini.
    //
    // memorandumPdf(), generateRincianBiaya(), generatePengeluaranRiil(),
    // generateMergedPdf() SEMUA DIPINDAH ke AgendaPdfController — jangan
    // ditambahin lagi di sini, biar gak dobel.

    public function destroy(Agenda $agenda)
    {
        // Hapus dokumen fisik yang terkait biar tidak jadi file sampah
        foreach ($agenda->dokumenUploads as $dokumen) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        $agenda->delete();

        return redirect()->route('agendas.index')
            ->with('success', 'Agenda berhasil dihapus.');
    }
}