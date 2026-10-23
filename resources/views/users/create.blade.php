<x-app-layout title="Create User">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
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

                <!-- Unit (dikelompokkan per Deputi) -->
                @php
                    $deputiLabel = [
                        1 => 'Deputi 1 — Tata Niaga dan Distribusi Pangan',
                        2 => 'Deputi 2 — Usaha Pangan dan Pertanian',
                        3 => 'Deputi 3 — Keterjangkauan dan Keamanan Pangan',
                        4 => 'Deputi 4 — Sumber Daya Maritim',
                    ];

                    $groups = [
                        'pimpinan' => ['label' => 'Pimpinan', 'items' => []],
                        'biro' => ['label' => 'Biro', 'items' => []],
                        1 => ['label' => $deputiLabel[1], 'items' => []],
                        2 => ['label' => $deputiLabel[2], 'items' => []],
                        3 => ['label' => $deputiLabel[3], 'items' => []],
                        4 => ['label' => $deputiLabel[4], 'items' => []],
                        'lain' => ['label' => 'Lainnya', 'items' => []],
                    ];

                    foreach ($units as $unit) {
                        $kode = (string) $unit->kode;

                        if (preg_match('/^Deputi ([1-4])$/', $kode, $m) || preg_match('/\bD([1-4])$/', $kode, $m)) {
                            $groups[(int) $m[1]]['items'][] = $unit;
                        } elseif (str_starts_with($kode, 'Wamenko')) {
                            $groups['pimpinan']['items'][] = $unit;
                        } elseif (str_starts_with($kode, 'Biro')) {
                            $groups['biro']['items'][] = $unit;
                        } else {
                            $groups['lain']['items'][] = $unit;
                        }
                    }

                    // Urutan dalam deputi: Deputi -> Sekretariat -> Asdep 1, 2, 3, ...
                    $rank = function ($u) {
                        if (str_starts_with($u->kode, 'Deputi')) {
                            return 0;
                        }
                        if (str_starts_with($u->kode, 'Sekretariat')) {
                            return 1;
                        }
                        return 2 + (preg_match('/^Asdep (\d+)/', $u->kode, $n) ? (int) $n[1] : 0);
                    };
                    foreach ([1, 2, 3, 4] as $d) {
                        usort($groups[$d]['items'], fn($a, $b) => $rank($a) <=> $rank($b));
                    }
                @endphp

                <div>
                    <label for="unit_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Unit
                    </label>
                    <select name="unit_id" id="unit_id"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition @error('unit_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($groups as $group)
                            @if (count($group['items']))
                                <optgroup label="{{ $group['label'] }}">
                                    @foreach ($group['items'] as $unit)
                                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>
                                            {{ $unit->kode }} — {{ $unit->nama }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Unit dikelompokkan berdasarkan Deputi.</p>
                    @error('unit_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Selection -->
                <div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Hak Akses / Role <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-0.5">Pilih satu peran sesuai tanggung jawab pengguna.</p>
                    </div>

                    @php
                        $roleInfo = [
                            'user' => [
                                'badge' => 'Staf',
                                'badgeClass' => 'bg-emerald-100 text-emerald-700',
                                'iconClass' => 'bg-emerald-100 text-emerald-600',
                                'checkedClass' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50/60',
                                'checkBg' => 'bg-emerald-500',
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />',
                                'desc' => 'Dapat mengelola Agenda, Pegawai, dan Nomor Memo. Tidak dapat mengelola user.',
                            ],
                            'pimpinan' => [
                                'badge' => 'Pimpinan',
                                'badgeClass' => 'bg-amber-100 text-amber-700',
                                'iconClass' => 'bg-amber-100 text-amber-600',
                                'checkedClass' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50/60',
                                'checkBg' => 'bg-amber-500',
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
                                'desc' => 'Hanya melihat: memantau kegiatan Surat Tugas, notulensi, dan materi di halaman Monitoring. Tidak dapat mengubah data.',
                            ],
                            'admin' => [
                                'badge' => 'Admin',
                                'badgeClass' => 'bg-purple-100 text-purple-700',
                                'iconClass' => 'bg-purple-100 text-purple-600',
                                'checkedClass' => 'peer-checked:border-purple-500 peer-checked:bg-purple-50/60',
                                'checkBg' => 'bg-purple-500',
                                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />',
                                'desc' => 'Akses penuh ke seluruh sistem, termasuk kelola User Manajemen.',
                            ],
                        ];
                        $fallbackRole = [
                            'badge' => null,
                            'badgeClass' => 'bg-gray-100 text-gray-700',
                            'iconClass' => 'bg-gray-100 text-gray-600',
                            'checkedClass' => 'peer-checked:border-indigo-500 peer-checked:bg-indigo-50/60',
                            'checkBg' => 'bg-indigo-500',
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />',
                            'desc' => '',
                        ];
                        $selectedRole = old('role', \App\Models\User::ROLE_USER);
                        $isSelf = false;
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach (\App\Models\User::ROLE_LABEL as $value => $label)
                            @php
                                $info = $roleInfo[$value] ?? $fallbackRole;
                                $badge = $info['badge'] ?? $label;
                            @endphp
                            <label class="relative block {{ $isSelf ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                <input type="radio" name="role" value="{{ $value }}"
                                    @checked($selectedRole === $value) @disabled($isSelf) class="peer sr-only">

                                <!-- Card -->
                                <div
                                    class="h-full flex md:flex-col gap-3 md:gap-4 p-4 pr-12 md:pr-4 rounded-2xl border-2 border-gray-200 bg-white transition
                                           hover:border-gray-300 hover:shadow-sm
                                           peer-focus-visible:ring-4 peer-focus-visible:ring-indigo-500/30
                                           peer-disabled:opacity-60 {{ $info['checkedClass'] }} peer-checked:shadow-md">
                                    <div
                                        class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center {{ $info['iconClass'] }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6"
                                            viewBox="0 0 24 24">{!! $info['icon'] !!}</svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <span class="font-semibold text-gray-900 text-sm">{{ $label }}</span>
                                            <span
                                                class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $info['badgeClass'] }}">{{ $badge }}</span>
                                        </div>
                                        <p class="text-xs leading-relaxed text-gray-500 mt-1.5">{{ $info['desc'] }}</p>
                                    </div>
                                </div>

                                <!-- Indikator pilihan (kanan atas) -->
                                <span
                                    class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-gray-300 bg-white peer-checked:hidden"></span>
                                <span
                                    class="absolute top-3 right-3 w-5 h-5 rounded-full hidden peer-checked:flex items-center justify-center text-white {{ $info['checkBg'] }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('role')
                        <p class="text-xs text-rose-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                <div class="pt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                    <a href="{{ route('users.index') }}"
                        class="px-5 py-2.5 rounded-xl text-sm font-medium text-center text-gray-600 hover:bg-gray-100 transition">
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
</x-app-layout>