<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Monitoring Surat Keluar & Draf Dokumen') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('surat-keluar.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">
                    Unduh Rekap (Excel/CSV)
                </a>
                <a href="{{ route('surat-keluar.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition">
                    + Buat Draf Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <form method="GET" action="{{ route('surat-keluar.index') }}" class="flex flex-wrap gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari hal, nomor surat, atau tujuan..."
                        class="flex-1 min-w-[200px] rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <select name="jenis" class="rounded-xl border-slate-200 text-sm">
                        <option value="">Semua Jenis</option>
                        @foreach (\App\Models\SuratKeluar::JENIS as $val => $label)
                            <option value="{{ $val }}" @selected(request('jenis') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-xl border-slate-200 text-sm">
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\SuratKeluar::STATUS_LABEL as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-xl hover:bg-slate-900 transition">Cari</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Jenis</th>
                            <th class="py-3 px-4">Nomor</th>
                            <th class="py-3 px-4">Hal</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Pembuat</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse ($suratKeluar as $s)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 text-xs font-semibold">{{ $s->nama_jenis }}</td>
                                <td class="py-3.5 px-4 font-mono text-xs">{{ $s->nomor_surat ?: '— (draf)' }}</td>
                                <td class="py-3.5 px-4 font-medium text-blue-600 max-w-xs truncate">{{ $s->hal }}</td>
                                <td class="py-3.5 px-4 text-xs">{{ optional($s->tanggal_surat)->format('d M Y') }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">{{ $s->status_label }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">{{ $s->pembuat->name ?? '-' }}</td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('surat-keluar.show', $s->id) }}" class="text-xs font-medium text-slate-600 hover:text-blue-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-sm text-slate-400">Belum ada dokumen surat keluar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $suratKeluar->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
