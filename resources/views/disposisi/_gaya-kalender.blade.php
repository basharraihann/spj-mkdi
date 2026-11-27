<style>
    /* ====== Empat warna: dipakai kartu kalender, titik tampilan Daftar, dan sidebar ====== */
    .w-baru    { --c-bg: #eef2ff; --c-bd: #a5b4fc; --c-tx: #3730a3; --c-ac: #c7d2fe; --c-dot: #6366f1; }
    .w-dibaca  { --c-bg: #f0f9ff; --c-bd: #7dd3fc; --c-tx: #075985; --c-ac: #bae6fd; --c-dot: #38bdf8; }
    .w-selesai { --c-bg: #f0fdf4; --c-bd: #86efac; --c-tx: #166534; --c-ac: #bbf7d0; --c-dot: #22c55e; }
    .w-umum    { --c-bg: #f8fafc; --c-bd: #cbd5e1; --c-tx: #475569; --c-ac: #e2e8f0; --c-dot: #94a3b8; }

    /* ====== Layout: sidebar kecil (kiri) + kalender (kanan) ====== */
    .kal-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; }
    .kd-container { max-width: 1280px; }

    @media (min-width: 1024px) {
        .kal-layout { grid-template-columns: 270px minmax(0, 1fr); align-items: start; }
        .kal-side { position: sticky; top: 1rem; }
    }

    /* ====== Sidebar: Jadwal Hari Ini ====== */
    .rp-list { display: flex; flex-direction: column; gap: .6rem; }

    .rp-item {
        width: 100%; text-align: left; cursor: pointer;
        border-radius: .75rem; border: 1px solid #e8eef7; border-left: 4px solid var(--c-bd, #cbd5e1);
        background: #fff; padding: .65rem .75rem .65rem .85rem;
        transition: background .15s, box-shadow .15s;
    }
    .rp-item:hover { background: #f8fbff; box-shadow: 0 1px 4px rgb(15 23 42 / .08); }

    .rp-jam { font-size: .72rem; font-weight: 700; color: #1d4ed8; font-variant-numeric: tabular-nums; }

    .rp-judul {
        margin-top: .15rem; font-size: .8rem; font-weight: 600; color: #0f172a; line-height: 1.25;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }

    .rp-item .rp-meta, .rp-item .rp-tempat, .rp-item .rp-nama, .rp-item .rp-link {
        display: block; margin-top: .1rem; font-size: .68rem; line-height: 1.3;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .rp-item .rp-meta { color: #64748b; }
    .rp-item .rp-tempat { color: #94a3b8; }
    .rp-item .rp-nama { color: #475569; }
    .rp-item a.rp-link, .rp-item a.rp-link:visited { font-weight: 600; color: #2563eb; }
    .rp-item a.rp-link:hover { text-decoration: underline; }

    /* ====== Toolbar ====== */
    .kb {
        border: 1px solid #e2e8f0; background: #fff; border-radius: .6rem; height: 1.8rem; padding: 0 .7rem;
        font-size: .72rem; font-weight: 500; color: #334155;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .kb:hover { background: #f8fafc; }

    #k-view.kd-seg { display: inline-flex; gap: 2px; padding: 3px; border: 1px solid #e2e8f0; border-radius: .7rem; background: #fff; }
    #k-view.kd-seg button {
        padding: .3rem .7rem; border-radius: .5rem; font-size: .62rem; font-weight: 600;
        letter-spacing: .04em; text-transform: uppercase; color: #64748b; background: transparent;
    }
    #k-view.kd-seg button.aktif { background: #0f172a; color: #fff; }

    /* ====== Avatar (sidebar & event kalender) ====== */
    .av {
        width: 22px; height: 22px; border-radius: 999px; border: 2px solid #fff; margin-left: -7px;
        object-fit: cover; background: #e2e8f0; color: #334155; font-size: .6rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center; flex: none; line-height: 1;
    }
    .av:first-child { margin-left: 0; }
    .av-n { background: #0f172a; color: #fff; }
    .av-row { display: flex; align-items: center; margin-top: .3rem; }

    /* ====== FullCalendar: dasar ====== */
    #kal {
        --fc-border-color: #eef2f7; --fc-today-bg-color: #f8fbff; --fc-page-bg-color: #fff;
        --fc-neutral-bg-color: #f8fafc; --fc-now-indicator-color: #2563eb; font-size: 11px;
    }
    #kal .fc-scrollgrid { border-radius: .75rem; overflow: hidden; }
    #kal .fc-event { cursor: pointer; }
    #kal a.fc-daygrid-day-number, #kal a.fc-col-header-cell-cushion { text-decoration: none; }

    /* Header kolom */
    #kal .fc-col-header-cell-cushion { padding: .2rem .15rem; font-weight: 600; color: #0f172a; font-size: .85rem; }
    .dh { display: inline-flex; flex-direction: column; align-items: center; padding: .15rem .5rem; border-radius: .8rem; }
    .dh-n { font-size: .56rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; font-weight: 500; }
    .dh-d { font-size: .85rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
    #kal .fc-day-today .dh { background: #dbeafe; }
    #kal .fc-day-today .dh-n, #kal .fc-day-today .dh-d { color: #1d4ed8; }

    /* Tampilan Bulan */
    #kal .fc-daygrid-day-frame { min-height: 78px; }
    #kal .fc-daygrid-day-top { justify-content: center; }
    #kal .fc-daygrid-day-number { font-size: .8rem; font-weight: 600; color: #1e293b; padding: .25rem 0 .05rem; font-variant-numeric: tabular-nums; }
    #kal .fc-day-other .fc-daygrid-day-number { color: #cbd5e1; }
    #kal .fc-day-today .fc-daygrid-day-number { color: #2563eb; }
    #kal .fc-daygrid-day.fc-day-today { background: #f5f9ff; box-shadow: inset 0 3px 0 #2563eb; }
    #kal .fc-daygrid-event { margin: 3px 6px 0; padding: 0; border: 0; white-space: normal; }
    #kal .fc-daygrid-dot-event .fc-daygrid-event-dot { display: none; }
    #kal .fc-daygrid-more-link { font-size: .72rem; color: #2563eb; font-weight: 600; margin: 3px 8px; }

    /* Tampilan Hari / Minggu */
    #kal .fc-timegrid-slot { height: 2rem; }
    #kal .fc-timegrid-slot-label-cushion, #kal .fc-timegrid-axis-cushion { font-size: .7rem; color: #94a3b8; }
    #kal .fc-timegrid-event { padding: 0; }
    #kal .fc-timegrid-event-harness-inset .fc-timegrid-event { box-shadow: none; }
    #kal .fc-timegrid-col-frame { background-image: repeating-linear-gradient(135deg, transparent 0 10px, rgba(148, 163, 184, .10) 10px 20px); }

    /* Tampilan Daftar */
    #kal .fc-list-day-cushion { background: #f5f9ff; }
    #kal .fc-list-event:hover td { background: #f8fbff; }
    #kal .fc-list-event .fc-list-event-dot { border-color: var(--c-dot); }
    #kal .fc-list-event.ev { background: transparent !important; border: 0 !important; color: #0f172a !important; box-shadow: none; }

    /* Tampilan Tahun */
    #kal .fc-multimonth-title { font-size: .9rem; color: #1e3a8a; }
    #kal .fc-multimonth .fc-daygrid-day-number { font-size: .72rem; padding: .2rem 0; }
    #kal .fc-multimonth .fc-daygrid-day-frame { min-height: 0; }
    #kal .fc-multimonth .fc-daygrid-day.fc-day-today { box-shadow: none; }

    /* ====== Kartu event ====== */
    #kal .fc-daygrid-event.ev, #kal .fc-timegrid-event.ev {
        background: var(--c-bg) !important; border: 1px solid var(--c-bd) !important;
        color: var(--c-tx) !important; border-radius: 12px; box-shadow: none;
    }
    #kal .ev .fc-event-main { color: inherit !important; padding: 0; }

    .ev-card { display: flex; flex-direction: column; gap: .15rem; height: 100%; min-width: 0; overflow: hidden; padding: .22rem .32rem; }
    .ev-top { display: flex; align-items: center; justify-content: space-between; gap: .4rem; }
    .ev-ic { width: 11px; height: 11px; flex: none; }
    .ev-ttl {
        font-size: .62rem; font-weight: 600; line-height: 1.15;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .ev-jam { margin-top: auto; font-size: .56rem; font-variant-numeric: tabular-nums; opacity: .9; }

    .ev .av-row { margin-top: 0; }
    .ev .av { width: 14px; height: 14px; font-size: .46rem; margin-left: -5px; border-color: var(--c-bg); }
    .ev .av-n { width: auto; min-width: 22px; padding: 0 5px; background: var(--c-ac); color: var(--c-tx); font-size: .58rem; }

    /* ====== Mobile ====== */
    @media (max-width: 767px) {
        #kal .fc-daygrid-day-frame { min-height: 72px; }
        #kal .fc-daygrid-day-number { font-size: .9rem; padding: .35rem 0 .1rem; }
        #kal .fc-daygrid-event { margin: 1px 2px 0; padding: 0; }
        #kal .fc-col-header-cell-cushion { font-size: .68rem; padding: .55rem 0; }
        .dh { padding: .25rem .4rem; }
        #kal .av { width: 16px; height: 16px; font-size: .5rem; margin-left: -5px; }
    }

    /* ====== Popup (modern, netral) ====== */
    #modal-dispo { backdrop-filter: blur(3px); }
    #modal-dispo[hidden] { display: none; }

    .pp-box { max-height: 92vh; display: flex; flex-direction: column; overflow: hidden !important; }
    .pp-accent { display: none; }
    .pp-head { flex: none; display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; padding: 1.2rem 1.4rem .9rem; }
    .pp-title { font-size: 1.05rem; font-weight: 700; line-height: 1.3; color: #0f172a; }
    .pp-sub { margin-top: .3rem; font-size: .75rem; color: #64748b; }
    .pp-body { flex: 1 1 auto; overflow-y: auto; padding: .25rem 1.4rem 1.1rem; display: flex; flex-direction: column; gap: 1rem; }

    .pp-grid { display: grid; grid-template-columns: 1fr; gap: .6rem; }
    @media (min-width: 560px) { .pp-grid { grid-template-columns: 1fr 1fr; } }

    .pp-info { display: flex; gap: .7rem; align-items: center; padding: .65rem .8rem; border: 1px solid #e2e8f0; border-radius: .8rem; background: #fff; min-width: 0; }
    .pp-info.full { grid-column: 1 / -1; }
    .pp-ic { flex: none; width: 2rem; height: 2rem; border-radius: .6rem; display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #475569; }
    .pp-ic svg { width: 1rem; height: 1rem; display: block; flex: none; }
    .pp-lbl { font-size: .62rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; line-height: 1.2; }
    .pp-val { margin-top: .1rem; font-size: .82rem; font-weight: 600; color: #0f172a; line-height: 1.35; word-break: break-word; }
    .pp-val small { display: block; font-weight: 500; font-size: .72rem; color: #64748b; }

    .pp-zoom {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .65rem .8rem; border-radius: .8rem; text-decoration: none;
        background: #fff; border: 1px solid #e2e8f0; color: #0f172a; transition: border-color .15s, background .15s;
    }
    .pp-zoom:hover { border-color: #94a3b8; background: #f8fafc; }
    .pp-zoom .t { min-width: 0; }
    .pp-zoom b { display: block; font-size: .82rem; font-weight: 600; line-height: 1.25; }
    .pp-zoom .h { display: block; margin-top: .1rem; font-size: .72rem; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pp-zoom .go { flex: none; background: #0f172a; color: #fff; border-radius: .55rem; padding: .35rem .75rem; font-size: .72rem; font-weight: 600; }

    .pp-file {
        display: inline-flex; align-items: center; gap: .4rem; align-self: flex-start; padding: .35rem .7rem;
        border: 1px solid #e2e8f0; background: #fff; color: #334155; border-radius: 999px;
        font-size: .72rem; font-weight: 600; text-decoration: none;
    }
    .pp-file:hover { background: #f8fafc; border-color: #94a3b8; }
    .pp-file svg { width: .9rem; height: .9rem; display: block; flex: none; }

    .pp-sec { font-size: .66rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #64748b; margin-bottom: .55rem; display: flex; align-items: center; gap: .4rem; }
    .pp-sec i { font-style: normal; background: #f1f5f9; color: #475569; border-radius: 999px; padding: 0 .45rem; font-size: .62rem; letter-spacing: 0; }

    .pp-tl { list-style: none; margin: 0; padding: 0; }
    .pp-tl li { padding: 0 0 .55rem 0; }
    .pp-tl li:last-child { padding-bottom: 0; }

    .pp-card { border: 1px solid #e2e8f0; border-radius: .8rem; padding: .6rem .8rem; background: #fff; }
    .pp-card.saya { border-color: #94a3b8; background: #f8fafc; }
    .pp-flow { font-size: .78rem; color: #475569; }
    .pp-flow b { color: #0f172a; }
    .pp-tag { display: inline-block; border-radius: .5rem; background: #f1f5f9; color: #334155; font-size: .68rem; font-weight: 500; padding: .12rem .5rem; }
    .pp-note { margin-top: .4rem; font-size: .78rem; color: #334155; white-space: pre-line; }
    .pp-due { margin-top: .35rem; font-size: .68rem; color: #64748b; }
    .pp-due.telat { color: #dc2626; font-weight: 600; }
    .pp-ikut { margin-left: .3rem; font-size: .6rem; font-weight: 700; color: #475569; background: #e2e8f0; border-radius: 999px; padding: .08rem .45rem; }
    .pp-ikut.anda { background: #0f172a; color: #fff; }

    /* Badge status di popup dibuat netral (kecuali selesai) */
    #m-chip .kd-st:not(.kd-st-selesai), .pp-tl .kd-st:not(.kd-st-selesai) { background: #f1f5f9 !important; color: #475569 !important; }

    .pp-foot { flex: none; border-top: 1px solid #eef2f7; background: #f8fafc; padding: .9rem 1.4rem 1rem; }
    .pp-foot textarea { border-radius: .75rem; }
</style>
