// ==== Cascading Provinsi -> Kab/Kota ====
// Dipasang lewat atribut di <select> Kab/Kota:
//   data-lokasi-provinsi="#tujuan-select"   selector <select> Provinsi (dicari di dalam form yang sama)
//   data-lokasi-map='{...}'                 peta { "Provinsi": ["Kab A", "Kota B"] }
//   data-lokasi-current="Kota lama"         kota terpilih awal (old() / data edit), opsional
//   data-lokasi-placeholder="Pilih kab/kota" teks opsi kosong, opsional
(function () {
    function initLokasi(kotaSelect) {
        if (!kotaSelect || kotaSelect.dataset.lokasiInit) return;

        const selector = kotaSelect.dataset.lokasiProvinsi;
        if (!selector) return; // querySelector('') bakal throw, jadi dijaga dulu

        const scope = kotaSelect.form || document;
        const provinsiSelect = scope.querySelector(selector);
        if (!provinsiSelect) return;
        kotaSelect.dataset.lokasiInit = '1';

        let map = {};
        try {
            map = JSON.parse(kotaSelect.dataset.lokasiMap || '{}') || {};
        } catch (e) {
            map = {};
        }

        const placeholder = kotaSelect.dataset.lokasiPlaceholder || '— Pilih Kab/Kota —';
        const provinsiAwal = provinsiSelect.value;
        let kota = kotaSelect.dataset.lokasiCurrent || '';

        function render() {
            const prov = provinsiSelect.value;
            const list = (map[prov] || []).slice().sort(function (a, b) {
                return a.localeCompare(b, 'id');
            });

            kotaSelect.innerHTML = '';

            if (!prov) {
                kotaSelect.appendChild(new Option('Pilih provinsi dulu', ''));
                kotaSelect.disabled = true;
                return;
            }

            const adaDiMaster = list.includes(kota);

            // Pindah ke provinsi lain: kota lama gak berlaku lagi.
            if (kota && !adaDiMaster && prov !== provinsiAwal) kota = '';

            // Data lama yang kota-nya belum ada di master (hanya di provinsi aslinya):
            // tetap ditampilkan biar gak keganti diam-diam pas halaman dibuka.
            const adaFallback = kota && !adaDiMaster;

            if (!list.length && !adaFallback) {
                kotaSelect.appendChild(new Option('Belum ada data kab/kota', ''));
                kotaSelect.disabled = true;
                return;
            }

            kotaSelect.disabled = false;
            kotaSelect.appendChild(new Option(placeholder, ''));
            list.forEach(function (nama) {
                kotaSelect.appendChild(new Option(nama, nama, false, nama === kota));
            });
            if (adaFallback) {
                kotaSelect.appendChild(new Option(kota + ' (belum ada di master)', kota, true, true));
            }
        }

        kotaSelect.addEventListener('change', function () {
            kota = kotaSelect.value;
        });
        provinsiSelect.addEventListener('change', render);

        render();
    }

    window.initLokasi = initLokasi;

    function initAll() {
        document.querySelectorAll('select[data-lokasi-provinsi]').forEach(initLokasi);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();