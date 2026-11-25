{{-- Satu langkah disposisi + anak-anaknya (rekursif). Variabel: $n = ['d' => Disposisi, 'anak' => [...]] --}}
@php
    $d = $n['d'];
    $warna = [
        'baru' => ['bg-red-500', 'bg-red-50 text-red-700 border-red-200'],
        'dibaca' => ['bg-amber-500', 'bg-amber-50 text-amber-700 border-amber-200'],
        'diteruskan' => ['bg-blue-500', 'bg-blue-50 text-blue-700 border-blue-200'],
        'selesai' => ['bg-emerald-500', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
    ][$d->status] ?? ['bg-gray-400', 'bg-gray-50 text-gray-600 border-gray-200'];
    $saya = (int) $d->kepada_user_id === (int) auth()->id();
@endphp

<li class="relative pl-6">
    <span class="absolute left-0 top-4 h-3 w-3 rounded-full ring-4 ring-white {{ $warna[0] }}"></span>
    <div class="rounded-xl border {{ $saya ? 'border-blue-300 bg-blue-50/30' : 'border-gray-200 bg-white' }} p-3.5">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="text-sm">
                <span class="text-gray-500">{{ $d->dari?->name ?? '-' }}</span>
                <span class="mx-1 text-gray-400">→</span>
                <span class="font-semibold text-gray-900">{{ $d->kepada?->name ?? '-' }}</span>
                @if ($saya)
                    <span class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">Anda</span>
                @endif
                <div class="text-xs text-gray-400">{{ $d->kepada?->unit?->nama }} ·
                    {{ \App\Models\User::ROLE_LABELS[$d->kepada?->role] ?? '' }}</div>
            </div>
            <span class="whitespace-nowrap rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $warna[1] }}">{{ $d->status_label }}</span>
        </div>

        @if ($d->instruksi_label)
            <div class="mt-2 flex flex-wrap gap-1">
                @foreach ($d->instruksi_label as $ins)
                    <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[11px] text-gray-700">{{ $ins }}</span>
                @endforeach
            </div>
        @endif
        @if ($d->catatan)
            <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $d->catatan }}</p>
        @endif

        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-500">
            <span>Dikirim {{ $d->created_at?->translatedFormat('d M Y H:i') }}</span>
            @if ($d->batas_waktu)
                <span class="{{ $d->terlambat ? 'text-red-600 font-semibold' : '' }}">Batas
                    {{ $d->batas_waktu->translatedFormat('d M Y') }}{{ $d->terlambat ? ' (terlambat)' : '' }}</span>
            @endif
            @if ($d->dibaca_at)
                <span>Dibaca {{ $d->dibaca_at->translatedFormat('d M Y H:i') }}</span>
            @endif
            @if ($d->diteruskan_at)
                <span>Diteruskan {{ $d->diteruskan_at->translatedFormat('d M Y H:i') }}</span>
            @endif
            @if ($d->selesai_at)
                <span>Selesai {{ $d->selesai_at->translatedFormat('d M Y H:i') }}</span>
            @endif
        </div>

        @if ($d->laporan)
            <div class="mt-2 rounded-lg border border-emerald-200 bg-emerald-50/60 px-3 py-2 text-sm text-emerald-900">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Laporan tindak lanjut</p>
                <p class="whitespace-pre-line">{{ $d->laporan }}</p>
                @if ($d->laporan_file_path)
                    <a href="{{ route('disposisi.laporan-berkas', $d) }}" target="_blank"
                        class="mt-1 inline-block text-xs font-medium text-emerald-700 underline">{{ $d->laporan_file_name ?? 'Lampiran laporan' }}</a>
                @endif
            </div>
        @endif
    </div>

    @if (!empty($n['anak']))
        <ul class="mt-3 ml-1.5 space-y-3 border-l-2 border-dashed border-gray-200 pl-3">
            @foreach ($n['anak'] as $anak)
                @include('disposisi._node', ['n' => $anak])
            @endforeach
        </ul>
    @endif
</li>
