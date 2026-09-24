<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Isi Data Memorandum — <span class="text-indigo-600">{{ $statusLabel }}</span>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <p class="text-sm text-gray-500 mb-4">
                Agenda: <strong>{{ $agenda->uraian_kegiatan }}</strong> — {{ $agenda->tujuan }}
            </p>

            <div class="mb-4 p-3 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded text-sm">
                Anda sedang mengisi memorandum untuk kelompok <strong>{{ $statusLabel }}</strong>.
                Nomor memo dan uraian di bawah khusus buat grup ini. Data anggaran & administrasi (MAK, klasifikasi,
                dst) dipakai bersama PNS &amp; Non-PNS — udah diisi di halaman Buat/Edit Agenda, ditampilkan di
                bawah sebagai referensi.
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('agendas.memorandum.store', [$agenda, $status]) }}" class="space-y-6">
                @csrf

                {{-- ================= 1. MEMORANDUM ================= --}}
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 border-b pb-2">
                        1. Memorandum ({{ $statusLabel }})
                    </h3>

                    <div class="mb-4 text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded p-3">
                        <strong>Yth:</strong> Kuasa Pengguna Anggaran &nbsp;|&nbsp;
                        <strong>Hal:</strong> Permintaan Pembayaran Langsung (LS) Perjalanan Dinas
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Nomor Memo <span class="text-xs text-gray-400">({{ $statusLabel }})</span>
                            </label>
                            <input type="text" name="nomor_memo" value="{{ old('nomor_memo', $currentNomorMemo) }}"
                                placeholder="LS.D1.PPK/KU.00/VII/2026"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dari</label>
                            <input type="text" value="{{ $agenda->dari_memo ?: '—' }}" disabled
                                class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                Dipakai bersama PNS &amp; Non-PNS — ubah di
                                <a href="{{ route('agendas.edit', $agenda) }}"
                                    class="text-indigo-600 hover:underline">halaman Edit Agenda</a>.
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Uraian / Isi Memo <span class="text-xs text-gray-400">({{ $statusLabel }})</span>
                            </label>
                            <textarea name="uraian_memo" rows="3"
                                placeholder="Perdin Narsum asn menghadiri undangan rapat monitoring dan evaluasi ... pada tanggal ... di ..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>{{ old('uraian_memo', $currentUraianMemo) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ================= RINGKASAN ANGGARAN & ADMINISTRASI (read-only) ================= --}}
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <div class="flex items-center justify-between border-b pb-2 mb-4">
                        <h3 class="font-semibold text-gray-800">
                            Anggaran &amp; Administrasi
                            <span class="text-xs font-normal text-gray-400">(dipakai bersama PNS &amp; Non-PNS)</span>
                        </h3>
                        <a href="{{ route('agendas.edit', $agenda) }}"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                            Ubah di Edit Agenda &rarr;
                        </a>
                    </div>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400">MAK</dt>
                            <dd class="font-mono text-gray-700">{{ $agenda->mak ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Petugas Verifikasi</dt>
                            <dd class="text-gray-700">{{ $agenda->petugasVerifikasi->nama ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Uraian Giat</dt>
                            <dd class="text-gray-700">{{ $agenda->uraian_giat ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Uraian Komponen</dt>
                            <dd class="text-gray-700">{{ $agenda->uraian_komponen ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Uraian Akun (AP)</dt>
                            <dd class="text-gray-700">{{ $agenda->uraian_akun_ap ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Uraian Belanja</dt>
                            <dd class="text-gray-700">{{ $agenda->uraian_belanja ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Klasifikasi</dt>
                            <dd class="text-gray-700">{{ $agenda->klasifikasi ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Nilai %</dt>
                            <dd class="text-gray-700">{{ $agenda->nilai_persen ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Pagu</dt>
                            <dd class="text-gray-700">
                                {{ $agenda->pagu ? 'Rp ' . number_format($agenda->pagu, 0, ',', '.') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Pengajuan</dt>
                            <dd class="text-gray-700">
                                {{ $agenda->pengajuan_nominal ? 'Rp ' . number_format($agenda->pengajuan_nominal, 0, ',', '.') : '—' }}
                            </dd>
                        </div>
                    </dl>

                    <p class="text-sm text-gray-500 mt-4 italic">
                        Rincian Transfer &amp; Total Biaya di PDF otomatis dihitung dari data Peserta & Biaya
                        yang berstatus <strong>{{ $statusLabel }}</strong> saja — tidak perlu diisi ulang di sini.
                    </p>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('agendas.show', $agenda) }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Simpan &amp; Generate PDF ({{ $statusLabel }})
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>