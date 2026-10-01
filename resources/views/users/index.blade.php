<x-app-layout title="User Manajemen">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar User Manajemen') }}
        </h2>
    </x-slot>

    @php
        // Kolom daftar (dipakai header & baris supaya selalu sejajar)
        $cols = 'xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_9.5rem_minmax(0,1.5fr)_8.5rem_10rem]';
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5">

            {{-- Judul + jumlah --}}
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800">User Manajemen</h3>
                    <p class="text-sm text-gray-500">Kelola akun pengguna, hak akses administrator, dan staf pengelola
                        SPJ.</p>
                </div>
                <span
                    class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5"
                    aria-live="polite">
                    <span id="user-visible-count">{{ $users->count() }}</span> dari {{ $users->count() }} user
                </span>
            </div>

            {{-- Ringkasan --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Total user</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5 tabular-nums">{{ $totalUsers }}</p>
                </div>
                <div class="rounded-xl bg-purple-50/60 border border-purple-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Administrator</p>
                    <p class="text-xl font-bold text-purple-700 mt-0.5 tabular-nums">{{ $totalAdmin }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50/60 border border-emerald-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Staf / Operator</p>
                    <p class="text-xl font-bold text-emerald-700 mt-0.5 tabular-nums">{{ $totalStaf }}</p>
                </div>
            </div>

            {{-- Filter instan, tanpa reload --}}
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="user-filter-q" class="text-xs font-semibold text-gray-600">Cari</label>
                    <input type="text" id="user-filter-q" autocomplete="off" placeholder="Nama, username, atau unit..."
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-64 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="user-filter-role" class="text-xs font-semibold text-gray-600">Role</label>
                    <select id="user-filter-role"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-44 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua Role</option>
                        <option value="admin">Administrator</option>
                        <option value="user">Staf / User</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="user-filter-unit" class="text-xs font-semibold text-gray-600">Unit</label>
                    <select id="user-filter-unit"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-56 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua Unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->kode }} - {{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="user-filter-reset"
                    class="text-sm text-gray-500 hover:text-gray-700 transition px-1 py-2 cursor-pointer">
                    Reset
                </button>

                <div class="hidden sm:block flex-1"></div>

                <a href="{{ route('users.create') }}"
                    class="inline-flex w-full sm:w-auto justify-center items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah User
                </a>
            </div>

            {{-- Daftar: seperti tabel di layar lebar, bertumpuk di HP & tablet --}}
            <div class="border border-gray-100 rounded-xl overflow-hidden">

                {{-- Judul kolom (hanya layar lebar) --}}
                <div
                    class="hidden xl:grid {{ $cols }} gap-4 bg-gray-50 px-4 py-3 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                    <div>User</div>
                    <div>Username</div>
                    <div>Role</div>
                    <div>Unit</div>
                    <div>Dibuat</div>
                    <div class="text-right">Aksi</div>
                </div>

                <div id="user-list" class="divide-y divide-gray-100">
                    @forelse ($users as $u)
                        <div class="js-user-row bg-white hover:bg-gray-50/60 transition px-4 py-4 grid grid-cols-2 gap-x-4 gap-y-3 xl:items-center {{ $cols }}"
                            data-search="{{ strtolower($u->name . ' ' . $u->username . ' ' . ($u->unit?->nama ?? '') . ' ' . ($u->unit?->kode ?? '')) }}"
                            data-role="{{ $u->role }}" data-unit="{{ $u->unit_id }}">

                            {{-- User --}}
                            <div class="col-span-2 xl:col-span-1 flex items-center gap-3 min-w-0">
                                <div
                                    class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span class="text-sm font-semibold text-gray-800 break-words">{{ $u->name }}</span>
                                    @if (auth()->id() === $u->id)
                                        <span
                                            class="bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-0.5 rounded-full border border-blue-200">
                                            Anda
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Username --}}
                            <div class="min-w-0 text-xs">
                                <p class="xl:hidden text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                    Username</p>
                                <p class="text-gray-700 font-medium break-words">{{ $u->username ?: '—' }}</p>
                            </div>

                            {{-- Role --}}
                            <div class="min-w-0">
                                <p class="xl:hidden text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-1">
                                    Role</p>
                                @if ($u->isAdmin())
                                    <span
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Administrator
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Staf / User
                                    </span>
                                @endif
                            </div>

                            {{-- Unit --}}
                            <div class="col-span-2 xl:col-span-1 min-w-0 text-xs">
                                <p class="xl:hidden text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                    Unit</p>
                                @if ($u->unit)
                                    <p class="text-gray-700 break-words">
                                        <span class="font-semibold">{{ $u->unit->kode }}</span>
                                        <span class="text-gray-500">{{ $u->unit->nama }}</span>
                                    </p>
                                @else
                                    <p class="text-gray-400">—</p>
                                @endif
                            </div>

                            {{-- Tanggal dibuat --}}
                            <div class="col-span-2 xl:col-span-1 min-w-0 text-xs">
                                <p class="xl:hidden text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                                    Dibuat</p>
                                <p class="text-gray-500">
                                    {{ $u->created_at ? $u->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                </p>
                            </div>

                            {{-- Aksi --}}
                            <div class="col-span-2 xl:col-span-1 flex items-center gap-2 xl:justify-end">
                                <a href="{{ route('users.edit', $u) }}" title="Edit {{ $u->name }}"
                                    class="inline-flex flex-1 xl:flex-none items-center justify-center px-3 py-2 xl:py-1 text-[11px] font-semibold text-blue-600 bg-white hover:bg-blue-50 border border-blue-500 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                    Edit
                                </a>

                                @if (auth()->id() !== $u->id)
                                    <form method="POST" action="{{ route('users.destroy', $u) }}"
                                        class="js-delete-user flex flex-1 xl:flex-none m-0" data-name="{{ $u->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus {{ $u->name }}"
                                            class="inline-flex flex-1 items-center justify-center px-3 py-2 xl:py-1 text-[11px] font-semibold text-red-500 bg-white hover:bg-red-50 border border-red-400 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-14 text-center text-gray-500">
                            <p class="text-sm font-semibold text-gray-800">Belum ada data user.</p>
                            <p class="text-xs text-gray-500 mt-1">Klik "Tambah User" untuk membuat akun pertama.</p>
                        </div>
                    @endforelse
                </div>

                <div id="user-no-result" class="hidden px-5 py-14 text-center text-gray-500">
                    <p class="text-sm font-semibold text-gray-800">Tidak ada user yang cocok dengan filter.</p>
                    <p class="text-xs text-gray-500 mt-1">Ubah kata kunci atau klik Reset untuk menampilkan semua.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            // Konfirmasi hapus (nama user diambil dari data-name, aman untuk tanda kutip)
            document.querySelectorAll('.js-delete-user').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!confirm('Apakah Anda yakin ingin menghapus user ' + form.dataset.name + '?')) {
                        e.preventDefault();
                    }
                });
            });

            // Search & filter instan
            const qInput = document.getElementById('user-filter-q');
            const roleSelect = document.getElementById('user-filter-role');
            const unitSelect = document.getElementById('user-filter-unit');
            const resetBtn = document.getElementById('user-filter-reset');

            const rows = Array.from(document.querySelectorAll('.js-user-row'));
            const list = document.getElementById('user-list');
            const noResult = document.getElementById('user-no-result');
            const countEl = document.getElementById('user-visible-count');

            if (rows.length === 0) return;

            function applyFilter() {
                const q = qInput.value.trim().toLowerCase();
                const role = roleSelect.value;
                const unit = unitSelect.value;
                let visible = 0;

                rows.forEach(function (row) {
                    const ok = (q === '' || row.dataset.search.includes(q))
                        && (role === '' || row.dataset.role === role)
                        && (unit === '' || row.dataset.unit === unit);

                    row.style.display = ok ? '' : 'none';
                    if (ok) visible++;
                });

                countEl.textContent = visible;
                noResult.classList.toggle('hidden', visible > 0);
                list.style.display = visible > 0 ? '' : 'none';
            }

            qInput.addEventListener('input', applyFilter);
            roleSelect.addEventListener('change', applyFilter);
            unitSelect.addEventListener('change', applyFilter);

            resetBtn.addEventListener('click', function () {
                qInput.value = '';
                roleSelect.value = '';
                unitSelect.value = '';
                applyFilter();
            });
        })();
    </script>
</x-app-layout>