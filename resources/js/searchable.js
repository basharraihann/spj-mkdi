// ==== Searchable select: ubah <select class="js-searchable"> jadi kotak yang bisa
// diketik buat nyaring opsi. Opsi & status disabled dibaca live dan dipantau lewat
// MutationObserver, jadi select yang opsinya berubah belakangan (Kab/Kota yang
// nunggu Provinsi) tetap ikut update. ====
(function () {
    function enhanceSearchable(select) {
        if (!select || select.dataset.searchEnhanced) return;
        select.dataset.searchEnhanced = '1';

        const originalParent = select.parentElement;
        // sembunyikan ikon panah bawaan markup, biar gak dobel sama ikon di bawah
        const existingIcon = originalParent.querySelector('svg');
        if (existingIcon) existingIcon.style.display = 'none';

        const wrapper = document.createElement('div');
        wrapper.className = 'relative';

        const input = document.createElement('input');
        input.type = 'text';
        input.autocomplete = 'off';
        input.className = select.className.replace('js-searchable', '').trim() + ' pr-9';
        if (select.hasAttribute('required')) input.setAttribute('required', 'required');

        const iconWrap = document.createElement('span');
        iconWrap.className = 'pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400';
        iconWrap.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>';

        const list = document.createElement('ul');
        list.className = 'absolute z-20 top-full left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg py-1 text-sm hidden';

        let highlighted = -1;

        function liveOptions() {
            return Array.from(select.options).filter(function (o) { return o.value !== ''; });
        }

        function itemEls() {
            return Array.from(list.children).filter(function (li) { return li.dataset.value !== undefined; });
        }

        function updateHighlight() {
            const items = itemEls();
            items.forEach(function (li, idx) {
                li.classList.toggle('bg-blue-100', idx === highlighted);
            });
            if (items[highlighted]) items[highlighted].scrollIntoView({ block: 'nearest' });
        }

        function renderList(filterText) {
            const f = (filterText || '').trim().toLowerCase();
            list.innerHTML = '';
            highlighted = -1;

            const filtered = liveOptions().filter(function (o) {
                return o.textContent.trim().toLowerCase().includes(f);
            });

            if (filtered.length === 0) {
                const li = document.createElement('li');
                li.className = 'px-3.5 py-2 text-gray-400 text-xs';
                li.textContent = 'Tidak ditemukan';
                list.appendChild(li);
                return;
            }

            filtered.forEach(function (o, idx) {
                const isSelected = o.value === select.value;
                const li = document.createElement('li');
                li.textContent = o.textContent.trim();
                li.dataset.value = o.value;
                li.className = 'px-3.5 py-2 cursor-pointer hover:bg-blue-50 text-gray-700' +
                    (isSelected ? ' bg-blue-50 font-medium text-blue-700' : '');
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault(); // biar blur gak duluan nutup list sebelum klik kepilih
                    pick(o);
                });
                list.appendChild(li);
                // tanpa filter: arahkan highlight ke opsi yang lagi terpilih
                if (!f && isSelected) highlighted = idx;
            });
        }

        function pick(o) {
            select.value = o.value;
            input.value = o.textContent.trim();
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            closeList();
        }

        // filterText kosong = tampilkan SEMUA opsi. Filter cuma dikirim dari ketikan user.
        function openList(filterText) {
            if (select.disabled) return;
            renderList(filterText || '');
            list.classList.remove('hidden');
            if (highlighted >= 0) updateHighlight();
        }

        function closeList() {
            list.classList.add('hidden');
        }

        // Sinkronkan kotak teks dengan select aslinya: dipanggil pas init dan tiap
        // select berubah dari luar (mis. Kab/Kota diisi ulang pas Provinsi diganti).
        function refreshFromSelect() {
            const current = select.selectedOptions[0];
            const label = (current && current.value !== '') ? current.textContent.trim() : '';

            // jangan timpa teks yang lagi diketik user
            if (document.activeElement !== input || select.disabled) {
                input.value = label;
            }

            input.disabled = select.disabled;
            if (select.disabled) {
                input.placeholder = current ? current.textContent.trim() : 'Tidak tersedia';
                closeList();
            } else {
                input.placeholder = select.dataset.placeholder || 'Cari & pilih...';
            }
        }

        input.addEventListener('focus', function () {
            if (select.disabled) return;
            input.select();   // teks ke-block, jadi ngetik langsung nimpa
            openList('');     // semua opsi, bukan difilter label terpilih
        });

        // klik pas sudah fokus tapi list tertutup (habis pilih / Escape)
        input.addEventListener('click', function () {
            if (select.disabled || !list.classList.contains('hidden')) return;
            input.select();
            openList('');
        });

        // filter hanya dari ketikan user
        input.addEventListener('input', function () {
            openList(input.value);
        });

        input.addEventListener('blur', function () {
            setTimeout(function () {
                refreshFromSelect(); // kalau ditutup tanpa milih, balik ke label terpilih
                closeList();
            }, 120);
        });

        input.addEventListener('keydown', function (e) {
            if (select.disabled) return;
            const isClosed = list.classList.contains('hidden');

            if (isClosed && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                e.preventDefault();
                openList('');
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlighted = Math.min(highlighted + 1, itemEls().length - 1);
                updateHighlight();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlighted = Math.max(highlighted - 1, 0);
                updateHighlight();
            } else if (e.key === 'Enter') {
                if (isClosed) return;
                e.preventDefault(); // list terbuka: Enter jangan submit form
                const items = itemEls();
                if (highlighted >= 0 && items[highlighted]) {
                    const val = items[highlighted].dataset.value;
                    const opt = liveOptions().find(function (o) { return o.value === val; });
                    if (opt) pick(opt);
                }
            } else if (e.key === 'Escape') {
                closeList();
                refreshFromSelect();
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) closeList();
        });

        // Pantau perubahan select aslinya (opsi diganti / disabled berubah).
        const observer = new MutationObserver(refreshFromSelect);
        observer.observe(select, {
            attributes: true,
            attributeFilter: ['disabled'],
            childList: true,
            subtree: true,
        });

        refreshFromSelect();

        select.classList.add('hidden');
        originalParent.insertBefore(wrapper, select);
        wrapper.appendChild(input);
        wrapper.appendChild(iconWrap);
        wrapper.appendChild(list);
        wrapper.appendChild(select);
    }

    // expose global, buat select yang baru muncul belakangan (di-render dinamis)
    window.enhanceSearchable = enhanceSearchable;

    function initAll() {
        document.querySelectorAll('select.js-searchable').forEach(enhanceSearchable);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();