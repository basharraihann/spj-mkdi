<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar user (Manajemen User).
     */
    public function index(Request $request): View
    {
        $query = User::query()->with('unit');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('unit', function ($u) use ($search) {
                        $u->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $units = Unit::orderBy('nama')->get();

        $totalUsers = User::count();
        
        // Akumulasi Admin (Admin Utama & Admin D2)
        $totalAdmin = User::whereIn('role', [
            User::ROLE_ADMIN ?? 'admin', 
            User::ROLE_ADMIN_D2 ?? 'admin-d2'
        ])->count();

        // Akumulasi Pimpinan (Eselon 1, Eselon 2, Deputi, Asdep)
        $totalPimpinan = User::whereIn('role', [
            User::ROLE_ESELON1 ?? 'eselon1',
            User::ROLE_ESELON2 ?? 'eselon2',
            User::ROLE_DEPUTI_D2 ?? 'deputi-d2',
            User::ROLE_ASDEP_D2 ?? 'asdep-d2',
            'pimpinan',
            'deputi',
            'asdep'
        ])->count();

        // Akumulasi Staf / TU / Pelaksana
        $totalStaf = User::whereIn('role', [
            User::ROLE_USER ?? 'user',
            User::ROLE_TU ?? 'tu',
            User::ROLE_TU_D2 ?? 'tu-d2',
            User::ROLE_PEGAWAI_D2 ?? 'pegawai-d2',
            'pegawai'
        ])->count();

        return view('users.index', compact(
            'users',
            'units',
            'totalUsers',
            'totalAdmin',
            'totalPimpinan',
            'totalStaf'
        ));
    }

    /**
     * Form tambah user baru.
     */
    public function create(): View
    {
        $units = Unit::orderBy('nama')->get();

        return view('users.create', compact('units'));
    }

    /**
     * Simpan user baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:' . User::class, 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'unit_id' => ['nullable', 'exists:units,id'],
            'role' => ['required', 'string', Rule::in(array_keys(User::ROLE_LABEL))],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'unit_id' => $validated['unit_id'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index');
    }

    /**
     * Form edit data user.
     */
    public function edit(User $user): View
    {
        $units = Unit::orderBy('nama')->get();

        return view('users.edit', compact('user', 'units'));
    }

    /**
     * Update data user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id), 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'unit_id' => ['nullable', 'exists:units,id'],
            'role' => ['required', 'string', Rule::in(array_keys(User::ROLE_LABEL))],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        // Cegah admin menurunkan role akunnya sendiri (bisa kehilangan akses manajemen user).
        if ($user->id === auth()->id() && $validated['role'] !== $user->role) {
            return back()->withInput()
                ->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->unit_id = $validated['unit_id'] ?? null;
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Hapus user dari database.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}