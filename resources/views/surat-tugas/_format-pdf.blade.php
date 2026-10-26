{{-- Pilihan format cetak ST sesuai template 2026. Dipakai di surat-tugas/show.blade.php --}}
<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-4 py-2.5">
        <h3 class="text-sm font-semibold text-slate-900">Cetak Sesuai Template 2026</h3>
    </div>
    <div class="flex flex-wrap gap-2 p-4 text-[13px] font-semibold">
        <a href="{{ route('surat-tugas.pdf', $st) }}" target="_blank" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-slate-700 hover:bg-slate-50">Format Standar (lama)</a>
        <a href="{{ route('surat-tugas.pdf', [$st, 'format' => 'lampiran']) }}" target="_blank" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-slate-700 hover:bg-slate-50">Staf dengan Lampiran</a>
        <a href="{{ route('surat-tugas.pdf', [$st, 'format' => 'tanpa_lampiran']) }}" target="_blank" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-slate-700 hover:bg-slate-50">Staf tanpa Lampiran</a>
        <a href="{{ route('surat-tugas.pdf', [$st, 'format' => 'an_sesdep']) }}" target="_blank" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-slate-700 hover:bg-slate-50">Eselon 2 (a.n. Sesdep)</a>
    </div>
</section>
