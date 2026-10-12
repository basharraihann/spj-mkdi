<x-app-layout title="Upload Anggaran">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Data Anggaran') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Perbarui Data</h2>
            <p class="text-sm text-gray-500 mb-5">
                Upload file <span class="font-medium">Laporan Ketersediaan Dana Detail</span> (.xlsx) dari SAKTI.
                Data yang berhasil diproses akan langsung menggantikan data aktif; kalau gagal, data lama tetap aman.
            </p>

            <form action="{{ route('anggaran.upload.store') }}" method="POST" enctype="multipart/form-data"
                class="flex flex-col sm:flex-row sm:items-start gap-3">
                @csrf
                <div class="flex-1">
                    <input type="file" name="file" accept=".xlsx,.xls" required
                        class="block w-full text-sm text-gray-600 border border-gray-200 rounded-xl file:mr-4 file:py-2.5 file:px-4 file:border-0 file:bg-gray-50 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100">
                    @error('file')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" onclick="this.disabled = true; this.textContent = 'Memproses...'; this.form.submit();"
                    class="inline-flex justify-center items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 transition disabled:opacity-60">
                    Upload
                </button>
            </form>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-4">Riwayat Upload</h3>
            <div class="rounded-xl border border-gray-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-medium">Waktu</th>
                            <th class="text-left px-4 py-2.5 font-medium">Periode</th>
                            <th class="text-left px-4 py-2.5 font-medium">File</th>
                            <th class="text-left px-4 py-2.5 font-medium">Oleh</th>
                            <th class="text-right px-4 py-2.5 font-medium">Item</th>
                            <th class="text-right px-4 py-2.5 font-medium">Total Pagu</th>
                            <th class="text-left px-4 py-2.5 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($uploads as $u)
                            <tr>
                                <td class="px-4 py-2.5 whitespace-nowrap text-gray-600">{{ $u->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2.5 text-gray-600">{{ $u->periode ?: '-' }}</td>
                                <td class="px-4 py-2.5 text-gray-600">{{ $u->nama_file }}</td>
                                <td class="px-4 py-2.5 text-gray-600">{{ $u->uploader->name ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($u->jumlah_item, 0, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap text-gray-600">Rp {{ number_format($u->total_pagu, 0, ',', '.') }}</td>
                                <td class="px-4 py-2.5">
                                    @if ($u->is_active)
                                        <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-1 rounded-full">Aktif</span>
                                    @else
                                        <span class="text-xs text-gray-400">Arsip</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-8 text-center text-gray-400">Belum ada upload.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-400 mt-3">Sistem menyimpan 5 upload terakhir; yang lebih lama dihapus otomatis.</p>
        </div>

        <a href="{{ route('anggaran.index') }}" class="inline-block text-sm text-indigo-600 hover:text-indigo-800">&larr; Kembali ke Monitor Anggaran</a>
    </div>
</x-app-layout>
