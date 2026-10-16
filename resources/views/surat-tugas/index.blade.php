@php
    // Kelas dasar tombol aksi di tabel (warna per tombol ditulis di tempatnya)
    $btn = 'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium transition';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Surat Tugas</h2>
            <a href="{{ route('surat-tugas.create') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">+ Buat Surat Tugas</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="rounded-lg border text-sm p-3" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d;">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-lg border text-sm p-3" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">{{ session('error') }}</div>
            @endif

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

            <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
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
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs" style="{{ $it->status_style }}">{{ $it->status_label }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('surat-tugas.show', $it) }}" title="Lihat detail" class="{{ $btn }} bg-blue-50 text-blue-700 hover:bg-blue-100">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Lihat
                                        </a>
                                        <a href="{{ route('surat-tugas.pdf', $it) }}" target="_blank" title="Buka PDF" class="{{ $btn }} bg-rose-50 text-rose-700 hover:bg-rose-100">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            PDF
                                        </a>
                                        @if ($it->isDraf())
                                            <a href="{{ route('surat-tugas.edit', $it) }}" title="Edit draf" class="{{ $btn }} bg-amber-50 text-amber-700 hover:bg-amber-100">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                                Edit
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada surat tugas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
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