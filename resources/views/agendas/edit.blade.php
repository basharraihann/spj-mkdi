<x-app-layout title="Edit Agenda">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Agenda</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center gap-2 mb-5 px-1 text-sm">
                <a href="{{ route('agendas.index') }}" class="text-gray-400 hover:text-gray-600 transition">Agenda</a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-700 font-semibold truncate max-w-xs">{{ $agenda->uraian_kegiatan }}</span>
            </div>

            @if ($errors->any())
                @php
                    $fieldLabels = [
                        'nomor_st' => 'Nomor Surat Tugas',
                        'nomor_st_karo' => 'Nomor ST Kepala Biro',
                        'uraian_kegiatan' => 'Uraian Kegiatan',
                        'tujuan' => 'Tujuan (Provinsi)',
                        'kota_tujuan' => 'Kab/Kota',
                        'alat_angkut' => 'Alat Angkut',
                        'tanggal_mulai' => 'Tanggal Mulai',
                        'tanggal_selesai' => 'Tanggal Selesai',
                        'ppk_id' => 'Pejabat Pembuat Komitmen (PPK)',
                        'bendahara_id' => 'Bendahara',
                        'penanggung_jawab_id' => 'Penanggung Jawab Kegiatan',
                        'pic_id' => 'PIC',
                        'pegawai_id' => 'Peserta',
                        'mak' => 'MAK',
                        'uraian_giat' => 'Uraian Giat',
                        'uraian_komponen' => 'Uraian Komponen',
                        'uraian_akun_ap' => 'Uraian Akun (AP)',
                        'uraian_belanja' => 'Uraian Belanja',
                        'petugas_verifikasi_id' => 'Petugas Verifikasi',
                        'klasifikasi' => 'Klasifikasi',
                        'nilai_persen' => 'Nilai %',
                        'pagu' => 'Pagu',
                        'pengajuan_nominal' => 'Pengajuan',
                        'nomor_memo_pns' => 'Nomor Memo (PNS)',
                        'nomor_memo_non_pns' => 'Nomor Memo (Non PNS)',
                    ];
                @endphp
                <div class="flex gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Ada {{ $errors->count() }} field yang perlu dicek:</p>
                        <ul class="space-y-0.5 list-disc list-inside">
                            @foreach ($errors->getMessages() as $field => $messages)
                                @php
                                    $baseField = explode('.', $field)[0];
                                    $label = $fieldLabels[$baseField] ?? $baseField;
                                @endphp
                                <li>
                                    <strong>{{ $label }}</strong>
                                    — {{ str_contains($messages[0], 'validation.') ? 'wajib diisi / formatnya belum sesuai' : $messages[0] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('agendas.update', $agenda) }}" method="POST" id="agenda-form">
                @csrf
                @method('PUT')
                @include('agendas._form')

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('agendas.show', $agenda) }}" class="text-sm text-gray-400 hover:text-gray-600 transition">
                        &larr; Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.kabKotaMap = @json($kabKotaMap);
        window.currentKota = @json(old('kota_tujuan', $agenda->kota_tujuan));
    </script>
    @vite(['resources/js/agendas/agenda-form.js'])
</x-app-layout>