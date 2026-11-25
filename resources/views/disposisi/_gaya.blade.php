{{-- Gaya bersama modul Disposisi & Kalender Disposisi (tema biru).
     Ditulis sebagai CSS biasa supaya tampil benar walau belum `npm run build`. --}}
@once
    <style>
        /* ===== Tema biru Disposisi ===== */
        .kd-wrap { background: transparent; } /* ikut latar layout */
        .kd-card { background: #fff; border: 1px solid #e8edf5; border-radius: 1rem; box-shadow: 0 1px 2px rgb(15 23 42 / .04); }
        .kd-btn-primary, .kd-btn-soft, .kd-btn-ghost, .kd-btn-outline {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            border-radius: .75rem; padding: .55rem 1rem; font-size: .875rem; font-weight: 600; transition: all .15s;
        }
        .kd-btn-primary { background: #2563eb; color: #fff; box-shadow: 0 4px 12px -4px rgb(37 99 235 / .55); }
        .kd-btn-primary:hover { background: #1d4ed8; }
        .kd-btn-soft { background: #eff6ff; color: #1d4ed8; }
        .kd-btn-soft:hover { background: #dbeafe; }
        .kd-btn-ghost { color: #475569; font-weight: 500; }
        .kd-btn-ghost:hover { background: #f1f5f9; }
        .kd-btn-outline { border: 1px solid #bfdbfe; color: #1d4ed8; background: #fff; }
        .kd-btn-outline:hover { background: #eff6ff; }

        .kd-tabs { display: inline-flex; background: #e9eef8; border-radius: .9rem; padding: .25rem; gap: .25rem; }
        .kd-tab { display: inline-flex; align-items: center; gap: .45rem; padding: .5rem 1rem; border-radius: .7rem;
            font-size: .875rem; font-weight: 600; color: #64748b; white-space: nowrap; transition: all .15s; }
        .kd-tab:hover { color: #1e3a8a; }
        .kd-tab.aktif { background: #2563eb; color: #fff; box-shadow: 0 4px 12px -4px rgb(37 99 235 / .6); }
        .kd-tab .kd-dot { min-width: 1.25rem; height: 1.25rem; padding: 0 .35rem; border-radius: 999px; font-size: .68rem;
            display: inline-flex; align-items: center; justify-content: center; background: #ef4444; color: #fff; }

        .kd-seg { display: inline-flex; gap: .35rem; flex-wrap: wrap; }
        .kd-seg button { padding: .4rem .85rem; border-radius: .6rem; font-size: .8rem; font-weight: 500; color: #475569; background: #eef2fa; }
        .kd-seg button:hover { background: #e0e9fb; color: #1d4ed8; }
        .kd-seg button.aktif { background: #2563eb; color: #fff; }

        .kd-input { width: 100%; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: .75rem; padding: .55rem .85rem; font-size: .875rem; outline: none; }
        .kd-input:focus { border-color: #93c5fd; background: #fff; box-shadow: 0 0 0 3px rgb(191 219 254 / .6); }
        .kd-label { display: block; font-size: .7rem; font-weight: 600; letter-spacing: .02em; color: #64748b; margin-bottom: .3rem; text-transform: uppercase; }

        .kd-chip { display: inline-flex; align-items: center; border-radius: 999px; padding: .1rem .55rem; font-size: .68rem; font-weight: 600; white-space: nowrap; }
        .kd-chip-gray { background: #f1f5f9; color: #475569; }
        .kd-chip-amber { background: #fef3c7; color: #92400e; }
        .kd-chip-red { background: #fee2e2; color: #b91c1c; }
        .kd-chip-violet { background: #ede9fe; color: #6d28d9; }
        .kd-st { display: inline-flex; align-items: center; gap: .35rem; border-radius: 999px; padding: .15rem .6rem; font-size: .7rem; font-weight: 600; white-space: nowrap; }
        .kd-st::before { content: ""; width: .4rem; height: .4rem; border-radius: 999px; background: currentColor; }
        .kd-st-baru { background: #dbeafe; color: #1d4ed8; }
        .kd-st-dibaca { background: #e0f2fe; color: #0369a1; }
        .kd-st-diteruskan { background: #eef2ff; color: #4338ca; }
        .kd-st-selesai { background: #dcfce7; color: #15803d; }
        .kd-st-keluar { background: #f1f5f9; color: #475569; }

        .kd-stat { padding: .9rem 1rem; }
        .kd-stat p:first-child { font-size: .72rem; color: #64748b; }
        .kd-stat p:last-child { font-size: 1.5rem; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; }

        /* Tabel hari ini */
        .kd-table th { font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; font-weight: 600; padding: .7rem 1rem; text-align: left; background: #f8fafc; }
        .kd-table td { padding: .85rem 1rem; vertical-align: top; border-top: 1px solid #f1f5f9; }
        .kd-table tbody tr { cursor: pointer; transition: background .1s; }
        .kd-table tbody tr:hover { background: #f8fbff; }
        .kd-baru-row { background: #f5f9ff; }

        /* Tata letak inti (tidak bergantung build Tailwind) */
        .kd-container { max-width: 1800px; margin: 0 auto; }
        .kd-filter-grid { display: grid; grid-template-columns: 1fr; gap: .75rem; margin-top: .75rem; }
        .kd-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; }
        .kd-ikon { padding: .5rem !important; }
        .kd-kecil { padding: .4rem .8rem !important; font-size: .75rem !important; }
        .kd-modal-box { max-height: 92vh; }
        @media (min-width: 640px) {
            .kd-filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .kd-auto-sm { width: auto !important; }
        }
        @media (min-width: 768px) {
            .kd-filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) auto; display: grid !important; }
            .kd-hanya-hp { display: none !important; }
        }
        @media (max-width: 767px) {
            .kd-tutup { display: none; }
            .kd-hanya-desktop { display: none !important; }
        }
        @media (min-width: 1024px) {
            .kd-layout { grid-template-columns: 18.5rem minmax(0, 1fr); }
            .kd-layout > .kd-utama { order: 2; }
            .kd-layout > aside { order: 1; }
        }
    </style>
@endonce
