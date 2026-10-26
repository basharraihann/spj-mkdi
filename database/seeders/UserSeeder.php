<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    private const DOMAIN = 'kemenkopangan.go.id';

    private const PASSWORD = 'pangan2026';

    public function run(): void
    {
        // Username lama yang sudah diganti, hapus supaya tidak jadi duplikat
        User::where('username', 'tudeputi5d2')->delete();

        foreach ($this->data() as $row) {
            $unitId = null;

            if (!empty($row['unit'])) {
                $unitId = Unit::where('kode', $row['unit'])->value('id');

                if (!$unitId) {
                    $this->command?->warn("Unit '{$row['unit']}' tidak ditemukan untuk user {$row['username']}. Jalankan UnitSeeder dulu.");
                }
            }

            User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name'],
                    'email' => $row['email'] ?? $row['username'] . '@' . self::DOMAIN,
                    'role' => $row['role'],
                    'unit_id' => $unitId,
                    'password' => Hash::make(self::PASSWORD),
                    'pegawai_id' => null,
                ]
            );
        }
    }

    /**
     * 'unit' berisi KODE unit (kolom `kode` di UnitSeeder).
     * Email kosong => dibuat otomatis: username@kemenkopangan.go.id
     * Aturan: asdepNd2 / tuasdepNd2 / pegNasdepNd2 selalu berpasangan dengan unit "Asdep N D2".
     * Role 'user' = Pegawai Pelaksana (1 pegawai = 1 unit).
     */
    private function data(): array
    {
        return [
            // Admin
            ['name' => 'Administrator System', 'username' => 'admin', 'email' => 'admin@kemenkopangan.go.id', 'role' => 'admin'],
            ['name' => 'Bashar Raihan', 'username' => 'bashar', 'email' => 'basharrihann@gmail.com', 'role' => 'admin'],

            // TU
            ['name' => 'Yasser', 'username' => 'tudeputi2', 'role' => 'tu', 'unit' => 'Deputi 2'],
            ['name' => 'TU Sekretariat D2', 'username' => 'tusesdepd2', 'role' => 'tu', 'unit' => 'Sekretariat D2'],
            ['name' => 'TU Asdep 1 D2', 'username' => 'tuasdep1d2', 'role' => 'tu', 'unit' => 'Asdep 1 D2'],
            ['name' => 'TU Asdep 2 D2', 'username' => 'tuasdep2d2', 'role' => 'tu', 'unit' => 'Asdep 2 D2'],
            ['name' => 'TU Asdep 3 D2', 'username' => 'tuasdep3d2', 'role' => 'tu', 'unit' => 'Asdep 3 D2'],
            ['name' => 'TU Asdep 4 D2', 'username' => 'tuasdep4d2', 'role' => 'tu', 'unit' => 'Asdep 4 D2'],
            ['name' => 'TU Asdep 5 D2', 'username' => 'tuasdep5d2', 'role' => 'tu', 'unit' => 'Asdep 5 D2'],

            // Eselon 1 & 2
            ['name' => 'Widiastuti', 'username' => 'deputi2', 'role' => 'eselon1', 'unit' => 'Deputi 2'],
            ['name' => 'Karsan', 'username' => 'sesdepd2', 'role' => 'eselon2', 'unit' => 'Sekretariat D2'],
            ['name' => 'Kus Prisetihadi', 'username' => 'asdep1d2', 'role' => 'eselon2', 'unit' => 'Asdep 1 D2'],        // Tanaman Pangan
            ['name' => 'Silvi Charlote Sumanti', 'username' => 'asdep2d2', 'role' => 'eselon2', 'unit' => 'Asdep 2 D2'], // Pertanian
            ['name' => 'Bona Kusuma', 'username' => 'asdep3d2', 'role' => 'eselon2', 'unit' => 'Asdep 3 D2'],            // Sarpras
            ['name' => 'Radian Bagiyono', 'username' => 'asdep4d2', 'role' => 'eselon2', 'unit' => 'Asdep 4 D2'],        // Perkebunan & Hortikultura
            ['name' => 'Harimurti', 'username' => 'asdep5d2', 'role' => 'eselon2', 'unit' => 'Asdep 5 D2'],              // Peternakan

            // Pegawai Pelaksana (placeholder, ganti nama aslinya nanti). Tambah baris lagi per unit bila perlu.
            ['name' => 'Pegawai Asdep 1 D2', 'username' => 'peg1asdep1d2', 'role' => 'user', 'unit' => 'Asdep 1 D2'],
            ['name' => 'Pegawai Asdep 2 D2', 'username' => 'peg1asdep2d2', 'role' => 'user', 'unit' => 'Asdep 2 D2'],
            ['name' => 'Pegawai Asdep 3 D2', 'username' => 'peg1asdep3d2', 'role' => 'user', 'unit' => 'Asdep 3 D2'],
            ['name' => 'Pegawai Asdep 4 D2', 'username' => 'peg1asdep4d2', 'role' => 'user', 'unit' => 'Asdep 4 D2'],
            ['name' => 'Pegawai Asdep 5 D2', 'username' => 'peg1asdep5d2', 'role' => 'user', 'unit' => 'Asdep 5 D2'],
        ];
    }
}