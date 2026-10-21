document.addEventListener('DOMContentLoaded', function () {
    // Ambil data konfigurasi yang dikirim dari Blade via window
    const config = window.pesertaConfig || {};
    const SIMPLE_COLS = config.simpleCols || [];
    const LUMPSUM_FIELDS = config.lumpsumFields || [];
    const LUMPSUM_RATE_COLS = new Set(LUMPSUM_FIELDS.map(f => 'col-' + f.rate));
    const AGENDA_MULAI = config.agendaMulai || '';
    const AGENDA_SELESAI = config.agendaSelesai || '';

    // ==== Toggle kolom tabel real-time ====
    document.querySelectorAll('input[name="komponen_biaya[]"]').forEach(function (cb) {
        function syncKomponenCol() {
            document.querySelectorAll('[data-col="' + cb.value + '"]').forEach(function (el) {
                el.classList.toggle('hidden', !cb.checked);
            });
        }
        cb.addEventListener('change', syncKomponenCol);
        syncKomponenCol();
    });

    document.querySelectorAll('input[name="jenis_uang_harian[]"]').forEach(function (cb) {
        function syncUhPair() {
            document.querySelectorAll('[data-uh="' + cb.value + '"]').forEach(function (el) {
                el.classList.toggle('hidden', !cb.checked);
            });
        }
        cb.addEventListener('change', syncUhPair);
        syncUhPair();
    });

    // Ambil digit murni saja dari sebuah string
    function onlyDigits(value) {
        return String(value ?? '').replace(/\D/g, '');
    }

    // Format angka jadi "500.000"
    function formatRupiah(rawValue) {
        const digits = onlyDigits(rawValue);
        if (!digits) return '';
        const trimmed = digits.replace(/^0+(?=\d)/, '');
        return trimmed.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Hitung total lumpsum satu baris
    function rowLumpsumValue(row) {
        let total = 0;
        LUMPSUM_FIELDS.forEach(function (f) {
            const hariEl = row.querySelector('.col-' + f.hari);
            const blk = hariEl ? hariEl.closest('[data-uh]') : null;
            if (blk && blk.classList.contains('hidden')) return;
            const hari = parseInt(onlyDigits(hariEl?.value) || '0', 10);
            const rate = parseInt(onlyDigits(row.querySelector('.col-' + f.rate)?.value) || '0', 10);
            total += hari * rate;
        });
        return total;
    }

    function updateRowLumpsumDisplay(row) {
        const display = row.querySelector('.row-lumpsum-total');
        if (display) {
            display.textContent = 'Rp ' + (formatRupiah(String(rowLumpsumValue(row))) || '0');
        }
    }

    function recalcTotals(group) {
        SIMPLE_COLS.forEach(function (col) {
            let sum = 0;
            document.querySelectorAll('input.col-' + col + '[data-group="' + group + '"]').forEach(function (input) {
                sum += parseInt(onlyDigits(input.value) || '0', 10);
            });
            const target = document.querySelector('.total-' + col + '[data-group="' + group + '"]');
            if (target) {
                target.textContent = sum ? ('Rp ' + formatRupiah(String(sum))) : 'Rp 0';
            }
        });

        let lumpsumSum = 0;
        document.querySelectorAll('tr.peserta-row[data-group="' + group + '"]').forEach(function (row) {
            lumpsumSum += rowLumpsumValue(row);
        });
        const lumpsumTarget = document.querySelector('.total-lumpsum[data-group="' + group + '"]');
        if (lumpsumTarget) {
            lumpsumTarget.textContent = lumpsumSum ? ('Rp ' + formatRupiah(String(lumpsumSum))) : 'Rp 0';
        }
    }

    // ==== Tanggal dinas per peserta ====
    function lamaHari(row) {
        const m = row.querySelector('.tgl-mulai')?.value;
        const s = row.querySelector('.tgl-selesai')?.value;
        if (!m || !s) return 0;
        const d = Math.round((new Date(s) - new Date(m)) / 86400000) + 1;
        return d > 0 ? d : 0;
    }

    function activeUhBlocks(row) {
        return Array.from(row.querySelectorAll('[data-uh]'))
            .filter(el => !el.classList.contains('hidden'));
    }

    function activeUhValues(row) {
        return activeUhBlocks(row).map(function (el) {
            return parseInt(onlyDigits(el.querySelector('.hari-input').value) || '0', 10);
        });
    }

    function updateWarn(row) {
        const lama = lamaHari(row);
        const total = activeUhValues(row).reduce((a, b) => a + b, 0);
        const warn = row.querySelector('.row-warn');
        if (warn) warn.classList.toggle('hidden', !(total > 0 && lama > 0 && total !== lama));
    }

    function setMode(row, manual) {
        row.dataset.manual = manual ? '1' : '';
        const label = row.querySelector('.uh-mode-label');
        const btn = row.querySelector('.uh-auto-btn');
        if (label) label.textContent = manual ? 'Diisi manual' : 'Otomatis dari tanggal';
        if (btn) btn.classList.toggle('hidden', !manual);
    }

    function applyAutoDays(row) {
        const lama = lamaHari(row);
        const active = activeUhBlocks(row);
        row.querySelectorAll('[data-uh]').forEach(function (block) {
            const input = block.querySelector('.hari-input');
            input.value = (active.indexOf(block) === 0 && lama) ? String(lama) : '';
        });
    }

    function detectInitialMode(row) {
        const lama = lamaHari(row);
        const vals = activeUhValues(row);
        const total = vals.reduce((a, b) => a + b, 0);
        const auto = total === 0 || (vals[0] === lama && vals.slice(1).every(v => v === 0));
        setMode(row, !auto);
    }

    function syncRowDays(row) {
        const lama = lamaHari(row);
        const lamaEl = row.querySelector('.row-lama');
        if (lamaEl) lamaEl.textContent = lama || '-';

        const mulai = row.querySelector('.tgl-mulai');
        const selesai = row.querySelector('.tgl-selesai');
        const resetBtn = row.querySelector('.tgl-reset');
        if (resetBtn && mulai && selesai) {
            const beda = mulai.value !== AGENDA_MULAI || selesai.value !== AGENDA_SELESAI;
            resetBtn.classList.toggle('hidden', !beda);
        }

        if (row.dataset.manual !== '1') applyAutoDays(row);

        updateWarn(row);
        updateRowLumpsumDisplay(row);
        recalcTotals(row.dataset.group);
    }

    document.querySelectorAll('tr.peserta-row').forEach(function (row) {
        const mulai = row.querySelector('.tgl-mulai');
        const selesai = row.querySelector('.tgl-selesai');
        if (!mulai || !selesai) return;

        function onDateChange(source) {
            if (mulai.value && selesai.value && mulai.value > selesai.value) {
                if (source === 'selesai') mulai.value = selesai.value;
                else selesai.value = mulai.value;
            }
            if (mulai.value) selesai.min = mulai.value;
            syncRowDays(row);
        }

        ['change', 'input'].forEach(function (ev) {
            mulai.addEventListener(ev, function () { onDateChange('mulai'); });
            selesai.addEventListener(ev, function () { onDateChange('selesai'); });
        });

        const resetBtn = row.querySelector('.tgl-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                mulai.value = AGENDA_MULAI;
                selesai.value = AGENDA_SELESAI;
                selesai.min = AGENDA_MULAI;
                syncRowDays(row);
            });
        }

        const autoBtn = row.querySelector('.uh-auto-btn');
        if (autoBtn) {
            autoBtn.addEventListener('click', function () {
                setMode(row, false);
                syncRowDays(row);
            });
        }
    });

    document.querySelectorAll('input[name="jenis_uang_harian[]"]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            document.querySelectorAll('tr.peserta-row').forEach(function (row) { syncRowDays(row); });
        });
    });

    // ==== Popup Peng. Riil Modal ====
    (function () {
        const modal = document.getElementById('peng-riil-modal');
        if (!modal) return;

        const entriesContainer = document.getElementById('peng-riil-modal-entries');
        const template = document.getElementById('peng-riil-entry-template');
        const addBtn = document.getElementById('peng-riil-modal-add');
        const totalDisplay = document.getElementById('peng-riil-modal-total');
        const namaLabel = document.getElementById('peng-riil-modal-nama');
        let activeBtn = null;
        let entryCounter = 0;

        function entryValue(entryEl) {
            const isSbm = entryEl.querySelector('.entry-mode-radio[value="sbm"]').checked;
            if (isSbm) {
                const opt = entryEl.querySelector('.entry-sbm-select').selectedOptions[0];
                return opt && opt.value ? parseInt(opt.dataset.rate || '0', 10) * 2 : 0;
            }
            return parseInt(onlyDigits(entryEl.querySelector('.entry-manual-input').value) || '0', 10);
        }

        function updateModalTotal() {
            let total = 0;
            entriesContainer.querySelectorAll('.peng-riil-entry').forEach(function (el) {
                total += entryValue(el);
            });
            totalDisplay.textContent = 'Rp ' + (formatRupiah(String(total)) || '0');
        }

        function syncEntryMode(entryEl) {
            const isSbm = entryEl.querySelector('.entry-mode-radio[value="sbm"]').checked;
            entryEl.querySelector('.entry-manual-section').classList.toggle('hidden', isSbm);
            entryEl.querySelector('.entry-sbm-section').classList.toggle('hidden', !isSbm);
        }

        function updateEntryKeterangan(entryEl) {
            const select = entryEl.querySelector('.entry-sbm-select');
            const preview = entryEl.querySelector('.entry-sbm-preview');
            const keteranganInput = entryEl.querySelector('.entry-keterangan-input');
            const opt = select.selectedOptions[0];

            if (!opt || !opt.value) {
                preview.textContent = 'Rp 0';
                return;
            }

            const rate = parseInt(opt.dataset.rate || '0', 10) * 2;
            preview.textContent = 'Rp ' + (formatRupiah(String(rate)) || '0');

            const tujuan = opt.dataset.tujuan || opt.textContent.trim();
            keteranganInput.value = opt.dataset.kategori === 'jabodetabek'
                ? 'Transport Jakarta – ' + tujuan + ' PP'
                : 'Transport Daerah (' + tujuan + ') PP';
        }

        function wireEntry(entryEl) {
            entryCounter++;
            const radioName = 'peng-riil-entry-mode-' + entryCounter;
            entryEl.querySelectorAll('.entry-mode-radio').forEach(function (r) { r.name = radioName; });

            entryEl.querySelectorAll('.entry-mode-radio').forEach(function (r) {
                r.addEventListener('change', function () {
                    syncEntryMode(entryEl);
                    updateModalTotal();
                });
            });

            const manualInput = entryEl.querySelector('.entry-manual-input');
            manualInput.addEventListener('input', function () {
                manualInput.value = onlyDigits(manualInput.value);
                updateModalTotal();
            });
            manualInput.addEventListener('blur', function () {
                manualInput.value = formatRupiah(manualInput.value);
            });

            const sbmSelect = entryEl.querySelector('.entry-sbm-select');
            sbmSelect.addEventListener('change', function () {
                updateEntryKeterangan(entryEl);
                updateModalTotal();
            });

            entryEl.querySelector('.peng-riil-entry-remove').addEventListener('click', function () {
                if (entriesContainer.querySelectorAll('.peng-riil-entry').length <= 1) {
                    entryEl.remove();
                    addEntry();
                    updateModalTotal();
                    return;
                }
                entryEl.remove();
                updateModalTotal();
            });
        }

        function addEntry(data) {
            const frag = template.content.cloneNode(true);
            const entryEl = frag.querySelector('.peng-riil-entry');
            entriesContainer.appendChild(frag);
            wireEntry(entryEl);

            if (data) {
                const isSbm = data.mode === 'sbm';
                entryEl.querySelector('.entry-mode-radio[value="' + (isSbm ? 'sbm' : 'manual') + '"]').checked = true;
                syncEntryMode(entryEl);

                if (isSbm) {
                    entryEl.querySelector('.entry-sbm-select').value = data.rate_id || '';
                    updateEntryKeterangan(entryEl);
                } else {
                    entryEl.querySelector('.entry-manual-input').value = formatRupiah(String(data.value || ''));
                }
                if (data.keterangan) {
                    entryEl.querySelector('.entry-keterangan-input').value = data.keterangan;
                }
            }

            return entryEl;
        }

        function openModal(btn) {
            activeBtn = btn;
            namaLabel.textContent = btn.dataset.nama || '';
            entriesContainer.innerHTML = '';

            let detail = [];
            try {
                detail = JSON.parse(btn.dataset.detail || '[]');
            } catch (e) {
                detail = [];
            }

            if (Array.isArray(detail) && detail.length) {
                detail.forEach(function (d) { addEntry(d); });
            } else {
                addEntry();
            }

            updateModalTotal();
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
            activeBtn = null;
        }

        addBtn.addEventListener('click', function () {
            addEntry();
            updateModalTotal();
        });

        document.querySelectorAll('.peng-riil-open-btn').forEach(function (btn) {
            btn.addEventListener('click', function () { openModal(btn); });
        });

        document.getElementById('peng-riil-modal-close')?.addEventListener('click', closeModal);
        document.getElementById('peng-riil-modal-cancel')?.addEventListener('click', closeModal);
        modal.addEventListener('click', closeModal);

        document.getElementById('peng-riil-modal-save')?.addEventListener('click', function () {
            if (!activeBtn) return;

            const entries = [];
            entriesContainer.querySelectorAll('.peng-riil-entry').forEach(function (entryEl) {
                const isSbm = entryEl.querySelector('.entry-mode-radio[value="sbm"]').checked;
                const keterangan = entryEl.querySelector('.entry-keterangan-input').value.trim();

                if (isSbm) {
                    const opt = entryEl.querySelector('.entry-sbm-select').selectedOptions[0];
                    if (!opt || !opt.value) return;
                    entries.push({
                        mode: 'sbm',
                        value: parseInt(opt.dataset.rate || '0', 10) * 2,
                        rate_id: opt.value,
                        tujuan: opt.dataset.tujuan || opt.textContent.trim(),
                        keterangan: keterangan,
                    });
                } else {
                    const value = parseInt(onlyDigits(entryEl.querySelector('.entry-manual-input').value) || '0', 10);
                    if (!value && !keterangan) return;
                    entries.push({
                        mode: 'manual',
                        value: value,
                        rate_id: '',
                        tujuan: '',
                        keterangan: keterangan,
                    });
                }
            });

            const pegawaiId = activeBtn.dataset.pegawaiId;
            const valueInput = document.querySelector('input.col-peng_riil[name="peng_riil[' + pegawaiId + ']"]');
            const detailField = document.querySelector('.peng-riil-detail-field[name="peng_riil_detail[' + pegawaiId + ']"]');
            const displayEl = activeBtn.querySelector('.peng-riil-display');
            const subEl = activeBtn.querySelector('.peng-riil-sub');

            const total = entries.reduce(function (sum, e) { return sum + (e.value || 0); }, 0);
            const entriesJson = JSON.stringify(entries);

            activeBtn.dataset.detail = entriesJson;
            if (valueInput) valueInput.value = total;
            if (detailField) detailField.value = entriesJson;

            if (displayEl) displayEl.textContent = total ? 'Rp ' + formatRupiah(String(total)) : 'Isi Peng. Riil';
            if (subEl) {
                subEl.textContent = entries.length > 1
                    ? entries.length + ' item'
                    : (entries.length === 1 ? (entries[0].mode === 'sbm' ? 'SBM' : 'Manual') : '');
            }

            if (valueInput) recalcTotals(valueInput.dataset.group);
            closeModal();
        });
    })();

    // ==== Input Masking ====
    document.querySelectorAll('.rupiah-input').forEach(function (input) {
        input.value = formatRupiah(input.value);

        input.addEventListener('focus', function () {
            input.value = onlyDigits(input.value);
            requestAnimationFrame(function () {
                input.setSelectionRange(input.value.length, input.value.length);
            });
        });

        input.addEventListener('input', function () {
            input.value = onlyDigits(input.value);
            const row = input.closest('tr.peserta-row');
            if (row) {
                const isLumpsumRate = Array.from(input.classList).some(c => LUMPSUM_RATE_COLS.has(c));
                if (isLumpsumRate) updateRowLumpsumDisplay(row);
            }
            recalcTotals(input.dataset.group);
        });

        input.addEventListener('blur', function () {
            input.value = formatRupiah(input.value);
        });
    });

    document.querySelectorAll('.hari-input').forEach(function (input) {
        input.value = onlyDigits(input.value);

        input.addEventListener('input', function () {
            input.value = onlyDigits(input.value).slice(0, 2);
            const row = input.closest('tr.peserta-row');
            setMode(row, true);
            updateWarn(row);
            updateRowLumpsumDisplay(row);
            recalcTotals(input.dataset.group);
        });
    });

    // Inisialisasi
    document.querySelectorAll('tr.peserta-row').forEach(function (row) {
        detectInitialMode(row);
        syncRowDays(row);
    });
    ['pns', 'nonpns'].forEach(recalcTotals);

    const form = document.getElementById('peserta-form');
    if (form) {
        form.addEventListener('submit', function () {
            document.querySelectorAll('.rupiah-input, .hari-input').forEach(function (input) {
                input.value = onlyDigits(input.value);
            });
        });
    }
});