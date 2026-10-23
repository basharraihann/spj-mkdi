<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Monitoring Surat Masuk') }}
            </h2>
            @if(auth()->user()?->isTu() || auth()->user()?->isAdmin() || in_array(auth()->user()->role ?? '', ['admin', 'tu', 'tu-d2', 'admin-d2'], true))
                <a href="{{ route('surat-masuk.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition flex items-center gap-2">
                    <span>+</span> Input Surat Masuk
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Flash Success Message --}}
        @if (session('success'))
            <div class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <form method="GET" action="{{ route('surat-masuk.index') }}" class="flex gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari perihal, asal, atau nomor agenda..."
                        class="flex-1 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-xl hover:bg-slate-900 transition">Cari</button>
                </form>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Agenda</th>
                        <th class="py-3 px-4">Perihal</th>
                        <th class="py-3 px-4">Asal Surat</th>
                        <th class="py-3 px-4">Tgl Diterima</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($suratMasuk ?? [] as $surat)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-medium text-slate-900">{{ $surat->nomor_agenda }}</td>
                            <td class="py-3.5 px-4 font-medium text-blue-600 max-w-xs truncate">{{ $surat->perihal }}</td>
                            <td class="py-3.5 px-4">{{ $surat->asal_surat }}</td>
                            <td class="py-3.5 px-4">{{ $surat->created_at ? $surat->created_at->format('d M Y') : '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ ucfirst($surat->status ?? 'baru') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Tombol Detail --}}
                                    <a href="{{ route('surat-masuk.show', $surat->id) }}" class="text-xs font-medium text-slate-600 hover:text-blue-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition">Detail</a>

                                    {{-- Tombol Hapus (Khusus Admin & TU) --}}
                                    @if(auth()->user()?->isTu() || auth()->user()?->isAdmin() || in_array(auth()->user()->role ?? '', ['admin', 'tu', 'tu-d2', 'admin-d2'], true))
                                        <form action="{{ route('surat-masuk.destroy', $surat->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus Surat Masuk ini? Seluruh rantai disposisi terkait juga akan terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-lg transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data surat masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>