/* STO Alat Berat Wilayah 1 — peningkatan tampilan (tanpa library, tanpa npm).
   Halaman tetap berfungsi tanpa berkas ini; skrip hanya menambah kenyamanan.
   Tombol & form memakai event delegation agar jendela detail yang dimuat belakangan
   (saat nomor STO diklik) langsung berfungsi tanpa inisialisasi ulang. */
(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  function on(type, selector, handler) {
    document.addEventListener(type, function (e) {
      var el = e.target.closest && e.target.closest(selector);
      if (el) { handler(e, el); }
    });
  }

  /* ---- Menu samping (HP & tablet): buka/tutup sebagai laci ---- */
  var navToggle = $('[data-nav-toggle]');
  function setNav(open) {
    document.body.classList.toggle('nav-open', open);
    if (navToggle) { navToggle.setAttribute('aria-expanded', open ? 'true' : 'false'); }
  }
  if (navToggle) {
    navToggle.addEventListener('click', function () { setNav(!document.body.classList.contains('nav-open')); });
  }
  on('click', '[data-nav-close]', function () { setNav(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && document.body.classList.contains('nav-open')) { setNav(false); if (navToggle) { navToggle.focus(); } }
  });

  /* ---- Toast: hilang otomatis ---- */
  $$('.toast').forEach(function (toast) {
    function dismiss() {
      toast.classList.add('is-leaving');
      setTimeout(function () { toast.remove(); }, 220);
    }
    var closeBtn = $('[data-toast-close]', toast);
    if (closeBtn) { closeBtn.addEventListener('click', dismiss); }
    // Toast dengan tombol "Urungkan" bertahan lebih lama.
    setTimeout(dismiss, $('.toast-undo', toast) ? 12000 : 6000);
  });

  /* ---- Dropzone upload PDF ---- */
  var MAX_BYTES = 10 * 1024 * 1024;
  function formatSize(bytes) {
    if (bytes >= 1048576) { return (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB'; }
    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
  }
  $$('[data-dropzone]').forEach(function (zone) {
    var input = $('input[type=file]', zone);
    var title = $('[data-dz-title]', zone);
    var hint = $('[data-dz-hint]', zone);
    if (!input || !title || !hint) { return; }
    var defaultTitle = title.textContent;
    var defaultHint = hint.textContent;

    function reset() {
      zone.classList.remove('has-file', 'has-error');
      title.textContent = defaultTitle;
      hint.textContent = defaultHint;
    }
    function fail(message) {
      input.value = '';
      zone.classList.remove('has-file');
      zone.classList.add('has-error');
      title.textContent = message;
      hint.textContent = 'Pilih file lain.';
    }
    function render() {
      var file = input.files && input.files[0];
      if (!file) { reset(); return; }
      zone.classList.remove('has-error');
      var isPdf = /\.pdf$/i.test(file.name) || file.type === 'application/pdf';
      if (!isPdf) { fail('File harus berformat PDF'); return; }
      if (file.size > MAX_BYTES) { fail('Ukuran file lebih dari 10 MB'); return; }
      zone.classList.add('has-file');
      title.textContent = file.name;
      hint.textContent = formatSize(file.size) + ' · siap dibaca';
    }

    input.addEventListener('change', render);
    ['dragenter', 'dragover'].forEach(function (name) {
      zone.addEventListener(name, function (e) { e.preventDefault(); zone.classList.add('is-drag'); });
    });
    ['dragleave', 'drop'].forEach(function (name) {
      zone.addEventListener(name, function (e) { e.preventDefault(); zone.classList.remove('is-drag'); });
    });
    zone.addEventListener('drop', function (e) {
      var files = e.dataTransfer && e.dataTransfer.files;
      if (!files || !files.length) { return; }
      try {
        var transfer = new DataTransfer();
        transfer.items.add(files[0]);
        input.files = transfer.files;
      } catch (err) {
        input.files = files;
      }
      render();
    });
  });

  /* ---- Form STO: baris barang dinamis dan hitung biaya otomatis ---- */
  var rupiah = function (cents) {
    return (cents / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  };
  function initStoForm(form) {
    if (form._stoInit) { return; }
    form._stoInit = true;
    var list = $('[data-item-list]', form);
    var tpl = $('template[data-item-template]', form);
    var totalEl = $('[data-total]', form);
    var nextIndex = 1000;
    form._itemsInitial = list.innerHTML;

    function recalc() {
      var total = 0;
      $$('[data-item-row]', list).forEach(function (row) {
        var q = parseFloat($('[data-q]', row).value);
        var r = parseFloat($('[data-r]', row).value);
        var cents = (isFinite(q) && isFinite(r)) ? Math.round(Math.round(q * 1000) * Math.round(r * 100) / 1000) : 0;
        total += cents;
        $('[data-amount]', row).textContent = rupiah(cents);
      });
      totalEl.textContent = rupiah(total);
    }
    function addRow(values) {
      list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, String(nextIndex++)));
      var row = list.lastElementChild;
      if (values) {
        ['name', 'quantity', 'unit', 'rate'].forEach(function (key) {
          var input = $('[name$="[' + key + ']"]', row);
          if (input && values[key] !== undefined && values[key] !== null) { input.value = values[key]; }
        });
      }
      return row;
    }
    form._recalc = recalc;
    form._addRow = addRow;

    list.addEventListener('input', recalc);
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-item-remove]');
      if (!btn) { return; }
      var rows = $$('[data-item-row]', list);
      var row = btn.closest('[data-item-row]');
      if (rows.length > 1) {
        row.remove();
      } else {
        // Baris terakhir tidak dihapus (minimal satu barang); cukup dikosongkan, satuan dipertahankan.
        $$('input', row).forEach(function (input) { if (!/\[unit\]$/.test(input.name)) { input.value = ''; } });
      }
      recalc();
    });
    $('[data-item-add]', form).addEventListener('click', function () {
      var row = addRow();
      var first = $('input', row);
      if (first) { first.focus(); }
      recalc();
    });
    recalc();
  }

  /* ---- Isi awal form Tambah STO dari STO yang gagal dibaca ---- */
  function applyPrefill(dlg, data) {
    var form = $('form[data-sto-form]', dlg);
    if (!form || !data) { return; }
    initStoForm(form);
    var set = function (name, value) {
      var el = form.elements.namedItem(name);
      if (el && value !== undefined && value !== null) { el.value = value; }
    };
    set('number', data.number);
    set('document_date', data.document_date);
    set('activity', data.activity);
    set('origin', data.origin);
    set('destination', data.destination);
    set('soj', '');
    set('mmj', '');
    $$('[data-prefill-field]', form).forEach(function (el) {
      var key = el.getAttribute('data-prefill-field');
      el.value = data[key] !== undefined ? data[key] : '';
    });
    var list = $('[data-item-list]', form);
    list.innerHTML = '';
    (data.items && data.items.length ? data.items : [{ unit: 'TON' }]).forEach(function (item) { form._addRow(item); });
    form._recalc();

    var note = $('[data-prefill-note]', dlg);
    if (note) {
      $('[data-prefill-source]', note).textContent = data.source || 'PDF';
      $('[data-prefill-pdf]', note).setAttribute('href', data.pdf || '#');
      note.hidden = false;
    }
    dlg.setAttribute('data-prefilled', '1');
    // Fokus ke kolom wajib pertama yang masih kosong.
    setTimeout(function () {
      var empty = $$('input[required], select[required], textarea[required]', form).filter(function (el) { return !el.value; })[0];
      if (empty) { empty.focus(); }
    }, 50);
  }
  function clearPrefill(dlg) {
    if (!dlg.hasAttribute('data-prefilled')) { return; }
    dlg.removeAttribute('data-prefilled');
    var form = $('form[data-sto-form]', dlg);
    if (form) {
      form.reset();
      $$('[data-prefill-field]', form).forEach(function (el) { el.value = ''; });
      if (form._itemsInitial !== undefined) { $('[data-item-list]', form).innerHTML = form._itemsInitial; form._recalc(); }
    }
    var note = $('[data-prefill-note]', dlg);
    if (note) { note.hidden = true; }
  }

  /* ---- Dialog / modal ---- */
  function openDialog(dlg) {
    if (dlg && dlg.showModal && !dlg.open) { dlg.showModal(); }
  }
  // Kembalikan form ubah ke isi awal (termasuk baris barang yang ditambah/dihapus).
  function resetEdit(dlg) {
    var form = $('form[data-sto-form]', dlg);
    if (!form) { return; }
    if (form.hasAttribute('data-reload-on-cancel')) {
      // Form dibuka ulang setelah gagal validasi: muat ulang agar kembali ke data tersimpan.
      window.location.replace(window.location.pathname + window.location.search.replace(/([?&])open=[^&]*&?/, '$1').replace(/[?&]$/, ''));
      return;
    }
    form.reset();
    if (form._itemsInitial !== undefined) {
      $('[data-item-list]', form).innerHTML = form._itemsInitial;
      form._recalc();
    }
    dlg.classList.remove('is-editing');
  }
  function initDialog(dlg) {
    if (dlg._init) { return; }
    dlg._init = true;
    var persistent = dlg.hasAttribute('data-persistent');
    // Selama mengisi form (ubah/tambah), Esc dan klik di luar tidak menutup agar isian tidak hilang.
    dlg.addEventListener('cancel', function (e) {
      if (persistent || dlg.classList.contains('is-editing')) { e.preventDefault(); }
      // Pratinjau impor: Esc tidak langsung membatalkan, tetapi menampilkan konfirmasi.
      if (persistent && $('.foot-confirm', dlg)) { dlg.classList.add('is-confirming'); }
    });
    if (!persistent) {
      dlg.addEventListener('click', function (e) {
        if (e.target === dlg && !dlg.classList.contains('is-editing')) { dlg.close(); }
      });
    }
    dlg.addEventListener('close', function () {
      dlg.classList.remove('is-confirming');
      if (!dlg.hasAttribute('data-always-edit') && dlg.classList.contains('is-editing')) { resetEdit(dlg); }
      clearPrefill(dlg);
    });
    $$('form[data-sto-form]', dlg).forEach(initStoForm);
    if (dlg.hasAttribute('data-autoopen') && dlg.showModal) {
      dlg.removeAttribute('open');
      dlg.showModal();
      // Fokus ke jendela itu sendiri agar tombol tutup tidak langsung tersorot.
      dlg.setAttribute('tabindex', '-1');
      dlg.focus({ preventScroll: true });
    }
  }
  $$('dialog.modal').forEach(initDialog);

  // Detail STO yang belum ada di halaman dimuat dari server saat diklik.
  var panelTpl = $('#sto-panel-url');
  function loadPanel(id, btn) {
    var stoId = id.replace(/^sto-/, '');
    if (!panelTpl || !/^\d+$/.test(stoId)) { return; }
    if (btn) { btn.setAttribute('aria-busy', 'true'); }
    document.body.classList.add('is-loading');
    fetch(panelTpl.getAttribute('data-url').replace('__ID__', stoId), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' } })
      .then(function (res) {
        if (res.redirected || !res.ok) { throw new Error(String(res.status)); }
        return res.text();
      })
      .then(function (html) {
        var holder = document.createElement('template');
        holder.innerHTML = html.trim();
        var dlg = holder.content.querySelector('dialog');
        if (!dlg) { throw new Error('kosong'); }
        document.body.appendChild(dlg);
        initDialog(dlg);
        openDialog(dlg);
      })
      .catch(function () {
        // Gagal memuat (mis. sesi habis): buka lewat halaman biasa.
        window.location.href = window.location.pathname + '?open=' + stoId;
      })
      .then(function () {
        if (btn) { btn.removeAttribute('aria-busy'); }
        document.body.classList.remove('is-loading');
      });
  }

  on('click', '[data-dialog-open]', function (e, btn) {
    var id = btn.getAttribute('data-dialog-open');
    var dlg = document.getElementById(id);
    if (!dlg) { loadPanel(id, btn); return; }
    var prefill = btn.getAttribute('data-prefill');
    if (prefill) {
      try { applyPrefill(dlg, JSON.parse(prefill)); } catch (err) { /* abaikan, buka form kosong */ }
    }
    openDialog(dlg);
  });
  on('click', '[data-dialog-close]', function (e, btn) {
    var dlg = btn.closest('dialog');
    if (dlg) { dlg.close(); }
  });
  on('click', '[data-edit-toggle]', function (e, btn) {
    var dlg = btn.closest('dialog');
    dlg.classList.add('is-editing');
    var first = $('form[data-sto-form] input:not([type=hidden])', dlg);
    if (first) { first.focus(); }
    $('.modal-body', dlg).scrollTop = 0;
  });
  on('click', '[data-edit-cancel]', function (e, btn) { resetEdit(btn.closest('dialog')); });
  // Konfirmasi di tempat (hapus STO, batalkan impor, hapus permanen di Sampah).
  on('click', '[data-delete-toggle], [data-confirm-toggle]', function (e, btn) {
    var scope = btn.closest('[data-confirm-scope], dialog');
    if (!scope) { return; }
    var shown = scope.classList.toggle('is-confirming');
    var target = shown ? $('.foot-confirm .btn-secondary, .trash-confirm .btn-secondary', scope) : null;
    if (target) { target.focus(); }
  });
  // Tautan lama "#unggah" membuka jendela unggah.
  if (window.location.hash === '#unggah') { openDialog($('#upload-pdf')); }

  /* ---- Toast di atas modal: jadikan popover (lapisan teratas) setelah modal dibuka ---- */
  var toastStack = $('.toasts[popover]');
  if (toastStack && toastStack.showPopover) {
    try { toastStack.showPopover(); } catch (err) { /* abaikan */ }
  } else if (toastStack) {
    toastStack.removeAttribute('popover');
  }

  /* ---- Isi cepat SOJ/MMJ ---- */
  var quickForm = $('[data-quick-form]');
  if (quickForm) {
    var status = $('[data-quick-status]', quickForm);
    var defaultStatus = status.textContent;
    var inputs = $$('[data-quick-input]', quickForm);
    var submitting = false;
    var dirtyCount = function () {
      return inputs.filter(function (el) { return el.value.trim() !== (el.getAttribute('data-initial') || ''); }).length;
    };
    var refresh = function () {
      inputs.forEach(function (el) {
        var changed = el.value.trim() !== (el.getAttribute('data-initial') || '');
        el.classList.toggle('is-changed', changed);
        el.classList.toggle('is-empty', el.value.trim() === '');
      });
      $$('[data-quick-row]', quickForm).forEach(function (row) {
        row.classList.toggle('is-dirty', !!$('.is-changed', row));
      });
      var n = dirtyCount();
      status.textContent = n ? n + ' kolom diubah, belum disimpan.' : defaultStatus;
      quickForm.classList.toggle('has-changes', n > 0);
    };
    quickForm.addEventListener('input', refresh);
    // Enter = pindah ke kolom berikutnya (seperti spreadsheet), bukan menyimpan.
    quickForm.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' || !e.target.matches('[data-quick-input]')) { return; }
      e.preventDefault();
      var next = inputs[inputs.indexOf(e.target) + (e.shiftKey ? -1 : 1)];
      if (next) { next.focus(); next.select(); }
    });
    quickForm.addEventListener('submit', function () { submitting = true; });
    window.addEventListener('beforeunload', function (e) {
      if (!submitting && dirtyCount() > 0) { e.preventDefault(); e.returnValue = ''; }
    });
    refresh();
  }

  /* ---- Tampilkan / sembunyikan password ---- */
  on('click', '[data-toggle-password]', function (e, btn) {
    var input = document.getElementById(btn.getAttribute('data-toggle-password'));
    if (!input) { return; }
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.classList.toggle('is-on', show);
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
  });

  /* ---- Status memuat pada form (cegah kirim ganda) ---- */
  // Tombol simpan bisa berada di luar <form> (atribut form="id"), mis. pada footer modal.
  function submitButton(form) {
    return $('[data-submit]', form) || (form.id ? $('[data-submit][form="' + form.id + '"]') : null);
  }
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.matches || !form.matches('form[data-loading]')) { return; }
    if (form.getAttribute('data-busy')) { e.preventDefault(); return; }
    form.setAttribute('data-busy', '1');
    var btn = submitButton(form);
    if (!btn) { return; }
    var text = btn.getAttribute('data-loading-text') || 'Memproses…';
    btn.setAttribute('data-original', btn.innerHTML);
    setTimeout(function () {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner" aria-hidden="true"></span><span>' + text + '</span>';
    }, 0);
  });
  // Tombol Kembali pada browser (bfcache): pulihkan tampilan tombol.
  window.addEventListener('pageshow', function (e) {
    if (!e.persisted) { return; }
    $$('form[data-busy]').forEach(function (form) {
      form.removeAttribute('data-busy');
      var btn = submitButton(form);
      if (btn && btn.getAttribute('data-original')) {
        btn.innerHTML = btn.getAttribute('data-original');
        btn.disabled = false;
      }
    });
  });

  // Rekap: cetak / simpan sebagai PDF lewat dialog cetak browser.
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-print]')) { window.print(); }
  });
})();
