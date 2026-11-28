{{-- Lonceng notifikasi. Pakai: <x-lonceng /> (taruh di navbar). Butuh Alpine.js dan meta csrf-token. --}}
<div class="relative"
    x-data="{
        buka: false, belum: 0, daftar: [], memuat: true,
        async muat() {
            try {
                const r = await fetch('{{ route('notifikasi.data') }}', { headers: { 'Accept': 'application/json' } });
                if (!r.ok) return;
                const d = await r.json();
                this.belum = d.belum;
                this.daftar = d.daftar;
            } catch (e) {} finally { this.memuat = false; }
        },
        async bacaSemua() {
            await fetch('{{ route('notifikasi.baca-semua') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            this.muat();
        }
    }"
    x-init="muat(); setInterval(() => muat(), 30000)"
    @click.outside="buka = false" @keydown.escape.window="buka = false">

    <button type="button" @click="buka = !buka" aria-label="Notifikasi" :aria-expanded="buka"
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 8a6 6 0 1112 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 003.4 0"/>
        </svg>
        <span x-show="belum > 0" x-text="belum > 9 ? '9+' : belum" x-cloak
            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-semibold text-white"></span>
    </button>

    <div x-show="buka" x-cloak x-transition.opacity
        class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <p class="font-semibold text-gray-900">Notifikasi</p>
            <button type="button" x-show="belum > 0" @click="bacaSemua()"
                class="text-xs text-blue-700 underline">Tandai semua dibaca</button>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <template x-for="n in daftar" :key="n.id">
                <a :href="n.url" class="flex gap-3 border-b border-gray-50 px-4 py-3 hover:bg-gray-50"
                    :class="n.dibaca ? '' : 'bg-blue-50/50'">
                    <span class="mt-1.5 h-2 w-2 flex-none rounded-full" :class="n.dibaca ? 'bg-transparent' : 'bg-blue-600'"></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-gray-900" x-text="n.judul"></span>
                        <span class="line-clamp-2 block text-sm text-gray-600" x-text="n.pesan"></span>
                        <span class="mt-0.5 block text-xs text-gray-400" x-text="n.waktu"></span>
                    </span>
                </a>
            </template>

            <p x-show="!memuat && daftar.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                Belum ada notifikasi.
            </p>
        </div>
    </div>
</div>
