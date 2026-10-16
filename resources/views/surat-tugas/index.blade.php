<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Surat Tugas</h2>
            <a href="{{ route('surat-tugas.create') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">+ Buat Surat Tugas</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @foreach (['success' => 'green', 'error' => 'red'] as $k => $c)
                @if (session($k))
                    <div class="rounded-lg bg-{{ $c }}-50 border border-{{ $c }}-200 text-{{ $c }}-700 text-sm p-3">{{ session($k) }}</div>
                @endif
            @endforeach

            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor / perihal..." class="rounded-lg border-gray-300 text-sm w-full sm:w-64">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\SuratTugas::STATUS_LABEL as $k => $l)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 rounded-lg border text-sm">Filter</button>
            </form>

            {{-- Desktop/tablet: tabel --}}
            <div class="hidden md:block bg-white rounded-xl shadow-sm border overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3">Nomor</th>
                            <th class="px-4 py-3">Perihal / Untuk</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Peserta</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($items as $it)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium whitespace-nowrap">{{ $it->nomor ?? '— (draf)' }}</td>
                                <td class="px-4 py-3 max-w-md">{{ \Illuminate\Support\Str::limit($it->untuk[0] ?? '-', 110) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $it->tanggal_indo }}</td>
                                <td class="px-4 py-3">{{ $it->peserta_count }} orang</td>
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs {{ $it->status_badge }}">{{ $it->status_label }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                    <a href="{{ route('surat-tugas.show', $it) }}" class="text-blue-600 hover:underline">Lihat</a>
                                    <a href="{{ route('surat-tugas.pdf', $it) }}" target="_blank" class="text-gray-700 hover:underline">PDF</a>
                                    @if ($it->isDraf())
                                        <a href="{{ route('surat-tugas.edit', $it) }}" class="text-amber-600 hover:underline">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada surat tugas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile: kartu --}}
            <div class="md:hidden space-y-3">
                @forelse ($items as $it)
                    <div class="bg-white rounded-xl border shadow-sm p-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="font-semibold text-sm break-all">{{ $it->nomor ?? '— (draf)' }}</div>
                            <span class="shrink-0 px-2 py-1 rounded-full text-xs {{ $it->status_badge }}">{{ $it->status_label }}</span>
                        </div>
                        <p class="text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($it->untuk[0] ?? '-', 120) }}</p>
                        <div class="text-xs text-gray-500">{{ $it->tanggal_indo }} · {{ $it->peserta_count }} peserta</div>
                        <div class="flex gap-4 pt-1 text-sm">
                            <a href="{{ route('surat-tugas.show', $it) }}" class="text-blue-600">Lihat</a>
                            <a href="{{ route('surat-tugas.pdf', $it) }}" target="_blank" class="text-gray-700">PDF</a>
                            @if ($it->isDraf())
                                <a href="{{ route('surat-tugas.edit', $it) }}" class="text-amber-600">Edit</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl border p-6 text-center text-sm text-gray-500">Belum ada surat tugas.</div>
                @endforelse
            </div>

            {{ $items->links() }}

            @if ($isAdmin)
                <form method="POST" action="{{ route('surat-tugas.counter') }}" class="bg-white rounded-xl border p-4 flex flex-wrap items-end gap-3 text-sm">
                    @csrf
                    <div>
                        <label class="block text-gray-600">Tahun</label>
                        <input type="number" name="tahun" value="{{ $tahun }}" class="rounded-lg border-gray-300 text-sm w-28">
                    </div>
                    <div>
                        <label class="block text-gray-600">Nomor terakhir terpakai</label>
                        <input type="number" name="last_number" value="{{ $counter }}" class="rounded-lg border-gray-300 text-sm w-36">
                    </div>
                    <button class="px-3 py-2 rounded-lg border">Atur counter</button>
                    <p class="text-gray-500">Contoh: isi 203 agar ST berikutnya bernomor 204. Counter hanya boleh naik.</p>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>