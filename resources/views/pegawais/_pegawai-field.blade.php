{{--
    Dropdown "Pegawai" untuk form tambah/edit user.
    Pakai di dalam <form>, misalnya di bawah field Unit:

        @include('users._pegawai-field')

    Butuh variabel dari UserController: $pegawai, $terpakai, $saran. Variabel $user hanya ada di form edit.
--}}
@php
    $pilih = old('pegawai_id', isset($user) ? ($user->pegawai_id ?? ($saran ?? null)) : null);
@endphp

<div>
    <label for="pegawai_id" class="block text-sm font-medium text-gray-700">Pegawai (opsional)</label>

    <select id="pegawai_id" name="pegawai_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
        <option value="">— Belum dipetakan —</option>
        @foreach ($pegawai as $p)
            @php $dipakaiLain = $terpakai->has($p->id); @endphp
            <option value="{{ $p->id }}" @selected((int) $pilih === (int) $p->id) @disabled($dipakaiLain)>
                {{ $p->nama_gelar ?: $p->nama }}{{ $p->nip ? ' — ' . $p->nip : '' }}{{ $dipakaiLain ? ' (dipakai ' . $terpakai[$p->id] . ')' : '' }}
            </option>
        @endforeach
    </select>

    @if (isset($user) && !$user->pegawai_id && ($saran ?? null))
        <p class="mt-1 text-xs" style="color:#92400e;">Saran dari kesamaan nama, belum tersimpan. Periksa lalu tekan
            Simpan.</p>
    @endif

    <p class="mt-1 text-xs text-gray-500">Akun yang dipetakan ke pegawai mendapat tugas "Isi Laporan" saat ST yang
        memuat pegawai itu diterbitkan.</p>

    @error('pegawai_id')
        <p class="mt-1 text-xs" style="color:#b91c1c;">{{ $message }}</p>
    @enderror
</div>
