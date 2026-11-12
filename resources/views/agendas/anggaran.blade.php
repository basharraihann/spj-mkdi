<x-app-layout title="Anggaran & Memo">
    <x-slot name="header">
        <a href="{{ route('agendas.show', $agenda) }}" class="ag-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Detail agenda
        </a>
        <h2>Anggaran & Memo</h2>
        <p class="ag-subtitle">{{ $agenda->uraian_kegiatan }}</p>
    </x-slot>

    @php
        $rp = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

        // Status kosong dianggap PNS, sama seperti halaman show agenda.
        $pesertaStatus = $agenda->pegawai->map(fn($p) => $p->status_kepegawaian ?? 'PNS');
        $jmlPns = $pesertaStatus->filter(fn($s) => $s === 'PNS')->count();
        $jmlNonPns = $pesertaStatus->filter(fn($s) => $s === 'Non PNS')->count();

        $check = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
    @endphp

    <div class="ag-page">

        {{-- Stepper: di HP hanya label langkah aktif yang tampil --}}
        <nav class="ag-stepper" aria-label="Langkah pengisian">
            <ol>
                <li class="ag-step is-done">
                    <a href="{{ route('agendas.edit', $agenda) }}">
                        <span class="ag-dot">{!! $check !!}</span>
                        <span class="ag-label">Detail Agenda & Peserta</span>
                    </a>
                </li>
                <li class="ag-line is-done" aria-hidden="true"></li>
                <li class="ag-step is-done">
                    <a href="{{ route('agendas.peserta', $agenda) }}">
                        <span class="ag-dot">{!! $check !!}</span>
                        <span class="ag-label">Rincian Biaya</span>
                    </a>
                </li>
                <li class="ag-line is-done" aria-hidden="true"></li>
                <li class="ag-step is-current" aria-current="step">
                    <span class="ag-dot">3</span>
                    <span class="ag-label">Anggaran & Memo</span>
                </li>
            </ol>
        </nav>

        @if ($errors->any())
            <div class="ag-alert" role="alert" style="margin-bottom:16px">
                <ul>
                    @foreach (collect($errors->all())->unique() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('agendas.anggaran.store', $agenda) }}" method="POST" id="anggaran-form"
            class="spj-form">
            @csrf

            {{-- Urutan DOM: ringkasan dulu (muncul di atas pada HP). Di desktop jadi kolom kanan yang menempel. --}}
            <div class="ag-grid">

                {{-- ===== Ringkasan ===== --}}
                <aside class="ag-aside" aria-label="Ringkasan agenda">

                    <details class="ag-card ag-agenda" id="ag-agenda" open>
                        <summary>
                            <div style="min-width:0">
                                <div class="ag-agenda-eyebrow">Agenda</div>
                                <div class="ag-agenda-title">{{ $agenda->uraian_kegiatan }}</div>
                            </div>
                            <svg class="ag-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>

                        <dl class="ag-meta">
                            <div>
                                <dt>Tujuan</dt>
                                <dd>{{ $agenda->tujuan }}</dd>
                            </div>
                            <div>
                                <dt>Tanggal</dt>
                                <dd>{{ $agenda->tanggal_mulai->translatedFormat('d M Y') }} –
                                    {{ $agenda->tanggal_selesai->translatedFormat('d M Y') }}</dd>
                            </div>
                            @if ($agenda->nomor_st)
                                <div>
                                    <dt>No. ST</dt>
                                    <dd class="break-all">{{ $agenda->nomor_st }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt>Peserta</dt>
                                <dd>
                                    <span class="ag-pill ag-pill--pns">{{ $jmlPns }} PNS</span>
                                    @if ($jmlNonPns > 0)
                                        <span class="ag-pill ag-pill--non">{{ $jmlNonPns }} Non PNS</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </details>

                    <div class="ag-total">
                        <div class="ag-total-label">Total biaya agenda</div>
                        <div class="ag-total-value">{{ $rp($totalBiaya) }}</div>

                        <div class="ag-total-split">
                            <div>
                                <span>Biaya PNS</span>
                                <strong>{{ $rp($totalAsn) }}</strong>
                            </div>
                            <div>
                                <span>Biaya Non PNS</span>
                                <strong>{{ $rp($totalNonAsn) }}</strong>
                            </div>
                        </div>

                        <a href="{{ route('agendas.peserta', $agenda) }}" class="ag-total-link">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Edit rincian biaya
                        </a>
                    </div>
                </aside>

                {{-- ===== Form ===== --}}
                <div class="ag-main">

                    @include('agendas.partials.administrasi-anggaran', ['agenda' => $agenda])

                    {{-- Action bar: di HP menempel di atas bottom nav --}}
                    <div class="ag-actionbar">
                        <div class="ag-actionbar-inner">
                            <a href="{{ route('agendas.show', $agenda) }}" class="ag-btn ag-btn-ghost">Batal</a>

                            <div class="ag-actionbar-total" aria-hidden="true">
                                <span>Total biaya</span>
                                <strong>{{ $rp($totalBiaya) }}</strong>
                            </div>

                            <button type="submit" id="btn-simpan-anggaran" class="ag-btn ag-btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="ag-btn-long">Simpan Anggaran & Memo</span>
                                <span class="ag-btn-short">Simpan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function () {
            // Info agenda: terbuka di desktop, terlipat di HP supaya form langsung kelihatan.
            const info = document.getElementById('ag-agenda');
            const desktop = window.matchMedia('(min-width: 1024px)');
            const sync = () => { info.open = desktop.matches; };
            sync();
            (desktop.addEventListener ? desktop.addEventListener('change', sync) : desktop.addListener(sync));

            // Cegah dobel klik simpan.
            const form = document.getElementById('anggaran-form');
            const btn = document.getElementById('btn-simpan-anggaran');
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.querySelector('.ag-btn-long').textContent = 'Menyimpan…';
                btn.querySelector('.ag-btn-short').textContent = 'Menyimpan…';
            });
            // Tombol "kembali" browser (bfcache) jangan menyisakan tombol yang terkunci.
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    btn.disabled = false;
                    btn.querySelector('.ag-btn-long').textContent = 'Simpan Anggaran & Memo';
                    btn.querySelector('.ag-btn-short').textContent = 'Simpan';
                }
            });
        })();
    </script>
</x-app-layout>
