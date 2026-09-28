{{-- Background lavender + gunung kabur (SVG murni, tanpa file gambar) --}}
<svg {{ $attributes }} viewBox="0 0 1440 800" preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#f9fbff" />
            <stop offset="0.55" stop-color="#eef2fe" />
            <stop offset="1" stop-color="#dde4fa" />
        </linearGradient>
        <linearGradient id="fog" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity="0" />
            <stop offset="1" stop-color="#fff" stop-opacity="0.55" />
        </linearGradient>
        <filter id="blur" x="-10%" y="-10%" width="120%" height="120%">
            <feGaussianBlur stdDeviation="9" />
        </filter>
    </defs>

    <rect width="1440" height="800" fill="url(#bg)" />

    <g filter="url(#blur)">
        {{-- gunung belakang --}}
        <path
            d="M-40 800 L-40 600 C120 560 200 500 300 470 C380 440 440 480 520 520 C600 560 680 500 760 470 C840 440 900 500 980 540 C1080 590 1180 530 1260 500 C1340 470 1400 500 1480 520 L1480 800 Z"
            fill="#d3dbf6" opacity="0.6" />
        {{-- gunung kiri --}}
        <path
            d="M-40 800 L-40 650 C90 620 160 560 240 500 C290 460 330 440 380 420 C430 440 470 500 560 560 C640 610 700 700 760 800 Z"
            fill="#cbd4f4" opacity="0.6" />
        {{-- gunung kanan --}}
        <path d="M680 800 C780 700 880 620 960 560 C1040 500 1100 470 1180 500 C1260 530 1340 560 1480 610 L1480 800 Z"
            fill="#ccd5f5" opacity="0.65" />
        {{-- sorotan lembut --}}
        <path d="M240 500 C300 480 350 470 380 420 C400 470 420 520 470 560 C400 540 320 520 240 500 Z" fill="#fff"
            opacity="0.5" />
        <path d="M960 560 C1020 520 1080 490 1180 500 C1120 540 1050 570 960 560 Z" fill="#fff" opacity="0.45" />
    </g>

    {{-- kabut di bawah --}}
    <rect y="480" width="1440" height="320" fill="url(#fog)" />
</svg>