{{-- Registrasi service worker + tombol "Pasang aplikasi" + penanda offline.
Service worker hanya jalan di HTTPS (atau localhost). --}}
<script>
    (function () {
        // 1) Service worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register(@json(asset('sw.js'))).catch(function () { /* diam saja */ });
            });
        }

        // 2) Tombol pasang aplikasi (Chrome/Edge/Android)
        var deferred = null;
        var installBtns = function () { return document.querySelectorAll('[data-install]'); };
        var toggleInstall = function (show) { installBtns().forEach(function (b) { b.hidden = !show; }); };

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferred = e;
            toggleInstall(true);
        });
        window.addEventListener('appinstalled', function () { deferred = null; toggleInstall(false); });
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-install]');
            if (!btn || !deferred) return;
            deferred.prompt();
            deferred.userChoice.finally(function () { deferred = null; toggleInstall(false); });
        });

        // 3) Penanda offline
        var banner = document.getElementById('spj-offline');
        if (banner) {
            var sync = function () { banner.classList.toggle('is-on', !navigator.onLine); };
            window.addEventListener('online', sync);
            window.addEventListener('offline', sync);
            sync();
        }

        // 4) Tampilan HP tidak bisa di-zoom (pinch / double-tap) supaya layout tetap rapi.
        //    Catatan: iOS Safari mengabaikan user-scalable=no, jadi ditahan lewat event juga.
        ['gesturestart', 'gesturechange', 'gestureend'].forEach(function (ev) {
            document.addEventListener(ev, function (e) { e.preventDefault(); }, { passive: false });
        });
        document.addEventListener('touchmove', function (e) {
            if (e.touches && e.touches.length > 1) e.preventDefault();
        }, { passive: false });

        // 5) Tabel yang MELUBER dari wadahnya: bungkus supaya bisa digeser ke samping di HP.
        //    Tabel yang sudah muat / sudah punya tampilan responsif sendiri (mis. tabel Agenda)
        //    tidak disentuh sama sekali.
        function bungkusTabelLebar() {
            document.querySelectorAll('.spj-main table').forEach(function (t) {
                if (t.closest('.overflow-x-auto, .spj-table-scroll')) return;
                var wadah = t.parentElement;
                if (!wadah || t.scrollWidth <= wadah.clientWidth + 1) return;
                var w = document.createElement('div');
                w.className = 'spj-table-scroll';
                t.parentNode.insertBefore(w, t);
                w.appendChild(t);
            });
        }
        window.addEventListener('load', bungkusTabelLebar);
        var tunda;
        window.addEventListener('resize', function () { clearTimeout(tunda); tunda = setTimeout(bungkusTabelLebar, 200); });
    })();
</script>
