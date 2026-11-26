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
     * Role yang boleh dipilih di form.
     */
    private function roles(): array
    {
        return [
            User::ROLE_USER,
            User::ROLE_TU_UNIT,
            User::ROLE_TU_SEKRETARIAT,
            User::ROLE_ESELON2,
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ];
    }

    /**
     * Role yang wajib punya unit (data dibatasi per unit).
     */
    private function unitRequiredRoles(): string
    {
        return implode(',', [User::ROLE_USER, User::ROLE_TU_UNIT, User::ROLE_ESELON2]);
    }

    /**
     * Pilihan status kepegawaian. Nilainya harus sama dengan yang dipakai di tabel pegawai.
     */
    private function statusKepegawaian(): array
    {
        return ['PNS', 'Non PNS'];
    }

    /**
     * Pesan validasi khusus.
     */
    private function messages(): array
    {
        return [
            'unit_id.required_if' => 'Unit wajib dipilih untuk role Staf, TU Unit, dan Eselon II.',
            'role.in' => 'Role yang dipilih tidak valid.',
            'status_kepegawaian.required' => 'Status kepegawaian wajib dipilih.',
            'status_kepegawaian.in' => 'Status kepegawaian yang dipilih tidak valid.',
        ];
    }

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

        $users = $query->orderBy('created_at', 'desc')->get();

        $units = Unit::orderBy('id')->get();

        $totalUsers = User::count();
        $totalAdmin = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->count();
        $totalTu = User::whereIn('role', [User::ROLE_TU_UNIT, User::ROLE_TU_SEKRETARIAT])->count();
        $totalStaf = User::where('role', User::ROLE_USER)->count();

        return view('users.index', compact('users', 'units', 'totalUsers', 'totalAdmin', 'totalTu', 'totalStaf'));
    }

    /**
     * Form tambah user baru.
     */
    public function create(): View
    {
        $units = Unit::orderBy('id')->get();
        $statusOptions = $this->statusKepegawaian();

        return view('users.create', compact('units', 'statusOptions'));
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
            'unit_id' => ['required_if:role,' . $this->unitRequiredRoles(), 'nullable', 'exists:units,id'],
            'role' => ['required', 'string', Rule::in($this->roles())],
            'status_kepegawaian' => ['required', Rule::in($this->statusKepegawaian())],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], $this->messages());

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'unit_id' => $validated['unit_id'] ?? null,
            'role' => $validated['role'],
            'status_kepegawaian' => $validated['status_kepegawaian'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Form edit data user.
     */
    public function edit(User $user): View
    {
        $units = Unit::orderBy('id')->get();
        $statusOptions = $this->statusKepegawaian();

        return view('users.edit', compact('user', 'units', 'statusOptions'));
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
            'unit_id' => ['required_if:role,' . $this->unitRequiredRoles(), 'nullable', 'exists:units,id'],
            'role' => ['required', 'string', Rule::in($this->roles())],
            'status_kepegawaian' => ['required', Rule::in($this->statusKepegawaian())],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ], $this->messages());

        // Admin tidak boleh mengubah role akunnya sendiri (mencegah kehilangan satu-satunya admin).
        if ($user->id === auth()->id() && $validated['role'] !== $user->role) {
            return back()
                ->withErrors(['role' => 'Anda tidak bisa mengubah role akun sendiri.'])
                ->withInput();
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->unit_id = $validated['unit_id'] ?? null;
        $user->role = $validated['role'];
        $user->status_kepegawaian = $validated['status_kepegawaian'];

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