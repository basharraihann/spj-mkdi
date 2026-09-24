{{-- Dipakai bersama oleh setiap section (PNS, Non PNS, Belum Diisi) di index.blade.php --}}

@if ($list->isEmpty())
    <div class="border border-dashed border-gray-200 rounded-xl px-6 py-10 text-center text-sm text-gray-400">
        Belum ada pegawai di kategori ini.
    </div>
@else

    {{-- Desktop / tablet: table view --}}
    <div class="hidden sm:block border border-gray-100 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                        <th class="w-8"></th>
                        <th class="text-center px-2 py-3.5 font-semibold w-10">No</th>
                        <th class="text-left px-5 py-3.5 font-semibold">Nama</th>
                        <th class="text-left px-5 py-3.5 font-semibold">NIP</th>
                        <th class="text-left px-5 py-3.5 font-semibold">Pangkat / Gol.</th>
                        <th class="text-left px-5 py-3.5 font-semibold">Jabatan</th>
                        <th class="text-center px-5 py-3.5 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="sortable-body divide-y divide-gray-100">
                    @foreach ($list as $pegawai)
                        <tr data-id="{{ $pegawai->id }}" class="group hover:bg-gray-50/60 transition">
                            <td
                                class="pl-3 py-4 text-gray-300 group-hover:text-gray-400 drag-handle cursor-grab active:cursor-grabbing transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="9" cy="6" r="1.5" />
                                    <circle cx="9" cy="12" r="1.5" />
                                    <circle cx="9" cy="18" r="1.5" />
                                    <circle cx="15" cy="6" r="1.5" />
                                    <circle cx="15" cy="12" r="1.5" />
                                    <circle cx="15" cy="18" r="1.5" />
                                </svg>
                            </td>
                            <td class="row-number px-2 py-4 text-center text-gray-400 tabular-nums">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-800">{{ $pegawai->nama }}</div>
                                @if ($pegawai->role_penandatangan)
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $pegawai->role_penandatangan }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-500 whitespace-nowrap font-mono text-xs">
                                {{ $pegawai->nip ?: '—' }}
                            </td>
                            <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                                {{ $pegawai->pangkat ?: '—' }}{{ $pegawai->golongan ? ' / ' . $pegawai->golongan : '' }}
                            </td>
                            <td class="px-5 py-4 text-gray-500 max-w-xs">
                                <span class="line-clamp-2">{{ $pegawai->jabatan }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('pegawais.edit', $pegawai) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-semibold border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('pegawais.destroy', $pegawai) }}" method="POST"
                                        onsubmit="return confirm('Hapus pegawai {{ $pegawai->nama }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-semibold border border-red-200 text-red-500 bg-white hover:bg-red-50 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: stacked card view --}}
    <div class="sm:hidden sortable-body space-y-3">
        @foreach ($list as $pegawai)
            <div data-id="{{ $pegawai->id }}" class="border border-gray-100 rounded-xl p-4 bg-white">
                <div class="flex items-start gap-3">
                    <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 pt-1 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="9" cy="6" r="1.5" />
                            <circle cx="9" cy="12" r="1.5" />
                            <circle cx="9" cy="18" r="1.5" />
                            <circle cx="15" cy="6" r="1.5" />
                            <circle cx="15" cy="12" r="1.5" />
                            <circle cx="15" cy="18" r="1.5" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-800 truncate">{{ $pegawai->nama }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $pegawai->jabatan }}</div>
                                @if ($pegawai->role_penandatangan)
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $pegawai->role_penandatangan }}</div>
                                @endif
                            </div>
                            <span class="row-number flex-shrink-0 text-xs text-gray-300 tabular-nums pt-0.5">
                                #{{ $loop->iteration }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 mt-3 text-xs">
                            <div>
                                <span class="text-gray-400">NIP</span>
                                <div class="text-gray-600 font-mono">{{ $pegawai->nip ?: '—' }}</div>
                            </div>
                            <div>
                                <span class="text-gray-400">Pangkat / Gol.</span>
                                <div class="text-gray-600">
                                    {{ $pegawai->pangkat ?: '—' }}{{ $pegawai->golongan ? ' / ' . $pegawai->golongan : '' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-3.5 pt-3 border-t border-gray-50">
                            <a href="{{ route('pegawais.edit', $pegawai) }}"
                                class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 rounded-md text-xs font-semibold border border-blue-500 text-blue-600 bg-white active:bg-blue-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                                </svg>
                                Edit
                            </a>
                            <form action="{{ route('pegawais.destroy', $pegawai) }}" method="POST" class="flex-1"
                                onsubmit="return confirm('Hapus pegawai {{ $pegawai->nama }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 rounded-md text-xs font-semibold border border-red-200 text-red-500 bg-white active:bg-red-50 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif