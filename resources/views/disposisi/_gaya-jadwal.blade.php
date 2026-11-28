{{-- Gaya khusus tab Jadwal Pegawai (kelas jp-* dan db-*). Dipakai: @include('disposisi._gaya-jadwal') --}}
@once
<style>
    .jp-sub {
        font-size: .75rem;
        color: #9ca3af;
        margin-top: .15rem;
    }

    .jp-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    @media (min-width: 768px) {
        .jp-stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .jp-orang {
        display: flex;
        align-items: center;
        gap: .85rem;
    }

    .jp-avatar {
        height: 2.75rem;
        width: 2.75rem;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .jp-nama {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .jp-ket {
        font-size: .8rem;
        color: #64748b;
    }

    .jp-merah {
        color: #b91c1c;
        font-weight: 600;
    }

    /* ====== Dashboard agenda ====== */
    .db-atas {
        display: grid;
        gap: 1rem;
    }

    @media (min-width: 1024px) {
        .db-atas {
            grid-template-columns: 1fr 1fr;
        }
    }

    .db-bawah {
        display: grid;
        gap: 1rem;
    }

    @media (min-width: 1024px) {
        .db-bawah {
            grid-template-columns: 1fr 1fr;
        }
    }

    .db-panel {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 1.25rem;
        padding: 1.25rem;
        min-width: 0;
    }

    .db-panel-kepala {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .db-panel-judul {
        font-size: 1.15rem;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .db-panel-judul small {
        font-size: .8rem;
        font-weight: 600;
        color: #6b7280;
        background: #f3f4f6;
        border-radius: 999px;
        padding: .1rem .55rem;
    }

    .db-panel-judul .db-thn {
        color: #9ca3af;
        font-weight: 600;
    }

    .db-kontrol {
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .db-ikon-btn {
        height: 2rem;
        width: 2rem;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .db-ikon-btn:hover {
        background: #f3f4f6;
    }

    .db-ikon-btn svg {
        height: .9rem;
        width: .9rem;
    }

    .db-pilih {
        appearance: none;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 999px;
        padding: .35rem 1.9rem .35rem .85rem;
        font-size: .8rem;
        color: #374151;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%236b7280'%3E%3Cpath d='M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right .55rem center;
        background-size: 1rem;
    }

    /* Kalender */
    .db-kal {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: .4rem;
    }

    .db-kal-hari {
        text-align: center;
        font-size: .72rem;
        font-weight: 600;
        color: #6b7280;
        padding: .35rem 0;
        border-radius: .6rem;
    }

    .db-kal-hari.db-kini {
        background: #e5e7eb;
        color: #111827;
    }

    .db-sel {
        position: relative;
        aspect-ratio: 1 / 1;
        max-height: 3.6rem;
        border: 0;
        border-radius: .8rem;
        background: #f3f4f6;
        color: #111827;
        font-size: .9rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: .35rem;
        transition: background .1s;
    }

    .db-sel:hover {
        background: #e5e7eb;
    }

    .db-sel.db-luar {
        color: #c4c8d0;
        background: #f7f8fa;
    }

    .db-sel.db-ini {
        background: #111827;
        color: #fff;
    }

    .db-sel.db-dipilih {
        box-shadow: 0 0 0 2px #2563eb;
    }

    .db-sel.db-ada::before {
        content: "";
        position: absolute;
        top: .45rem;
        left: 50%;
        transform: translateX(-50%);
        height: .28rem;
        width: .28rem;
        border-radius: 999px;
        background: #111827;
    }

    .db-sel.db-ini.db-ada::before {
        background: #fff;
    }

    .db-sel.db-ada-telat::before {
        background: #dc2626;
    }

    .db-sel:focus-visible,
    .db-ikon-btn:focus-visible,
    .db-pilih:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    /* Daftar kegiatan */
    .db-gulung {
        max-height: 28rem;
        overflow-y: auto;
        display: grid;
        gap: .6rem;
        padding-right: .15rem;
        align-content: start;
    }

    .db-gulung.db-pendek {
        max-height: 22rem;
    }

    .db-item {
        display: flex;
        gap: .9rem;
        align-items: center;
        padding: .8rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        background: #fff;
        text-decoration: none;
        transition: border-color .1s, background .1s;
    }

    .db-item:hover,
    .db-item:focus-visible {
        border-color: #cbd5e1;
        background: #fafbfc;
        outline: none;
    }

    .db-item-tgl {
        width: 3rem;
        text-align: center;
        border-right: 1px solid #e5e7eb;
        padding-right: .8rem;
        flex-shrink: 0;
        line-height: 1.1;
    }

    .db-item-tgl b {
        display: block;
        font-size: 1.25rem;
        color: #111827;
        font-variant-numeric: tabular-nums;
    }

    .db-item-tgl span {
        font-size: .72rem;
        color: #9ca3af;
    }

    .db-item-isi {
        min-width: 0;
        display: grid;
        gap: .35rem;
    }

    .db-item-judul {
        font-weight: 700;
        color: #111827;
        font-size: .95rem;
        line-height: 1.3;
    }

    .db-item-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .4rem .6rem;
        font-size: .72rem;
        color: #4b5563;
    }

    .db-meta-ik {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border: 1px solid #eef0f4;
        border-radius: .5rem;
        padding: .1rem .45rem;
    }

    .db-meta-ik svg {
        height: .8rem;
        width: .8rem;
        color: #6b7280;
    }

    .db-pil {
        font-size: .7rem;
        font-weight: 600;
        border-radius: .4rem;
        padding: .12rem .5rem;
        border: 1px solid transparent;
    }

    .db-pil-biru {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .db-pil-merah {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .db-pil-kuning {
        background: #ffedd5;
        color: #c2410c;
        border-color: #fed7aa;
    }

    .db-pil-hijau {
        background: #dcfce7;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .db-pil-ungu {
        background: #ede9fe;
        color: #6d28d9;
        border-color: #ddd6fe;
    }

    .db-kosong {
        text-align: center;
        color: #6b7280;
        font-size: .85rem;
        padding: 2.5rem 1rem;
    }

    .db-lepas {
        border: 0;
        background: none;
        color: #1d4ed8;
        font-size: .75rem;
        cursor: pointer;
        text-decoration: underline;
        padding: 0;
    }

    .db-bawah .db-panel-telat .db-panel-judul {
        color: #b91c1c;
    }

    .db-bawah .db-panel-belum .db-panel-judul {
        color: #92400e;
    }

    /* Matriks */
    .jp-scroll {
        overflow-x: auto;
    }

    .jp-matriks {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        font-size: .8rem;
    }

    .jp-matriks th {
        padding: .6rem .4rem;
        background: #f8fafc;
        color: #64748b;
        font-size: .68rem;
        font-weight: 600;
        text-align: center;
        border-bottom: 1px solid #e8edf5;
        white-space: nowrap;
    }

    .jp-matriks th.jp-libur,
    .jp-matriks td.jp-libur {
        background: #f8fafc;
    }

    .jp-matriks td {
        padding: .45rem .3rem;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
    }

    .jp-matriks .jp-nm {
        position: sticky;
        left: 0;
        z-index: 1;
        background: #fff;
        text-align: left;
        padding: .5rem .9rem;
        min-width: 11rem;
        border-right: 1px solid #f1f5f9;
    }

    .jp-matriks th.jp-nm {
        background: #f8fafc;
    }

    .jp-matriks .jp-nm a {
        font-weight: 600;
        color: #0f172a;
    }

    .jp-matriks .jp-nm a:hover {
        color: #1d4ed8;
    }

    .jp-matriks .jp-nm small {
        display: block;
        font-size: .68rem;
        color: #94a3b8;
    }

    .jp-sel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.7rem;
        height: 1.7rem;
        padding: 0 .35rem;
        border-radius: .5rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .jp-sel-hijau {
        background: #dcfce7;
        color: #166534;
    }

    .jp-sel-kuning {
        background: #fef3c7;
        color: #92400e;
    }

    .jp-sel-merah {
        background: #fee2e2;
        color: #b91c1c;
    }

    .jp-sel-abu {
        background: #f1f5f9;
        color: #64748b;
    }

    .jp-legenda {
        display: flex;
        flex-wrap: wrap;
        gap: .9rem;
        font-size: .75rem;
        color: #64748b;
        padding: .8rem 1rem;
    }

    .jp-legenda span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .jp-legenda i {
        display: inline-block;
        width: .8rem;
        height: .8rem;
        border-radius: .25rem;
    }

    /* Hari ini: orange, sama seperti tanggal hari ini di tab Daftar */
    .db-sel.db-ini {
        background: #ea580c;
        color: #fff;
    }

    .db-sel.db-ini:hover {
        background: #c2410c;
    }

    .db-sel.db-ini.db-ada::before {
        background: #fff;
    }

    .db-kal-hari.db-kini {
        background: #ffedd5;
        color: #c2410c;
    }

    .db-item.db-hari-ini .db-item-tgl b,
    .db-item.db-hari-ini .db-item-tgl span {
        color: #ea580c;
    }

    .db-item.db-hari-ini .db-item-tgl b {
        font-weight: 800;
    }

/* Link zoom / daring di kartu kegiatan */
.db-zoom { color: #1d4ed8; cursor: pointer; max-width: 100%; }
.db-zoom:hover { background: #eff6ff; border-color: #bfdbfe; }
.db-zoom svg { color: #1d4ed8; }
.db-zoom-teks { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 14rem; }
.db-zoom:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
</style>
@endonce
