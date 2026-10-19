<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pemetaan akun (users) ke data pegawai. Rutenya sudah dibungkus middleware admin. */
class UserPegawaiController extends Controller
{
    public function index()
    {
        $users = User::with('pegawai:id,nama,nama_gelar')->orderBy('name')->get();

        $pegawai = Pegawai::orderBy('urutan')->orderBy('nama')
            ->get(['id', 'nama', 'nama_gelar', 'nip']);

        // pegawai yang sudah dipakai akun lain: pegawai_id => nama akun
        $terpakai = $users->whereNotNull('pegawai_id')->pluck('name', 'pegawai_id');

        // Saran berdasarkan kesamaan nama. Hanya saran: baru tersimpan kalau admin menekan Simpan.
        $norm = fn($s) => mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $s)));
        $saran = [];
        foreach ($users->whereNull('pegawai_id') as $u) {
            $cocok = $pegawai->first(
                fn($p) => !$terpakai->has($p->id)
                    && in_array($norm($u->name), [$norm($p->nama), $norm($p->nama_gelar)], true)
            );
            if ($cocok) {
                $saran[$u->id] = $cocok->id;
            }
        }

        return view('pemetaan-akun.index', compact('users', 'pegawai', 'terpakai', 'saran'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'pegawai_id' => [
                'nullable',
                'exists:pegawai,id',
                Rule::unique('users', 'pegawai_id')->ignore($user->id),
            ],
        ], [
            'pegawai_id.unique' => 'Pegawai ini sudah dipetakan ke akun lain.',
        ]);

        $user->pegawai_id = $data['pegawai_id'] ?: null;
        $user->save();

        return back()->with('success', 'Pemetaan akun ' . $user->name . ' diperbarui.');
    }
}
