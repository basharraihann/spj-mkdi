{{--
Script bersama: komponen Alpine "lokasi" (provinsi -> kota/kabupaten)
dan pengubah <select class="js-searchable"> menjadi input yang bisa diketik.
Dipakai di surat-tugas/_form.blade.php. Salinan yang sama masih ada di nominatif/_form.blade.php,
jangan di-include di halaman yang sudah memuat script itu.
--}}
<script>
    document.addEventListener('alpine:init', function () {
        Alpine.data('lokasi', function (cfg) {
            return {
                provinsi: cfg.provinsi || '',
                kota: cfg.kota || '',
                map: cfg.map || {},

                get daftarProvinsi() {
                    const list = Object.keys(this.map).sort();
                    if (this.provinsi && !list.includes(this.provinsi)) {
                        return [this.provinsi, ...list];
                    }
                    return list;
                },

                get daftarKota() {
                    if (!this.provinsi) return [];
                    let list = (this.map[this.provinsi] || []).slice().sort();
                    if (this.kota && !list.includes(this.kota)) {
                        list = [this.kota, ...list];
                    }
                    return list;
                },

                ubahProvinsi() {
                    if (!this.daftarKota.includes(this.kota)) {
                        this.kota = '';
                    }
                },
            };
        });
    });

    // ===== Searchable select: ubah <select class="js-searchable"> jadi input yang bisa diketik =====
    (function () {
        function enhanceSearchable(select) {
            if (!select || select.dataset.searchEnhanced) return;
            select.dataset.searchEnhanced = '1';

            const originalParent = select.parentElement;
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

            function renderList(filterText) {
                const f = (filterText || '').toLowerCase();
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
                filtered.forEach(function (o) {
                    const li = document.createElement('li');
                    li.textContent = o.textContent.trim();
                    li.dataset.value = o.value;
                    li.className = 'px-3.5 py-2 cursor-pointer hover:bg-blue-50 text-gray-700' +
                        (o.value === select.value ? ' bg-blue-50 font-medium text-blue-700' : '');
                    li.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        pick(o);
                    });
                    list.appendChild(li);
                });
            }

            function pick(o) {
                select.value = o.value;
                input.value = o.textContent.trim();
                select.dispatchEvent(new Event('change', { bubbles: true }));
                select.dispatchEvent(new Event('input', { bubbles: true }));
                closeList();
            }

            function openList() {
                if (select.disabled) return;
                renderList(input.value);
                list.classList.remove('hidden');
            }

            function closeList() {
                list.classList.add('hidden');
            }

            function updateHighlight() {
                const items = itemEls();
                items.forEach(function (li, idx) {
                    li.classList.toggle('bg-blue-100', idx === highlighted);
                });
                if (items[highlighted]) items[highlighted].scrollIntoView({ block: 'nearest' });
            }

            function refreshFromSelect() {
                const current = select.selectedOptions[0];
                input.value = (current && current.value !== '') ? current.textContent.trim() : '';
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
                input.select();
                openList();
            });

            input.addEventListener('input', openList);

            input.addEventListener('blur', function () {
                setTimeout(function () {
                    refreshFromSelect();
                    closeList();
                }, 120);
            });

            input.addEventListener('keydown', function (e) {
                if (select.disabled) return;
                if (list.classList.contains('hidden') && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    e.preventDefault();
                    openList();
                    return;
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const items = itemEls();
                    highlighted = Math.min(highlighted + 1, items.length - 1);
                    updateHighlight();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    highlighted = Math.max(highlighted - 1, 0);
                    updateHighlight();
                } else if (e.key === 'Enter') {
                    const items = itemEls();
                    if (!list.classList.contains('hidden') && highlighted >= 0 && items[highlighted]) {
                        e.preventDefault();
                        const val = items[highlighted].dataset.value;
                        const opt = liveOptions().find(function (o) { return o.value === val; });
                        if (opt) pick(opt);
                    }
                } else if (e.key === 'Escape') {
                    closeList();
                }
            });

            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target)) closeList();
            });

            const observer = new MutationObserver(refreshFromSelect);
            observer.observe(select, { attributes: true, attributeFilter: ['disabled'], childList: true, subtree: true });

            refreshFromSelect();

            select.classList.add('hidden');
            originalParent.insertBefore(wrapper, select);
            wrapper.appendChild(input);
            wrapper.appendChild(iconWrap);
            wrapper.appendChild(list);
            wrapper.appendChild(select);
        }

        document.querySelectorAll('select.js-searchable').forEach(enhanceSearchable);
    })();
</script>
