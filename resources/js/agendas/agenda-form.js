document.addEventListener('DOMContentLoaded', function () {
    let anggaranOpen = true;
    let initialRender = true;

    // ==== SweetAlert Konfirmasi Hapus Peserta Berbiaya (Spesifik Edit) ====
    function confirmHapusPesertaBerbiaya(onConfirm) {
        const warningIconSvg = `
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="46" fill="#FEF2F2"/>
                <path d="M50 26L84 84H16L50 26Z" fill="#EF4444"/>
                <rect x="46" y="46" width="8" height="20" rx="4" fill="#FEF2F2"/>
                <rect x="46" y="70" width="8" height="8" rx="4" fill="#FEF2F2"/>
            </svg>
        `;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                html: `
                    <div class="swal-delete-icon-wrap">${warningIconSvg}</div>
                    <div class="swal-delete-title">Peserta ini punya rincian biaya</div>
                    <div class="swal-delete-text">Kalau di-uncek, data biayanya bakal ikut hilang pas disimpan. Lanjut?</div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Lanjut',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                buttonsStyling: false,
                focusConfirm: false,
                customClass: {
                    popup: 'swal-delete-popup',
                    actions: 'swal-delete-actions',
                    confirmButton: 'swal-btn-confirm',
                    cancelButton: 'swal-btn-cancel',
                },
            }).then(function (result) {
                if (result.isConfirmed && typeof onConfirm === 'function') {
                    onConfirm();
                }
            });
        } else {
            if (confirm('Peserta ini punya rincian biaya. Kalau di-uncek, data biayanya bakal ikut hilang pas disimpan. Lanjut?')) {
                onConfirm();
            }
        }
    }

    // ==== Tab Switcher (PNS / Non PNS) ====
    function showPesertaPanel(key) {
        document.querySelectorAll('[data-peserta-panel]').forEach(function (panel) {
            panel.classList.toggle('hidden', panel.dataset.pesertaPanel !== key);
        });
        document.querySelectorAll('.peserta-tab-btn').forEach(function (btn) {
            const active = btn.dataset.tab === key;
            btn.classList.toggle('border-blue-600', active);
            btn.classList.toggle('text-blue-600', active);
            btn.classList.toggle('border-transparent', !active);
            btn.classList.toggle('text-gray-400', !active);
            const badge = btn.querySelector('.peserta-tab-badge');
            if (badge) {
                badge.classList.toggle('bg-blue-50', active);
                badge.classList.toggle('text-blue-500', active);
                badge.classList.toggle('bg-gray-100', !active);
                badge.classList.toggle('text-gray-400', !active);
            }
        });
    }

    document.querySelectorAll('.peserta-tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showPesertaPanel(btn.dataset.tab);
        });
    });

    // ==== Highlight Singkat ====
    function flashField(wrap) {
        if (!wrap) return;
        wrap.classList.add('ring-2', 'ring-blue-300', 'ring-offset-4');
        setTimeout(function () {
            wrap.classList.remove('ring-2', 'ring-blue-300', 'ring-offset-4');
        }, 1500);
    }

    function setWrapVisible(wrap, visible) {
        const wasHidden = wrap.classList.contains('hidden');
        wrap.classList.toggle('hidden', !visible);
        if (visible && wasHidden) {
            if (!initialRender) flashField(wrap);
            return true;
        }
        return false;
    }

    // ==== Field Visibility & Counter ====
    function toggleNomorStKaro(checked) {
        const wrap = document.getElementById('nomor-st-karo-wrap');
        const namaLabel = document.getElementById('nomor-st-karo-nama');
        const input = document.getElementById('nomor-st-karo-input');
        if (!wrap) return;

        const karo = checked.find(function (cb) { return cb.dataset.karo === '1'; });

        setWrapVisible(wrap, Boolean(karo));
        if (namaLabel) {
            namaLabel.textContent = karo ? '(' + karo.dataset.namaPegawai + ')' : '';
        }
        if (input) {
            input.required = Boolean(karo);
        }
        syncAnggaranHeight();
    }

    function toggleNomorMemoFields(checked) {
        const pnsWrap = document.getElementById('nomor-memo-pns-wrap');
        const pnsInput = document.getElementById('nomor-memo-pns-input');
        const nonPnsWrap = document.getElementById('nomor-memo-non-pns-wrap');
        const nonPnsInput = document.getElementById('nomor-memo-non-pns-input');
        if (!pnsWrap || !nonPnsWrap) return;

        function statusOf(cb) {
            const panel = cb.closest('[data-peserta-panel]');
            return panel ? panel.dataset.pesertaPanel : null;
        }

        const adaPns = checked.some(function (cb) { return statusOf(cb) === 'pns'; });
        const adaNonPns = checked.some(function (cb) { return statusOf(cb) === 'nonpns'; });

        setWrapVisible(pnsWrap, adaPns);
        setWrapVisible(nonPnsWrap, adaNonPns);
        if (pnsInput) pnsInput.required = adaPns;
        if (nonPnsInput) nonPnsInput.required = adaNonPns;

        syncAnggaranHeight();
    }

    function updatePesertaCount() {
        const checked = Array.from(document.querySelectorAll('.peserta-checkbox:checked'));

        toggleNomorStKaro(checked);
        toggleNomorMemoFields(checked);

        const counter = document.getElementById('peserta-count');
        if (counter) counter.textContent = checked.length + ' dipilih';

        const chipsWrap = document.getElementById('peserta-selected-chips');
        if (!chipsWrap) return;

        if (checked.length === 0) {
            chipsWrap.classList.add('hidden');
            chipsWrap.innerHTML = '';
            return;
        }

        chipsWrap.classList.remove('hidden');
        chipsWrap.innerHTML = '';
        checked.forEach(function (cb) {
            const nama = cb.dataset.namaPegawai ?? '';
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-medium pl-2.5 pr-1.5 py-1 rounded-full';
            chip.innerHTML = '<span></span><button type="button" class="hover:bg-blue-100 rounded-full p-0.5 transition" aria-label="Hapus">'
                + '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'
                + '</button>';
            chip.querySelector('span').textContent = nama;
            chip.querySelector('button').addEventListener('click', function () {
                if (cb.dataset.hasBiaya === '1') {
                    confirmHapusPesertaBerbiaya(function () {
                        cb.checked = false;
                        updatePesertaCount();
                    });
                    return;
                }
                cb.checked = false;
                updatePesertaCount();
            });
            chipsWrap.appendChild(chip);
        });
    }

    // ==== Search + Filter ====
    const pesertaSearch = document.getElementById('peserta-search');
    const pesertaSearchClear = document.getElementById('peserta-search-clear');
    const pesertaUnitFilter = document.getElementById('peserta-unit-filter');
    const pesertaSelectAllLabel = document.querySelector('.peserta-select-all-label');

    function applyPesertaFilters() {
        const keyword = (pesertaSearch?.value ?? '').trim().toLowerCase();
        const unit = pesertaUnitFilter?.value ?? '';
        const filterActive = Boolean(keyword || unit);
        const visibleCountByGroup = {};

        if (pesertaSearchClear) pesertaSearchClear.classList.toggle('hidden', !keyword);

        document.querySelectorAll('[data-peserta-panel]').forEach(function (panel) {
            const group = panel.dataset.pesertaPanel;
            const items = panel.querySelectorAll('.peserta-item');
            let visibleCount = 0;

            items.forEach(function (item) {
                const matchNama = !keyword || item.dataset.nama.includes(keyword);
                const matchUnit = !unit || item.dataset.unit === unit;
                const match = matchNama && matchUnit;
                item.classList.toggle('hidden', !match);
                if (match) visibleCount++;
            });

            visibleCountByGroup[group] = visibleCount;

            const emptyMsg = panel.querySelector('.peserta-empty-search');
            if (emptyMsg) {
                emptyMsg.classList.toggle('hidden', items.length === 0 || visibleCount > 0);
            }
        });

        document.querySelectorAll('.peserta-tab-btn').forEach(function (btn) {
            const badge = btn.querySelector('.peserta-tab-badge');
            if (!badge) return;
            badge.textContent = visibleCountByGroup[btn.dataset.tab];
        });

        if (pesertaSelectAllLabel) {
            pesertaSelectAllLabel.textContent = filterActive ? 'Pilih semua (hasil filter)' : 'Pilih semua';
        }

        if (filterActive) {
            const activePanel = document.querySelector('[data-peserta-panel]:not(.hidden)');
            const activeGroup = activePanel?.dataset.pesertaPanel;
            if (activeGroup && visibleCountByGroup[activeGroup] === 0) {
                const otherGroup = Object.keys(visibleCountByGroup).find(function (g) {
                    return g !== activeGroup && visibleCountByGroup[g] > 0;
                });
                if (otherGroup) showPesertaPanel(otherGroup);
            }
        }
    }

    if (pesertaSearch) pesertaSearch.addEventListener('input', applyPesertaFilters);
    if (pesertaUnitFilter) pesertaUnitFilter.addEventListener('change', applyPesertaFilters);
    if (pesertaSearchClear) {
        pesertaSearchClear.addEventListener('click', function () {
            if (!pesertaSearch) return;
            pesertaSearch.value = '';
            pesertaSearch.focus();
            applyPesertaFilters();
        });
    }
    document.querySelectorAll('.peserta-reset-filters').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (pesertaSearch) pesertaSearch.value = '';
            if (pesertaUnitFilter) pesertaUnitFilter.value = '';
            applyPesertaFilters();
        });
    });

    // ==== Select All ====
    const selectAllToggle = document.querySelector('.peserta-select-all');
    if (selectAllToggle) {
        selectAllToggle.addEventListener('change', function () {
            const activePanel = document.querySelector('[data-peserta-panel]:not(.hidden)');
            if (!activePanel) return;
            activePanel.querySelectorAll('.peserta-checkbox').forEach(function (cb) {
                const item = cb.closest('.peserta-item');
                if (!item || item.classList.contains('hidden')) return;

                if (!selectAllToggle.checked && cb.checked && cb.dataset.hasBiaya === '1') {
                    return;
                }
                cb.checked = selectAllToggle.checked;
            });
            updatePesertaCount();
        });
    }

    // ==== Event Checkbox Peserta ====
    document.querySelectorAll('.peserta-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            if (!cb.checked && cb.dataset.hasBiaya === '1') {
                cb.checked = true;
                confirmHapusPesertaBerbiaya(function () {
                    cb.checked = false;
                    updatePesertaCount();
                });
                return;
            }
            updatePesertaCount();
        });
    });

    // ==== Collapsible Anggaran ====
    function syncAnggaranHeight() {
        const body = document.getElementById('anggaran-body');
        if (body && anggaranOpen) {
            body.style.maxHeight = 'none';
            body.style.overflow = 'visible';
        }
    }

    (function () {
        const btn = document.getElementById('anggaran-toggle');
        const body = document.getElementById('anggaran-body');
        const chevron = document.getElementById('anggaran-chevron');
        if (!btn || !body) return;

        function setOpen(next) {
            anggaranOpen = next;
            btn.setAttribute('aria-expanded', next);
            if (chevron) chevron.style.transform = next ? 'rotate(180deg)' : 'rotate(0deg)';

            if (next) {
                body.style.overflow = 'hidden';
                body.style.maxHeight = body.scrollHeight + 'px';
                body.style.opacity = '1';
                setTimeout(function () {
                    if (anggaranOpen) {
                        body.style.maxHeight = 'none';
                        body.style.overflow = 'visible';
                    }
                }, 320);
            } else {
                body.style.overflow = 'hidden';
                body.style.maxHeight = body.scrollHeight + 'px';
                void body.offsetHeight;
                body.style.maxHeight = '0px';
                body.style.opacity = '0';
            }
        }

        btn.addEventListener('click', function () { setOpen(!anggaranOpen); });
        setOpen(true);
    })();

    // ==== Form Invalid Handler ====
    const agendaForm = document.getElementById('agenda-form');
    if (agendaForm) {
        agendaForm.addEventListener('invalid', function (e) {
            const body = document.getElementById('anggaran-body');
            if (!body || !body.contains(e.target) || anggaranOpen) return;

            document.getElementById('anggaran-toggle')?.click();
            const target = e.target;
            setTimeout(function () {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.focus({ preventScroll: true });
            }, 350);
        }, true);
    }

    // ==== Badge Progres Penanggung Jawab ====
    (function () {
        const badge = document.getElementById('pj-progress');
        const selects = ['ppk_id', 'bendahara_id', 'penanggung_jawab_id']
            .map(function (n) { return document.querySelector('[name="' + n + '"]'); })
            .filter(Boolean);
        if (!badge || !selects.length) return;

        function render() {
            const terisi = selects.filter(function (s) { return s.value; }).length;
            const lengkap = terisi === selects.length;
            badge.textContent = terisi + '/' + selects.length + ' dipilih';
            badge.classList.toggle('bg-blue-50', lengkap);
            badge.classList.toggle('text-blue-600', lengkap);
            badge.classList.toggle('bg-gray-50', !lengkap);
            badge.classList.toggle('text-gray-400', !lengkap);
        }

        selects.forEach(function (s) { s.addEventListener('change', render); });
        render();
    })();

    // ==== Tanggal Selesai & Durasi ====
    (function () {
        const mulai = document.getElementById('tanggal-mulai');
        const selesai = document.getElementById('tanggal-selesai');
        const info = document.getElementById('durasi-info');
        if (!mulai || !selesai) return;

        function sync() {
            if (mulai.value) selesai.min = mulai.value;
            if (mulai.value && selesai.value && selesai.value < mulai.value) {
                selesai.value = mulai.value;
            }

            if (!info) return;
            if (mulai.value && selesai.value) {
                const hari = Math.round((new Date(selesai.value) - new Date(mulai.value)) / 86400000) + 1;
                info.textContent = hari + ' hari perjalanan';
                info.classList.remove('hidden');
            } else {
                info.classList.add('hidden');
            }
        }

        mulai.addEventListener('change', sync);
        selesai.addEventListener('change', sync);
        sync();
    })();

    updatePesertaCount();
    initialRender = false;
    applyPesertaFilters();

    // ==== Dropdown Kab/Kota Cascading ====
    (function () {
        const provinsiSelect = document.getElementById('tujuan-select');
        const kotaSelect = document.getElementById('kota-tujuan-select');
        if (!provinsiSelect || !kotaSelect) return;

        const kabKotaMap = window.kabKotaMap || {};
        const selectedKota = window.currentKota || window.oldKota || '';

        function renderKotaOptions() {
            const list = kabKotaMap[provinsiSelect.value] || [];
            kotaSelect.innerHTML = '';

            if (!provinsiSelect.value) {
                kotaSelect.appendChild(new Option('Pilih provinsi dulu', ''));
                kotaSelect.disabled = true;
                return;
            }
            if (!list.length) {
                kotaSelect.appendChild(new Option('Belum ada data kab/kota', ''));
                kotaSelect.disabled = true;
                return;
            }

            kotaSelect.disabled = false;
            kotaSelect.appendChild(new Option('Pilih kab/kota', ''));
            list.forEach(function (nama) {
                const opt = new Option(nama, nama);
                if (selectedKota && selectedKota === nama) opt.selected = true;
                kotaSelect.appendChild(opt);
            });

            if (selectedKota && !list.includes(selectedKota)) {
                const opt = new Option(selectedKota + ' (belum ada di master)', selectedKota, true, true);
                kotaSelect.appendChild(opt);
            }
        }

        provinsiSelect.addEventListener('change', renderKotaOptions);
        renderKotaOptions();
    })();

    // ==== Searchable Select Enhancement ====
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
            observer.observe(select, { attributes: true, attributeFilter: ['disabled'], childList: true });

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
});