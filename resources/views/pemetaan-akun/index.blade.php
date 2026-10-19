<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pemetaan Akun ke Pegawai</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-3 text-sm">
        <p class="text-gray-600">
            Akun yang dipetakan ke pegawai otomatis mendapat tugas "Isi Laporan" saat ST yang memuat nama pegawai itu
            diterbitkan. Satu pegawai hanya bisa dipetakan ke satu akun.
        </p>

        @if ($errors->any())
            <div class="rounded-lg p-3 text-xs" style="background:#fee2e2;color:#b91c1c;">{{ $errors->first() }}</div>
        @endif

        <div class="bg-white rounded-xl border shadow-sm divide-y">
            @foreach ($users as $u)
                <form method="POST" action="{{ route('pemetaan-akun.update', $u) }}"
                    class="grid grid-cols-12 gap-2 p-3 items-center">
                    @csrf
                    @method('PUT')

                    <div class="col-span-12 md:col-span-4">
                        <div class="font-medium">{{ $u->name }}</div>
                        <div class="text-xs text-gray-500">{{ $u->email }}</div>
                    </div>

                    <div class="col-span-9 md:col-span-6">
                        @php $pilih = old('pegawai_id', $u->pegawai_id ?? ($saran[$u->id] ?? null)); @endphp
                        <select name="pegawai_id" class="border rounded-lg px-2 py-1.5 w-full">
                            <option value="">— Belum dipetakan —</option>
                            @foreach ($pegawai as $p)
                                @php $dipakaiLain = $terpakai->has($p->id) && (int) $u->pegawai_id !== (int) $p->id; @endphp
                                <option value="{{ $p->id }}" @selected((int) $pilih === (int) $p->id)
                                    @disabled($dipakaiLain)>
                                    {{ $p->nama_gelar ?: $p->nama }}{{ $p->nip ? ' — ' . $p->nip : '' }}{{ $dipakaiLain ? ' (dipakai ' . $terpakai[$p->id] . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if (!$u->pegawai_id && isset($saran[$u->id]))
                            <div class="text-xs mt-1" style="color:#92400e;">Saran dari kesamaan nama, belum
                                tersimpan. Periksa lalu tekan Simpan.</div>
                        @endif
                    </div>

                    <div class="col-span-3 md:col-span-2">
                        <button class="w-full rounded-lg px-3 py-1.5"
                            style="background:#1f2937;color:#fff;">Simpan</button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-app-layout>
