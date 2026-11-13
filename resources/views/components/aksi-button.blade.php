{{--
Komponen tombol aksi dengan dialog konfirmasi / alasan (SweetAlert2 lewat window.appDialog).
Dipakai untuk: Kirim ke Keuangan, Ajukan pengembalian, Setujui/Tolak pengajuan,
verifikasi (K2/K3), Input SAKTI, Selesai OTP, dst. Halaman lain (mis. Monitoring Memo)
tinggal panggil komponen ini, TIDAK perlu menyalin script lagi.

Simpan file ini di: resources/views/components/aksi-button.blade.php

Syarat: layout sudah memuat SweetAlert2 + window.appDialog (sudah ada di layouts/app.blade.php).

Contoh:

1) Konfirmasi saja
<x-aksi-button :url="route('memo.kirim', $memo['id'])" varian="biru"
    :konfirmasi="'Kirim memo ' . $memo['nomor_memo'] . ' ke Keuangan?'">
    Kirim ke Keuangan
</x-aksi-button>

2) Wajib isi alasan
<x-aksi-button :url="route('memo.ajukan', $memo['id'])" varian="abu"
    prompt="alasan" :wajib="true" prompt-label="Alasan pengajuan (wajib diisi):">
    Ajukan pengembalian
</x-aksi-button>

3) Dengan field tetap
<x-aksi-button :url="route('memo.pengajuan.putusan', $id)" varian="hijau"
    :fields="['setuju' => 1]" konfirmasi="Setujui pengajuan?">
    Setujui pengajuan
</x-aksi-button>

Props:
- url (wajib)        : tujuan POST
- varian             : biru (default) | hijau | merah | abu  -> warna tombol & tombol OK di dialog
- konfirmasi         : teks dialog konfirmasi (kosong = tanpa dialog konfirmasi)
- prompt             : nama field yang diisi dari dialog alasan (kosong = tanpa dialog alasan)
- promptLabel        : label kotak alasan
- wajib              : true kalau alasan wajib diisi
- fields             : array field tetap yang ikut terkirim
--}}

@props([
    'url',
    'varian' => 'biru',
    'konfirmasi' => null,
    'prompt' => null,
    'promptLabel' => 'Keterangan:',
    'wajib' => false,
    'fields' => [],
])

@php
    $base = 'inline-flex items-center justify-center px-2.5 py-1.5 xl:py-1 text-[11px] font-semibold rounded-md border transition cursor-pointer focus:outline-none focus-visible:ring-2';
    $kelasVarian = [
        'biru' => 'text-white bg-blue-600 hover:bg-blue-700 border-blue-600 focus-visible:ring-blue-500',
        'hijau' => 'text-white bg-emerald-600 hover:bg-emerald-700 border-emerald-600 focus-visible:ring-emerald-500',
        'merah' => 'text-red-600 bg-white hover:bg-red-50 border-red-400 focus-visible:ring-red-500',
        'abu' => 'text-gray-600 bg-white hover:bg-gray-100 border-gray-300 focus-visible:ring-blue-500',
    ];
    // Warna tombol OK di dialog (abu memakai biru)
    $varianDialog = in_array($varian, ['hijau', 'merah'], true) ? $varian : 'biru';
@endphp

<button type="button" {{ $attributes->merge(['class' => 'js-aksi ' . $base . ' ' . ($kelasVarian[$varian] ?? $kelasVarian['biru'])]) }}
    data-url="{{ $url }}"
    data-varian="{{ $varianDialog }}"
    data-fields="{{ json_encode((object) $fields) }}"
    @if ($konfirmasi) data-konfirmasi="{{ $konfirmasi }}" @endif
    @if ($prompt) data-prompt="{{ $prompt }}" data-prompt-label="{{ $promptLabel }}" data-prompt-wajib="{{ $wajib ? '1' : '0' }}" @endif>
    {{ $slot }}
</button>

{{-- Script dimuat SEKALI saja per halaman, berapa pun jumlah tombolnya. --}}
@once
    <script>
        (function () {
            // Form POST dibuat lewat JS (bukan di markup) supaya aman dipakai di dalam <form> lain.
            function kirimAksi(url, fields) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.style.display = 'none';

                const token = document.querySelector('meta[name="csrf-token"]');
                const tambah = function (nama, nilai) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = nama;
                    input.value = nilai;
                    form.appendChild(input);
                };

                tambah('_token', token ? token.content : '');
                Object.keys(fields).forEach(function (nama) { tambah(nama, fields[nama]); });

                document.body.appendChild(form);
                form.submit();
            }

            document.addEventListener('click', async function (e) {
                const btn = e.target.closest('.js-aksi');
                if (!btn || btn.disabled) return;

                const judul = btn.textContent.trim();
                const varian = btn.dataset.varian || 'biru';
                const fields = JSON.parse(btn.dataset.fields || '{}');

                if (btn.dataset.konfirmasi) {
                    const ok = await window.appDialog.confirm({
                        judul: judul,
                        pesan: btn.dataset.konfirmasi,
                        varian: varian,
                        teksOk: judul,
                    });
                    if (!ok) return;
                }

                if (btn.dataset.prompt) {
                    const nilai = await window.appDialog.prompt({
                        judul: judul,
                        label: btn.dataset.promptLabel || 'Keterangan:',
                        wajib: btn.dataset.promptWajib === '1',
                        varian: varian,
                        teksOk: judul,
                    });
                    if (nilai === null) return;
                    fields[btn.dataset.prompt] = nilai;
                }

                // Cegah klik ganda
                document.querySelectorAll('.js-aksi').forEach(function (b) { b.disabled = true; });
                kirimAksi(btn.dataset.url, fields);
            });
        })();
    </script>
@endonce
