<x-app-layout title="Profile">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    {{-- Gaya khusus halaman Profil. Dibatasi lewat prefix .pf- supaya tidak memengaruhi halaman lain.
         Warna & bentuk mengikuti halaman User Manajemen (indigo, rounded-xl, kartu rounded-2xl). --}}
    <style>
        .pf-page { max-width: 80rem; margin: 0 auto; padding: 2rem 1rem; }
        @media (min-width: 640px)  { .pf-page { padding-left: 1.5rem; padding-right: 1.5rem; } }
        @media (min-width: 1024px) { .pf-page { padding-left: 2rem; padding-right: 2rem; } }

        .pf-sub { font-size: .875rem; color: #6b7280; margin-bottom: 1.5rem; }

        .pf-grid { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1024px) { .pf-grid { grid-template-columns: 20rem minmax(0, 1fr); align-items: start; } }
        .pf-stack { display: grid; gap: 1.5rem; min-width: 0; }

        .pf-card { background: var(--ui-surface, #fff); border: 1px solid #f3f4f6; border-radius: 1rem;
                   box-shadow: 0 1px 2px 0 rgb(0 0 0 / .05); padding: 1.5rem; }
        @media (min-width: 640px) { .pf-card { padding: 2rem; } }

        .pf-title { font-size: 1rem; font-weight: 600; color: #111827; }
        .pf-desc  { font-size: .75rem; color: #6b7280; margin-top: .125rem; }
        .pf-body  { margin-top: 1.25rem; }

        /* Ringkasan akun */
        .pf-who { display: flex; align-items: center; gap: .875rem; }
        .pf-avatar { width: 3.5rem; height: 3.5rem; border-radius: 9999px; flex-shrink: 0; display: flex;
                     align-items: center; justify-content: center; background: #e0e7ff; color: #4338ca;
                     font-size: 1.25rem; font-weight: 600; }
        .pf-name  { font-size: 1rem; font-weight: 600; color: #111827; overflow-wrap: anywhere; }
        .pf-mail  { font-size: .875rem; color: #6b7280; overflow-wrap: anywhere; }
        .pf-meta  { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #f3f4f6; display: grid; gap: 1rem; }
        .pf-meta dt { font-size: .75rem; color: #9ca3af; }
        .pf-meta dd { font-size: .875rem; color: #1f2937; margin-top: .125rem; overflow-wrap: anywhere; }
        .pf-badge { display: inline-block; font-size: .75rem; font-weight: 600; padding: .125rem .5rem; border-radius: .25rem; }
        .pf-badge--user  { background: #d1fae5; color: #047857; }
        .pf-badge--tu    { background: #e0f2fe; color: #0369a1; }
        .pf-badge--admin { background: #f3e8ff; color: #7e22ce; }
        .pf-badge--super { background: #ffe4e6; color: #be123c; }
        .pf-badge--tusekr { background: #cffafe; color: #0e7490; }
        .pf-badge--eselon { background: #fef3c7; color: #b45309; }

        /* Form */
        .pf-form   { display: grid; gap: 1.25rem; }
        .pf-fields { display: grid; gap: 1.25rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 640px) { .pf-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .pf-span   { grid-column: 1 / -1; }
        @media (min-width: 640px) { .pf-half { grid-column: 1 / 2; } }

        .pf-label { display: block; font-size: .875rem; font-weight: 500; color: #374151; margin-bottom: .25rem; }
        .pf-input { width: 100%; padding: .625rem 1rem; border-radius: .75rem; border: 1px solid #e5e7eb;
                    background: #fff; font: inherit; font-size: .875rem; color: #111827; transition: border-color .15s, box-shadow .15s; }
        .pf-input::placeholder { color: #9ca3af; }
        .pf-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgb(99 102 241 / .2); }
        .pf-input.is-error { border-color: #f43f5e; }
        .pf-error { font-size: .75rem; color: #f43f5e; margin-top: .25rem; }
        .pf-hint  { font-size: .75rem; color: #9ca3af; margin-top: .25rem; }

        .pf-actions { display: flex; align-items: center; gap: .75rem; padding-top: 1.25rem; border-top: 1px solid #f3f4f6; }
        .pf-btn { display: inline-flex; align-items: center; justify-content: center; padding: .625rem 1.5rem;
                  border-radius: .75rem; border: 1px solid transparent; font: inherit; font-size: .875rem; font-weight: 500;
                  cursor: pointer; transition: background .15s, box-shadow .15s; }
        .pf-btn:focus-visible { outline: 2px solid #6366f1; outline-offset: 2px; }
        .pf-btn--primary { background: #4f46e5; color: #fff; box-shadow: 0 1px 2px 0 rgb(0 0 0 / .05); }
        .pf-btn--primary:hover { background: #4338ca; }
        .pf-saved { font-size: .875rem; color: #059669; }

        .pf-warn { font-size: .875rem; color: #1f2937; background: #fffbeb; border: 1px solid #fde68a;
                   border-radius: .75rem; padding: .75rem 1rem; margin-top: .5rem; }
        .pf-link { background: none; border: 0; padding: 0; font: inherit; color: #4f46e5; text-decoration: underline; cursor: pointer; }
        .pf-ok   { font-size: .875rem; color: #059669; margin-top: .5rem; }

    </style>

    <div class="pf-page">
        <p class="pf-sub">Kelola data akun dan email Anda. Password hanya bisa diubah oleh admin melalui User Manajemen.</p>

        <div class="pf-grid">
            {{-- Ringkasan akun --}}
            <aside class="pf-card">
                @php
                    $inisial = mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1));
                    $badge = match ($user->role) {
                        'super_admin' => 'pf-badge--super',
                        'admin' => 'pf-badge--admin',
                        'tu_sekretariat' => 'pf-badge--tusekr',
                        'tu_unit', 'tu' => 'pf-badge--tu',
                        'eselon2' => 'pf-badge--eselon',
                        default => 'pf-badge--user',
                    };
                @endphp
                <div class="pf-who">
                    <div class="pf-avatar" aria-hidden="true">{{ $inisial }}</div>
                    <div style="min-width:0">
                        <div class="pf-name">{{ $user->name }}</div>
                        <div class="pf-mail">{{ $user->email }}</div>
                    </div>
                </div>

                <dl class="pf-meta">
                    @if ($user->username)
                        <div>
                            <dt>Username</dt>
                            <dd>{{ $user->username }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt>Hak akses</dt>
                        <dd><span class="pf-badge {{ $badge }}">{{ $user->roleLabel() }}</span></dd>
                    </div>
                    <div>
                        <dt>Unit</dt>
                        <dd>
                            @if ($user->unit)
                                {{ $user->unit->kode }}<br>
                                <span style="color:#6b7280">{{ $user->unit->nama }}</span>
                            @else
                                <span style="color:#9ca3af">Semua unit</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </aside>

            <div class="pf-stack">
                <section class="pf-card">
                    @include('profile.partials.update-profile-information-form')
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
