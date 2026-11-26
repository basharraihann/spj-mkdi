<x-app-layout title="Create User">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah User Baru') }}
        </h2>
    </x-slot>
    @php
        // Daftar role yang tampil di form. Tambah role baru cukup menambah satu blok di sini.
        // 'unit' => true berarti unit wajib dipilih untuk role tersebut.
        $roleOptions = [
            [
                'value' => 'user',
                'title' => 'Staf / User',
                'badge' => 'Staf',
                'badge_class' => 'bg-emerald-100 text-emerald-700',
                'desc' => 'Operator SPJ di unitnya.',
                'unit' => true,
                'allow' => ['Dashboard', 'Agenda, Memorandum, Nominatif', 'Lihat Realisasi Anggaran', 'Lihat Monitoring Memo', 'Unduh Massal'],
                'deny' => ['Pegawai, Nomor MAK', 'Approval memo, upload anggaran'],
            ],
            [
                'value' => 'tu_unit',
                'title' => 'TU Unit',
                'badge' => 'TU Unit',
                'badge_class' => 'bg-sky-100 text-sky-700',
                'desc' => 'Tata Usaha di tingkat unit kerja.',
                'unit' => true,
                'allow' => ['Dashboard', 'Pegawai, Nomor MAK', 'Lihat Realisasi Anggaran', 'Lihat Monitoring Memo', 'Unduh Massal'],
                'deny' => ['Agenda, Memorandum, Nominatif', 'Approval memo, upload anggaran'],
            ],
            [
                'value' => 'tu_sekretariat',
                'title' => 'TU Sekretariat',
                'badge' => 'TU Sekr.',
                'badge_class' => 'bg-cyan-100 text-cyan-700',
                'desc' => 'Tata Usaha Sekretariat (Keuangan Sesdep).',
                'unit' => false,
                'allow' => ['Dashboard', 'Pegawai, Nomor MAK', 'Lihat Realisasi Anggaran', 'Approval Monitoring Memo', 'Unduh Massal'],
                'deny' => ['Agenda, Memorandum, Nominatif', 'Upload anggaran'],
            ],
            [
                'value' => 'eselon2',
                'title' => 'Eselon II',
                'badge' => 'Eselon II',
                'badge_class' => 'bg-amber-100 text-amber-700',
                'desc' => 'Pimpinan: hanya memantau.',
                'unit' => false,
                'allow' => ['Dashboard', 'Lihat Realisasi Anggaran'],
                'deny' => ['Monitoring Memo, Unduh Massal', 'Semua menu pengelolaan'],
            ],
            [
                'value' => 'admin',
                'title' => 'Administrator',
                'badge' => 'Admin',
                'badge_class' => 'bg-purple-100 text-purple-700',
                'desc' => 'Pengelola sistem untuk semua unit.',
                'unit' => false,
                'allow' => ['Semua menu Staf dan TU', 'Upload Realisasi Anggaran', 'Approval Monitoring Memo', 'Pemetaan Unit', 'User Manajemen'],
                'deny' => [],
            ],
            [
                'value' => 'super_admin',
                'title' => 'Super Admin',
                'badge' => 'Super',
                'badge_class' => 'bg-rose-100 text-rose-700',
                'desc' => 'Akses penuh tanpa batasan.',
                'unit' => false,
                'allow' => ['Semua menu dan semua fitur', 'Semua unit'],
                'deny' => [],
            ],
        ];

        $unitRequiredRoles = collect($roleOptions)->where('unit', true)->pluck('value')->values();
    @endphp

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        <!-- Subjudul (judul halaman ada di header) -->
        <p class="text-sm text-gray-500">Buat akun pengguna baru dan tentukan hak akses peran (role)</p>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                @csrf

                <!-- Isian utama: 2 kolom di layar lebar -->
                <div class="fg-2">
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
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
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
                                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>
                                    {{ $unit->kode }} - {{ $unit->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Wajib untuk Staf dan TU Unit, karena menentukan data yang
                            bisa dilihat. Boleh kosong untuk role lainnya.</p>
                        @error('unit_id')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hak Akses / Role <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($roleOptions as $role)
                            <label
                                class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition focus:outline-none has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/30 border-gray-200 hover:border-gray-300">
                                <div class="flex items-center justify-between mb-2 gap-2">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="role" value="{{ $role['value'] }}"
                                            {{ old('role', 'user') == $role['value'] ? 'checked' : '' }}
                                            class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <span class="font-semibold text-gray-900 text-sm">{{ $role['title'] }}</span>
                                    </div>
                                    <span
                                        class="text-xs font-semibold px-2 py-0.5 rounded whitespace-nowrap {{ $role['badge_class'] }}">{{ $role['badge'] }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mb-2">{{ $role['desc'] }}</p>
                                <ul class="space-y-1 text-xs text-gray-600">
                                    @foreach ($role['allow'] as $item)
                                        <li class="flex gap-1.5"><span class="text-emerald-600">✓</span> {{ $item }}</li>
                                    @endforeach
                                    @foreach ($role['deny'] as $item)
                                        <li class="flex gap-1.5 text-gray-400"><span>✕</span> {{ $item }}</li>
                                    @endforeach
                                </ul>
                            </label>
                        @endforeach
                    </div>
                    @error('role')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-100 fg-2">
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required placeholder="Minimal 8 karakter"
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
                    <a href="{{ route('users.index') }}"
                        class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm transition hover:shadow">
                        Simpan User Baru
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        // Unit wajib hanya untuk role tertentu (Staf dan TU Unit), opsional untuk lainnya.
        (function () {
            const unitRequiredRoles = @json($unitRequiredRoles);
            const unit = document.getElementById('unit_id');
            const star = document.getElementById('unit-required');

            function sync() {
                const checked = document.querySelector('input[type="radio"][name="role"]:checked');
                const needUnit = checked && unitRequiredRoles.includes(checked.value);
                unit.required = needUnit;
                star.classList.toggle('hidden', !needUnit);
            }

            document.querySelectorAll('input[type="radio"][name="role"]').forEach(function (el) {
                el.addEventListener('change', sync);
            });
            sync();
        })();
    </script>
</x-app-layout>