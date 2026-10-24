<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyeragamkan role akun ke 5 role baku hasil kesepakatan:
 * admin, user, tu, eselon1, eselon2.
 *
 * Akun Deputi 2 yang sebelumnya memakai role khusus (deputi-d2, asdep-d2, dst.)
 * dipindah ke role baku dan dibedakan lewat users.unit_id = unit 'Deputi 2'.
 * Username, password, pegawai_id, dan data lain TIDAK diubah.
 */
return new class extends Migration
{
    private const MAP = [
        'admin-d2' => 'admin',
        'tu-d2' => 'tu',
        'deputi-d2' => 'eselon1',
        'asdep-d2' => 'eselon2',
        'pegawai-d2' => 'user',
    ];

    public function up(): void
    {
        $unitId = DB::table('units')->where('kode', 'Deputi 2')->value('id');

        if (!$unitId) {
            $unitId = DB::table('units')->insertGetId([
                'kode' => 'Deputi 2',
                'nama' => 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (self::MAP as $lama => $baru) {
            DB::table('users')->where('role', $lama)->update([
                'role' => $baru,
                'unit_id' => $unitId,
            ]);
        }

        // Role alias lama tanpa -d2 ikut dibakukan (tidak mengubah unit)
        DB::table('users')->where('role', 'deputi')->update(['role' => 'eselon1']);
        DB::table('users')->where('role', 'asdep')->update(['role' => 'eselon2']);
        DB::table('users')->where('role', 'pegawai')->update(['role' => 'user']);
    }

    public function down(): void
    {
        // Kembalikan role D2 berdasarkan username bawaan seeder
        foreach (self::MAP as $lama => $baru) {
            DB::table('users')->where('username', $lama)->update(['role' => $lama]);
        }
    }
};
