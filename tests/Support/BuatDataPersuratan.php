<?php

namespace Tests\Support;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\Unit;
use App\Models\User;

/**
 * Data uji: 3 unit dengan akun lengkap per unit.
 *
 *  Unit A (Asdep 1 D2): tuA, es2A (Eselon 2), pegA1, pegA2 (pegawai)
 *  Unit B (Asdep 2 D2): tuB, es2B, pegB
 *  Unit D (Deputi 2)  : tuD, es1 (Eselon 1)
 *  Unit C (Asdep 3 D2): tuC saja, TANPA pejabat (untuk uji penolakan)
 *  admin              : tanpa unit
 */
trait BuatDataPersuratan
{
    protected Unit $unitA;
    protected Unit $unitB;
    protected Unit $unitC;
    protected Unit $unitD;

    protected User $admin;
    protected User $tuA;
    protected User $tuB;
    protected User $tuC;
    protected User $tuD;
    protected User $es1;
    protected User $es2A;
    protected User $es2B;
    protected User $pegA1;
    protected User $pegA2;
    protected User $pegB;

    protected function siapkanDataPersuratan(): void
    {
        // firstOrCreate: migrasi normalisasi role sudah membuat unit "Deputi 2"
        $this->unitA = Unit::firstOrCreate(['kode' => 'Asdep 1 D2'], ['nama' => 'Asisten Deputi A']);
        $this->unitB = Unit::firstOrCreate(['kode' => 'Asdep 2 D2'], ['nama' => 'Asisten Deputi B']);
        $this->unitC = Unit::firstOrCreate(['kode' => 'Asdep 3 D2'], ['nama' => 'Asisten Deputi C']);
        $this->unitD = Unit::firstOrCreate(['kode' => 'Deputi 2'], ['nama' => 'Deputi Bidang Koordinasi']);

        $this->admin = $this->buatUser('admin', 'admin');

        $this->tuA = $this->buatUser('tua', 'tu', $this->unitA);
        $this->tuB = $this->buatUser('tub', 'tu', $this->unitB);
        $this->tuC = $this->buatUser('tuc', 'tu', $this->unitC);
        $this->tuD = $this->buatUser('tud', 'tu', $this->unitD);

        $this->es1 = $this->buatUser('es1', 'eselon1', $this->unitD);
        $this->es2A = $this->buatUser('es2a', 'eselon2', $this->unitA);
        $this->es2B = $this->buatUser('es2b', 'eselon2', $this->unitB);

        $this->pegA1 = $this->buatUser('pega1', 'user', $this->unitA);
        $this->pegA2 = $this->buatUser('pega2', 'user', $this->unitA);
        $this->pegB = $this->buatUser('pegb', 'user', $this->unitB);
    }

    protected function buatUser(string $username, string $role, ?Unit $unit = null): User
    {
        return User::create([
            'name' => 'Nama ' . strtoupper($username),
            'username' => $username,
            'email' => $username . '@test.local',
            'password' => 'password',
            'role' => $role,
            'unit_id' => $unit?->id,
        ]);
    }

    /**
     * Surat masuk milik sebuah unit + disposisi awal (TU -> pejabat), sama seperti hasil form input.
     */
    protected function buatSuratMasuk(User $tu, Unit $unit, User $pejabat, string $perihal, string $statusDispo = 'baru'): SuratMasuk
    {
        $surat = SuratMasuk::create([
            'nomor_agenda' => 'TEST-' . uniqid(),
            'nomor_surat' => 'B-1/TEST/2026',
            'asal_surat' => 'Instansi Uji',
            'perihal' => $perihal,
            'tanggal_surat' => now()->toDateString(),
            'tanggal_diterima' => now()->toDateString(),
            'created_by_id' => $tu->id,
            'unit_id' => $unit->id,
            'jalur_tujuan' => $pejabat->role === 'eselon1' ? 'deputi' : 'eselon2',
            'status' => 'baru',
        ]);

        $this->buatDisposisi($surat, null, $tu, $pejabat, $statusDispo);

        return $surat;
    }

    protected function buatDisposisi(SuratMasuk $surat, ?Disposisi $parent, User $dari, User $ke, string $status = 'baru'): Disposisi
    {
        return Disposisi::create([
            'surat_masuk_id' => $surat->id,
            'parent_dispo_id' => $parent?->id,
            'pengirim_id' => $dari->id,
            'penerima_id' => $ke->id,
            'instruksi' => 'Mohon ditindaklanjuti.',
            'sifat' => 'biasa',
            'status' => $status,
        ]);
    }

    protected function akarDisposisi(SuratMasuk $surat): Disposisi
    {
        return Disposisi::where('surat_masuk_id', $surat->id)->whereNull('parent_dispo_id')->firstOrFail();
    }
}
