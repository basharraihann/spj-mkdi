{{--
Tab halaman Disposisi.
Variabel: $tampil = 'daftar' | 'kalender' | 'jadwal'

--}}
@php
    $tampil = $tampil ?? 'daftar';
    // $menyatu = true: tab kecil tanpa latar sendiri, dipasang di dalam bar atas kartu (bar-nya dibuat oleh halaman pemanggil)
    $menyatu = $menyatu ?? false;
    // Badge dicache 30 detik per user supaya tiap render tab tidak query ulang
    $baruMasuk = \Illuminate\Support\Facades\Cache::remember(
        'disposisi.baru.' . auth()->id(),
        30,
        fn() => (int) \App\Models\Disposisi::jumlahBaru(auth()->id())
    );

    // Badge ikut tab tujuan notifikasi (sama dengan DisposisiBaru): pengelola -> Daftar, akun biasa -> Jadwal Pegawai
    $tabBadge = \App\Models\DisposisiSurat::bolehKelola(auth()->user()) ? 'daftar' : 'jadwal';

    $tabs = [
        ['daftar', 'Daftar', route('disposisi.index'), 'M4 6h16M4 12h16M4 18h16', $tabBadge === 'daftar' ? $baruMasuk : 0],
        ['kalender', 'Kalender', route('disposisi.index', ['tampil' => 'kalender']), 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 0],
        ['jadwal', 'Jadwal Pegawai', route('disposisi.index', ['tampil' => 'jadwal']), 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', $tabBadge === 'jadwal' ? $baruMasuk : 0],
    ];
@endphp

<nav @class([
    'flex w-full gap-1 overflow-x-auto' => true,
    'sm:inline-flex sm:w-auto' => $menyatu,
    'gap-0.5 rounded-xl bg-slate-100 p-1 sm:inline-flex sm:w-auto' => !$menyatu,
]) aria-label="Tampilan disposisi">
    @foreach ($tabs as [$kunci, $nama, $url, $ikon, $angka])
        <a href="{{ $url }}" data-tab @if ($tampil === $kunci) aria-current="page" @endif @class([
            'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100',
            'flex-none' => $menyatu,
            'flex-1 flex-none sm:flex-none' => !$menyatu,
            'bg-blue-600 text-white shadow-sm' => $tampil === $kunci,
            'text-slate-600 hover:bg-white hover:text-slate-900' => $tampil !== $kunci && $menyatu,
            'text-slate-600 hover:text-slate-900' => $tampil !== $kunci && !$menyatu,
        ])>
            <svg class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" />
            </svg>
            {{ $nama }}
            @if ($angka > 0)
                <span
                    class="grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1.5 text-[11px] font-semibold leading-none text-white">{{ $angka }}</span>
            @endif
        </a>
    @endforeach
</nav>

@once
    <style>
        #tab-loading {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0;
            background: #2563eb;
            z-index: 9999;
            box-shadow: 0 0 8px rgb(37 99 235 / .6);
            transition: width 6s cubic-bezier(.1, .7, .2, 1);
            pointer-events: none;
        }

        nav[aria-label="Tampilan disposisi"].sedang-pindah a:not(.tab-tuju) {
            opacity: .6;
        }
    </style>
    <script>
        (function () {
            var SEL = 'nav[aria-label="Tampilan disposisi"] a[data-tab]';
            var AKTIF = ['bg-blue-600', 'text-white', 'shadow-sm'];

            // 1) Prefetch begitu kursor/jari mendekat, supaya klik terasa instan
            var sudah = {};
            function prefetch(a) {
                if (sudah[a.href] || a.getAttribute('aria-current')) return;
                sudah[a.href] = true;
                var l = document.createElement('link');
                l.rel = 'prefetch'; l.as = 'document'; l.href = a.href;
                document.head.appendChild(l);
            }

            // 2) Feedback instan: tab langsung aktif + progress bar tipis
            function mulai(a) {
                var nav = a.closest('nav');
                nav.classList.add('sedang-pindah');
                nav.querySelectorAll('a[data-tab]').forEach(function (x) {
                    AKTIF.forEach(function (c) { x.classList.remove(c); });
                    x.classList.add('text-slate-600');
                    x.classList.remove('tab-tuju');
                });
                a.classList.remove('text-slate-600');
                AKTIF.forEach(function (c) { a.classList.add(c); });
                a.classList.add('tab-tuju');

                var bar = document.getElementById('tab-loading');
                if (!bar) { bar = document.createElement('div'); bar.id = 'tab-loading'; document.body.appendChild(bar); }
                bar.style.transition = 'none'; bar.style.width = '0';
                void bar.offsetWidth;
                bar.style.transition = ''; bar.style.width = '85%';
            }

            function pasang(root) {
                (root || document).querySelectorAll(SEL).forEach(function (a) {
                    if (a.dataset.siap) return;
                    a.dataset.siap = '1';
                    ['mouseenter', 'focus', 'touchstart'].forEach(function (ev) {
                        a.addEventListener(ev, function () { prefetch(a); }, { passive: true, once: true });
                    });
                    a.addEventListener('click', function (e) {
                        if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
                        if (a.getAttribute('aria-current')) { e.preventDefault(); return; }
                        mulai(a);
                    });
                });
            }

            pasang();
            // Bersihkan bar kalau halaman dipulihkan dari bfcache (tombol Back)
            window.addEventListener('pageshow', function (e) {
                if (!e.persisted) return;
                var bar = document.getElementById('tab-loading'); if (bar) bar.style.width = '0';
                document.querySelectorAll('nav.sedang-pindah').forEach(function (n) { n.classList.remove('sedang-pindah'); });
            });
        })();
    </script>
@endonce