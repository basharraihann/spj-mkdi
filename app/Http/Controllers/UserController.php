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
    public function index()
    {
        // Ambil semua user beserta unit-nya (tanpa paginate dan tanpa filter)
        $users = User::with('unit')
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('kode')->get();

        // Ringkasan dihitung dari koleksi yang sama supaya angkanya selalu cocok
        $totalUsers = $users->count();
        $totalAdmin = $users->filter(fn($u) => $u->isAdmin())->count();
        $totalPimpinan = $users->filter(fn($u) => $u->isPimpinan())->count();
        $totalStaf = $totalUsers - $totalAdmin - $totalPimpinan;

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