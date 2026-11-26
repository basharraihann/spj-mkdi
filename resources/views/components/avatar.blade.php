{{--
    Avatar bulat. Pakai foto user kalau ada, kalau tidak (atau gagal dimuat) tampil inisial berwarna.

    Contoh:
      <x-avatar :user="$d->kepada" class="h-9 w-9 text-xs" />
      <x-avatar :src="$p['foto'] ?? null" :nama="$p['nama']" class="h-11 w-11 text-sm" />

    Ukuran (h-* w-* text-*) wajib diberikan lewat class.
    Cara mengambil foto SAMA PERSIS dengan KalenderDisposisiController::orang(),
    supaya halaman kalender dan halaman lain menampilkan foto yang sama.
--}}
@props(['user' => null, 'src' => null, 'nama' => null])

@php
    $nama = (string) ($nama ?: ($user?->name ?? ''));

    // Foto: src eksplisit dulu, lalu kolom/accessor di model User (urutan sama dengan controller kalender)
    $foto = $src;
    if (! $foto && $user) {
        $foto = $user->profile_photo_url ?? $user->avatar_url ?? $user->avatar ?? $user->foto ?? null;
    }

    // Path relatif di disk publik diubah jadi URL /storage/...
    if ($foto && ! preg_match('#^(https?:)?//|^/|^data:#i', $foto)) {
        $foto = asset('storage/' . ltrim($foto, '/'));
    }

    // Inisial dua huruf + warna tetap per nama
    $inisial = collect(preg_split('/\s+/', trim($nama)))
        ->filter()->take(2)
        ->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))
        ->implode('') ?: '?';

    $palet = [
        'bg-blue-100 text-blue-700',
        'bg-emerald-100 text-emerald-700',
        'bg-amber-100 text-amber-700',
        'bg-violet-100 text-violet-700',
        'bg-rose-100 text-rose-700',
        'bg-cyan-100 text-cyan-700',
    ];
    $warna = $palet[crc32($nama) % count($palet)];
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-grid flex-none select-none place-items-center overflow-hidden rounded-full font-semibold ' . $warna]) }} aria-hidden="true">
    <span>{{ $inisial }}</span>
    @if ($foto)
        {{-- Menutupi inisial. Kalau gambar gagal dimuat, dihapus sehingga inisial tampil lagi. --}}
        <img src="{{ $foto }}" alt="" loading="lazy" onerror="this.remove()"
            class="absolute inset-0 h-full w-full object-cover">
    @endif
</span>
