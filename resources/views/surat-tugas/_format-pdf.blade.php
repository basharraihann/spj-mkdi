{{-- Cetak ST: versi ditentukan otomatis dari jumlah peserta. Dipakai di surat-tugas/show.blade.php --}}
@php
    $jumlah = $st->peserta->count();
    $pakaiLampiran = $jumlah > 1;
@endphp

<section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
    <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
        <h3 class="text-sm font-semibold text-slate-900">Cetak Surat Tugas</h3>
        <p class="mt-0.5 text-xs text-slate-500">Versi dipilih otomatis sesuai jumlah peserta.</p>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 p-4 sm:p-5">
        <div class="min-w-0">
            <p class="text-[13px] font-semibold text-slate-800">
                {{ $pakaiLampiran ? 'Versi dengan lampiran' : 'Versi tanpa lampiran' }}
            </p>
            <p class="mt-0.5 text-xs text-slate-500">
                {{ $jumlah }} peserta
                &middot;
                {{ $pakaiLampiran ? 'daftar peserta dipisah ke halaman lampiran.' : 'peserta langsung tercantum di badan surat.' }}
            </p>
        </div>

        @if ($jumlah > 0)
            <a href="{{ route('surat-tugas.pdf', $st) }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1.5 rounded-xl bg-blue-700 px-4 py-2 text-[13px] font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                Cetak PDF
            </a>
        @else
            <span class="text-xs text-slate-400">Tambahkan peserta dulu.</span>
        @endif
    </div>
</section>