<x-app-layout title="Edit User">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        // Admin tidak boleh mengubah role akunnya sendiri (dicek juga di UserController@update).
        $akunSendiri = $user->id === auth()->id();
    @endphp

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto space-y-6">

        <!-- Back & Title -->
        <div>
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke User Manajemen
            </a>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Data User</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui data profil, email, unit, role, atau ubah password user</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        placeholder="Contoh: Ahmad Subagja"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('name') border-rose-500 @enderror">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Username <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                        <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}"
                            required placeholder="contoh: ahmad_subagja"
                            class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('username') border-rose-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Hanya huruf, angka, dan underscore (_). Digunakan untuk login.
                    </p>
                    @error('username')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        placeholder="user@mkdi.go.id"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit -->
                <div>
                    <label for="unit_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Unit <span id="unit-required" class="text-rose-500">*</span>
                    </label>
                    <select name="unit_id" id="unit_id"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('unit_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $user->unit_id) == $unit->id)>
                                {{ $unit->kode }} - {{ $unit->nama }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Wajib untuk Staf dan TU, karena menentukan data yang bisa
                        dilihat. Boleh kosong untuk Administrator.</p>
                    @error('unit_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hak Akses / Role <span class="text-rose-500">*</span>
                    </label>

                    @if ($akunSendiri)
                        {{-- Radio yang disabled tidak ikut terkirim, jadi role dikirim lewat input hidden. --}}
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <p class="text-xs text-amber-600 mb-2">Role akun yang sedang Anda pakai tidak bisa diubah.</p>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <label
                            class="relative flex flex-col p-4 rounded-xl border-2 transition focus:outline-none has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/30 border-gray-200 {{ $akunSendiri ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:border-gray-300' }}">
                            <div class="flex items-center justify-between mb-1 gap-2">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="role" value="user" {{ old('role', $user->role) == 'user' ? 'checked' : '' }}
                                        @disabled($akunSendiri)
                                        class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="font-semibold text-gray-900 text-sm">Staf / User</span>
                                </div>
                                <span
                                    class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Staf</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Dapat mengelola Agenda, Pegawai, dan Nomor Memo
                                unitnya. Tidak dapat mengubah status memo, upload anggaran, atau mengelola user.</p>
                        </label>

                        <label
                            class="relative flex flex-col p-4 rounded-xl border-2 transition focus:outline-none has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/30 border-gray-200 {{ $akunSendiri ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:border-gray-300' }}">
                            <div class="flex items-center justify-between mb-1 gap-2">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="role" value="tu" {{ old('role', $user->role) == 'tu' ? 'checked' : '' }}
                                        @disabled($akunSendiri)
                                        class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="font-semibold text-gray-900 text-sm">Tata Usaha</span>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-sky-100 text-sky-700">TU</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Seperti Staf, ditambah dapat mengubah status memo dan
                                meng-upload anggaran. Tidak dapat mengelola user.</p>
                        </label>

                        <label
                            class="relative flex flex-col p-4 rounded-xl border-2 transition focus:outline-none has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/30 border-gray-200 {{ $akunSendiri ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:border-gray-300' }}">
                            <div class="flex items-center justify-between mb-1 gap-2">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="role" value="admin" {{ old('role', $user->role) == 'admin' ? 'checked' : '' }}
                                        @disabled($akunSendiri)
                                        class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="font-semibold text-gray-900 text-sm">Administrator</span>
                                </div>
                                <span
                                    class="text-xs font-semibold px-2 py-0.5 rounded bg-purple-100 text-purple-700">Admin</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Akses penuh ke seluruh sistem dan semua unit,
                                termasuk kelola User Manajemen.</p>
                        </label>
                    </div>
                    @error('role')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Section Password Optional -->
                <div class="pt-6 border-t border-gray-100 space-y-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Ubah Password</h3>
                        <p class="text-xs text-gray-500">Biarkan kosong jika Anda tidak ingin mengubah password user.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Password Baru -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password Baru
                            </label>
                            <input type="password" name="password" id="password"
                                placeholder="Kosongkan jika tidak diubah"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('password') border-rose-500 @enderror">
                            @error('password')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                placeholder="Ulangi password baru"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>
                    </div>
                </div>

                <!-- Submit Action Buttons -->
                <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}"
                        class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm transition hover:shadow">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        // Unit wajib untuk Staf dan TU, opsional untuk Administrator.
        (function () {
            const unit = document.getElementById('unit_id');
            const star = document.getElementById('unit-required');

            function sync() {
                const checked = document.querySelector('input[type="radio"][name="role"]:checked');
                const isAdmin = checked && checked.value === 'admin';
                unit.required = !isAdmin;
                star.classList.toggle('hidden', isAdmin);
            }

            document.querySelectorAll('input[type="radio"][name="role"]').forEach(function (el) {
                el.addEventListener('change', sync);
            });
            sync();
        })();
    </script>
</x-app-layout>