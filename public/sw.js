/* Service worker SPJ.
 * - Aset statis (build, gambar, ikon, font) : stale-while-revalidate
 * - Halaman HTML : SELALU jaringan dulu (data anggaran/memo sensitif & berubah,
 *   jadi tidak pernah disimpan di cache). Offline -> halaman /offline.html.
 * - Request non-GET (simpan form, ambil nomor memo) : tidak disentuh sama sekali.
 */
const VERSION = 'spj-v8';
const STATIC_CACHE = VERSION + '-static';
const BASE = new URL('./', self.location).pathname; // ikut subfolder kalau app tidak di root
const OFFLINE_URL = BASE + 'offline.html';

const PRECACHE = [OFFLINE_URL, BASE + 'icons/icon-192.png', BASE + 'images/logoheader.png', BASE + 'css/spj-ui.css'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC_CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

const isStaticAsset = (url) =>
    url.pathname.startsWith(BASE + 'build/') ||
    url.pathname.startsWith(BASE + 'images/') ||
    url.pathname.startsWith(BASE + 'icons/') ||
    url.pathname.startsWith(BASE + 'css/') ||
    url.pathname.startsWith(BASE + 'js/') ||
    url.hostname === 'fonts.bunny.net' ||
    url.hostname === 'fonts.googleapis.com' ||
    url.hostname === 'fonts.gstatic.com' ||
    url.hostname === 'cdn.jsdelivr.net';

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Vite dev server (hot reload) jangan diganggu
    if (url.port === '5173' || url.pathname.endsWith('/hot')) return;

    // Navigasi halaman: jaringan dulu, jatuh ke halaman offline
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.open(STATIC_CACHE).then(async (cache) => {
                const cached = await cache.match(req);
                const network = fetch(req)
                    .then((res) => {
                        if (res && (res.ok || res.type === 'opaque')) cache.put(req, res.clone());
                        return res;
                    })
                    .catch(() => cached);
                return cached || network;
            })
        );
    }
});

self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') self.skipWaiting();
});
