<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto space-y-6">

        <!-- Back & Title -->
        <div>
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke User Manajemen
            </a>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah User Baru</h1>
            <p class="text-sm text-gray-500 mt-1">Buat akun pengguna baru dan tentukan hak akses peran (role)</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
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
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required
                            placeholder="contoh: ahmad_subagja"
                            class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('username') border-rose-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Hanya huruf, angka, dan underscore (_). Digunakan untuk login.</p>
                    @error('username')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        placeholder="user@mkdi.go.id"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('email') border-rose-500 @enderror">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hak Akses / Role <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition focus:outline-none has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/30 border-gray-200 hover:border-gray-300">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="role" value="user" {{ old('role', 'user') == 'user' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="font-semibold text-gray-900 text-sm">Staf / User</span>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Staf</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Dapat mengelola Agenda, Pegawai, dan Nomor Memo. Tidak dapat mengelola user.</p>
                        </label>

                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition focus:outline-none has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/30 border-gray-200 hover:border-gray-300">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="role" value="admin" {{ old('role') == 'admin' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="font-semibold text-gray-900 text-sm">Administrator</span>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-purple-100 text-purple-700">Admin</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Akses penuh ke seluruh sistem, termasuk kelola User Manajemen.</p>
                        </label>
                    </div>
                    @error('role')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('password') border-rose-500 @enderror">
                        @error('password')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Konfirmasi Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            placeholder="Ulangi password"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>
                </div>

                <!-- Submit Action Buttons -->
                <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm transition hover:shadow">
                        Simpan User Baru
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
