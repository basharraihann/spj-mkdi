<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Tusi</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">

        {{-- Tambah --}}
        <form method="POST" action="{{ route('tusi.store') }}"
            class="grid grid-cols-12 gap-2 bg-white p-3 rounded-xl border shadow-sm text-sm">
            @csrf
            <input name="kode" value="{{ old('kode') }}" placeholder="Kode"
                class="col-span-12 sm:col-span-2 border rounded-lg px-2 py-1.5" required>
            <input name="nama" value="{{ old('nama') }}" placeholder="Nama tusi"
                class="col-span-12 sm:col-span-5 border rounded-lg px-2 py-1.5" required>
            <input name="urutan" type="number" value="{{ old('urutan') }}" placeholder="Urut"
                class="col-span-4 sm:col-span-1 border rounded-lg px-2 py-1.5">
            <label class="col-span-4 sm:col-span-2 flex items-center gap-1">
                <input type="checkbox" name="aktif" value="1" checked> Aktif
            </label>
            <button class="col-span-4 sm:col-span-2 rounded-lg px-3 py-1.5"
                style="background:#2563eb;color:#fff;">Tambah</button>

            @if ($errors->any())
                <div class="col-span-12 text-xs" style="color:#b91c1c;">{{ $errors->first() }}</div>
            @endif
        </form>

        {{-- Daftar --}}
        <div class="bg-white rounded-xl border shadow-sm divide-y text-sm">
            @forelse ($items as $t)
                <div class="grid grid-cols-12 gap-2 p-3 items-center">
                    <form method="POST" action="{{ route('tusi.update', $t) }}"
                        class="col-span-12 lg:col-span-10 grid grid-cols-12 gap-2">
                        @csrf
                        @method('PUT')
                        <input name="kode" value="{{ $t->kode }}"
                            class="col-span-12 sm:col-span-2 border rounded-lg px-2 py-1.5">
                        <input name="nama" value="{{ $t->nama }}"
                            class="col-span-12 sm:col-span-5 border rounded-lg px-2 py-1.5">
                        <input name="urutan" type="number" value="{{ $t->urutan }}"
                            class="col-span-4 sm:col-span-1 border rounded-lg px-2 py-1.5">
                        <label class="col-span-4 sm:col-span-2 flex items-center gap-1">
                            <input type="checkbox" name="aktif" value="1" @checked($t->aktif)> Aktif
                        </label>
                        <button class="col-span-4 sm:col-span-2 rounded-lg px-2 py-1.5"
                            style="background:#1f2937;color:#fff;">Simpan</button>
                    </form>

                    <div class="col-span-12 lg:col-span-2 flex items-center justify-between">
                        <span class="text-gray-500">{{ $t->surat_tugas_count }} ST</span>
                        <form method="POST" action="{{ route('tusi.destroy', $t) }}"
                            onsubmit="return confirm('Hapus tusi ini?')">
                            @csrf
                            @method('DELETE')
                            <button style="color:#dc2626;">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-4 text-gray-500">Belum ada tusi. Jalankan TusiSeeder atau tambahkan di atas.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
