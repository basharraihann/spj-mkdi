{{--
    Dialog konfirmasi & input alasan (pengganti window.confirm / window.prompt).
    Dipakai lewat JavaScript, dari Blade mana pun:

        const r = await spjConfirm('Kirim memo ke Keuangan?', { title: 'Kirim ke Keuangan', okText: 'Kirim' });
        if (!r) return;                       // true = OK, false = batal

        const alasan = await spjPrompt('Alasan pengembalian (wajib diisi):', { title: 'Ajukan pengembalian', required: true });
        if (alasan === null) return;          // null = batal, selain itu string isian

    Opsi: title, okText, cancelText, tone ('primary' | 'danger' | 'success'), placeholder, required.
--}}
<div id="spj-dlg" class="spj-dlg" hidden>
    <div class="spj-dlg-backdrop" data-dlg="cancel"></div>
    <div class="spj-dlg-panel" role="dialog" aria-modal="true" aria-labelledby="spj-dlg-title"
        aria-describedby="spj-dlg-msg" tabindex="-1">
        <div class="spj-dlg-icon" data-dlg-icon aria-hidden="true"></div>
        <h3 id="spj-dlg-title" class="spj-dlg-title"></h3>
        <p id="spj-dlg-msg" class="spj-dlg-msg"></p>

        <div class="spj-dlg-field" data-dlg-field hidden>
            <textarea id="spj-dlg-input" rows="3" maxlength="500" aria-describedby="spj-dlg-err"></textarea>
            <p id="spj-dlg-err" class="spj-dlg-err" role="alert" hidden></p>
        </div>

        <div class="spj-dlg-actions">
            <button type="button" class="spj-dlg-btn spj-dlg-btn--cancel" data-dlg="cancel">Batal</button>
            <button type="button" class="spj-dlg-btn spj-dlg-btn--ok" data-dlg="ok">OK</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const root = document.getElementById('spj-dlg');
        if (!root) return;
        const panel = root.querySelector('.spj-dlg-panel');
        const elTitle = document.getElementById('spj-dlg-title');
        const elMsg = document.getElementById('spj-dlg-msg');
        const elIcon = root.querySelector('[data-dlg-icon]');
        const elField = root.querySelector('[data-dlg-field]');
        const elInput = document.getElementById('spj-dlg-input');
        const elErr = document.getElementById('spj-dlg-err');
        const btnOk = root.querySelector('[data-dlg="ok"]');
        const btnCancel = root.querySelector('.spj-dlg-btn--cancel');

        const ICONS = {
            primary: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>',
            danger: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0zM12 9v4M12 17h.01"/></svg>',
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l3 3 5-6"/></svg>',
        };

        let resolver = null;
        let opts = null;
        let lastFocus = null;
        let queue = Promise.resolve();

        function close(result) {
            if (!resolver) return;
            const done = resolver;
            resolver = null;
            root.classList.remove('is-open');
            document.removeEventListener('keydown', onKey, true);
            document.documentElement.classList.remove('spj-dlg-lock');
            setTimeout(function () { root.hidden = true; }, 160);
            if (lastFocus && lastFocus.focus) { try { lastFocus.focus({ preventScroll: true }); } catch (e) { } }
            done(result);
        }

        function submit() {
            if (opts.input) {
                const v = elInput.value.trim();
                if (opts.required && v === '') {
                    elErr.textContent = 'Wajib diisi.';
                    elErr.hidden = false;
                    elInput.classList.add('is-invalid');
                    elInput.focus();
                    return;
                }
                close({ ok: true, value: v });
            } else {
                close({ ok: true, value: '' });
            }
        }

        function onKey(e) {
            if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); close({ ok: false, value: null }); return; }
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
                const t = e.target;
                if (t === btnCancel) return;           // biarkan Enter menekan tombol Batal
                if (t === elInput && !(e.ctrlKey || e.metaKey)) { // Enter biasa di textarea = kirim juga
                    e.preventDefault(); submit(); return;
                }
                if (t !== btnOk) { e.preventDefault(); submit(); }
                return;
            }
            if (e.key === 'Tab') { // fokus tidak keluar dari dialog
                const f = Array.from(panel.querySelectorAll('textarea:not([hidden]), button')).filter(function (n) { return !n.closest('[hidden]'); });
                if (!f.length) return;
                const first = f[0], last = f[f.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        }

        function open(o) {
            return new Promise(function (resolve) {
                resolver = resolve;
                opts = o;
                lastFocus = document.activeElement;
                const tone = o.tone || 'primary';

                root.dataset.tone = tone;
                elIcon.innerHTML = ICONS[tone] || ICONS.primary;
                elTitle.textContent = o.title || (o.input ? 'Isi keterangan' : 'Konfirmasi');
                elMsg.textContent = o.message || '';
                elMsg.hidden = !o.message;
                btnOk.textContent = o.okText || 'OK';
                btnCancel.textContent = o.cancelText || 'Batal';

                elField.hidden = !o.input;
                elInput.value = '';
                elInput.placeholder = o.placeholder || 'Tulis di sini…';
                elInput.classList.remove('is-invalid');
                elErr.hidden = true;

                root.hidden = false;
                document.documentElement.classList.add('spj-dlg-lock');
                requestAnimationFrame(function () { root.classList.add('is-open'); });
                document.addEventListener('keydown', onKey, true);
                setTimeout(function () { (o.input ? elInput : btnOk).focus({ preventScroll: true }); }, 30);
            });
        }

        root.addEventListener('click', function (e) {
            const a = e.target.closest('[data-dlg]');
            if (!a) return;
            if (a.dataset.dlg === 'ok') submit();
            else close({ ok: false, value: null });
        });
        elInput.addEventListener('input', function () {
            elErr.hidden = true;
            elInput.classList.remove('is-invalid');
        });

        // Dialog diantre supaya dua pemanggilan beruntun tidak saling timpa.
        function show(o) {
            const p = queue.then(function () { return open(o); });
            queue = p.catch(function () { });
            return p;
        }

        window.spjConfirm = function (message, o) {
            return show(Object.assign({ message: message, input: false }, o || {})).then(function (r) { return r.ok; });
        };
        window.spjPrompt = function (label, o) {
            return show(Object.assign({ message: label, input: true }, o || {})).then(function (r) { return r.ok ? r.value : null; });
        };
    })();
</script>
