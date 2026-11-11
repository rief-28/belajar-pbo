/*
 * Catatku: peningkatan progresif. Semua halaman tetap berfungsi tanpa JS,
 * kecuali konfirmasi hapus yang memakai dialog (FR-4).
 */
(() => {
    'use strict';

    document.documentElement.classList.add('js');

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    // ---- Pesan flash ----------------------------------------------------

    document.addEventListener('click', (event) => {
        const close = event.target.closest('.flash-close');
        if (close) {
            close.closest('[data-flash]')?.remove();
        }
    });

    $$('[data-flash]').forEach((flash) => {
        // Galat tetap tampil sampai ditutup. Pesan lain hilang sendiri.
        if (!flash.classList.contains('flash-danger')) {
            setTimeout(() => flash.remove(), 6000);
        }
    });

    // ---- Dialog konfirmasi hapus (FR-4) ---------------------------------

    const dialog = $('#confirm-dialog');

    function confirmAction(message) {
        if (!dialog || typeof dialog.showModal !== 'function') {
            return Promise.resolve(window.confirm(message));
        }
        return new Promise((resolve) => {
            $('#confirm-text').textContent = message;
            dialog.returnValue = 'cancel';
            dialog.addEventListener('close', () => resolve(dialog.returnValue === 'ok'), { once: true });
            dialog.showModal();
            $('#confirm-ok')?.focus();
        });
    }

    // Fase capture agar berjalan sebelum penjaga kirim-ganda di bawah.
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm') || form.dataset.confirmed === '1') {
            return;
        }
        event.preventDefault();
        confirmAction(form.dataset.confirm).then((ok) => {
            if (!ok) {
                return;
            }
            form.dataset.confirmed = '1';
            form.requestSubmit();
        });
    }, true);

    // ---- Cegah kirim ganda ---------------------------------------------

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (event.defaultPrevented || !(form instanceof HTMLFormElement) || form.method !== 'post') {
            return;
        }
        $$('button[type="submit"]', form).forEach((button) => {
            button.disabled = true;
            button.classList.add('is-loading');
        });
    });

    // Tombol aktif lagi bila halaman dipulihkan dari tombol kembali browser.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            $$('form[method="post"] button[type="submit"]').forEach((button) => {
                button.disabled = false;
                button.classList.remove('is-loading');
            });
        }
    });

    // ---- Cari dan saring real-time (FR-6, FR-7, FR-8) --------------------

    const filterForm = $('#filter-form');

    if (filterForm) {
        let timer = null;
        let controller = null;

        const buildUrl = () => {
            const params = new URLSearchParams();
            new FormData(filterForm).forEach((value, key) => {
                if (typeof value === 'string' && value !== '') {
                    params.set(key, value);
                }
            });
            if (params.get('sort') === 'desc') {
                params.delete('sort');
            }
            const query = params.toString();
            return 'index.php' + (query ? '?' + query : '');
        };

        const refresh = async () => {
            const url = buildUrl();
            controller?.abort();
            controller = new AbortController();
            try {
                const response = await fetch(url, {
                    signal: controller.signal,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'fetch' },
                });
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                $$('[data-live]').forEach((current) => {
                    const fresh = doc.getElementById(current.id);
                    if (fresh) {
                        current.replaceWith(fresh);
                    }
                });
                history.replaceState(null, '', url);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    // Cadangan: muat halaman penuh bila pembaruan sebagian gagal.
                    window.location.href = url;
                }
            }
        };

        filterForm.addEventListener('input', (event) => {
            if (event.target.matches('input[type="search"]')) {
                clearTimeout(timer);
                timer = setTimeout(refresh, 250);
            }
        });

        filterForm.addEventListener('change', (event) => {
            if (event.target.matches('select')) {
                refresh();
            }
        });

        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            clearTimeout(timer);
            refresh();
        });

        $('[data-reset]', filterForm)?.addEventListener('click', (event) => {
            event.preventDefault();
            filterForm.reset();
            $$('input[type="search"]', filterForm).forEach((input) => { input.value = ''; });
            $$('select', filterForm).forEach((select) => { select.selectedIndex = 0; });
            refresh();
        });

        // Form dikirim lewat Enter/pencarian lalu halaman memakai tombol Reset browser.
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                refresh();
            }
        });
    }

    // ---- Form pengeluaran -------------------------------------------------

    const expenseForm = $('#expense-form');

    if (expenseForm) {
        // Nominal: tampilkan pemisah ribuan, kirim hanya angka.
        const digits = (text) => (text || '').replace(/\D+/g, '').slice(0, 12);
        const group = (text) => digits(text).replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        $$('[data-money]', expenseForm).forEach((input) => {
            input.value = group(input.value);

            input.addEventListener('input', () => {
                const caret = input.selectionStart ?? input.value.length;
                const digitsBeforeCaret = digits(input.value.slice(0, caret)).length;
                input.value = group(input.value);

                let position = 0;
                let seen = 0;
                while (position < input.value.length && seen < digitsBeforeCaret) {
                    if (/\d/.test(input.value[position])) {
                        seen += 1;
                    }
                    position += 1;
                }
                input.setSelectionRange(position, position);
            });
        });

        expenseForm.addEventListener('submit', () => {
            $$('[data-money]', expenseForm).forEach((input) => {
                input.value = digits(input.value);
            });
        });

        // Cara bayar: tampilkan isian sesuai tipe. Fieldset yang disembunyikan
        // dinonaktifkan agar tidak ikut tervalidasi atau terkirim.
        const syncType = () => {
            const selected = $('input[name="type"]:checked', expenseForm)?.value;
            $$('[data-type-fields]', expenseForm).forEach((fieldset) => {
                const active = fieldset.dataset.typeFields === selected;
                fieldset.hidden = !active;
                fieldset.disabled = !active;
            });
        };

        // Kategori "Lainnya" mewajibkan catatan (PRD 2.4).
        const note = $('#note', expenseForm);
        const noteFlag = $('#note-flag', expenseForm);
        const requiredCategory = expenseForm.dataset.noteRequiredCategory;

        const syncNote = () => {
            if (!note) {
                return;
            }
            const category = $('input[name="category"]:checked', expenseForm)?.value;
            const required = category === requiredCategory;
            note.required = required;
            if (noteFlag) {
                noteFlag.textContent = required ? '(wajib untuk kategori Lainnya)' : '(opsional)';
            }
        };

        expenseForm.addEventListener('change', (event) => {
            if (event.target.matches('input[name="type"]')) {
                syncType();
            } else if (event.target.matches('input[name="category"]')) {
                syncNote();
            }
        });

        syncType();
        syncNote();

        // Form baru: fokus ke nominal agar bisa langsung mengetik.
        const amount = $('#amount', expenseForm);
        if (amount && amount.value === '' && !$('.field-error', expenseForm)) {
            amount.focus();
        }
    }
})();
