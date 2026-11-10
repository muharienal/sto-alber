/* STO Alber — peningkatan tampilan (tanpa library, tanpa npm).
   Halaman tetap berfungsi tanpa berkas ini; skrip hanya menambah kenyamanan. */
(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ---- Menu pengguna: tutup saat klik di luar / tekan Esc ---- */
  var menu = $('.usermenu');
  if (menu) {
    document.addEventListener('click', function (e) {
      if (menu.open && !menu.contains(e.target)) { menu.open = false; }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && menu.open) { menu.open = false; $('summary', menu).focus(); }
    });
  }

  /* ---- Toast: hilang otomatis ---- */
  $$('.toast').forEach(function (toast) {
    var closeBtn = $('[data-toast-close]', toast);
    function dismiss() {
      toast.classList.add('is-leaving');
      setTimeout(function () { toast.remove(); }, 220);
    }
    if (closeBtn) { closeBtn.addEventListener('click', dismiss); }
    setTimeout(dismiss, 6000);
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
      hint.textContent = formatSize(file.size) + ' · siap dipratinjau';
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

  /* ---- Dialog / modal ---- */
  function openDialog(dlg) {
    if (dlg && dlg.showModal && !dlg.open) { dlg.showModal(); }
  }
  function formOf(dlg) { return $('form[data-sto-form]', dlg); }

  // Kembalikan form ubah ke isi awal (termasuk baris barang yang ditambah/dihapus).
  function resetEdit(dlg) {
    var form = formOf(dlg);
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

  $$('[data-dialog-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openDialog(document.getElementById(btn.getAttribute('data-dialog-open')));
    });
  });
  $$('[data-dialog-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dlg = btn.closest('dialog');
      if (dlg) { dlg.close(); }
    });
  });
  $$('[data-edit-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dlg = btn.closest('dialog');
      dlg.classList.add('is-editing');
      var first = $('form[data-sto-form] input:not([type=hidden])', dlg);
      if (first) { first.focus(); }
      $('.modal-body', dlg).scrollTop = 0;
    });
  });
  $$('[data-edit-cancel]').forEach(function (btn) {
    btn.addEventListener('click', function () { resetEdit(btn.closest('dialog')); });
  });
  $$('[data-delete-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () { btn.closest('dialog').classList.toggle('is-confirming'); });
  });

  $$('dialog.modal').forEach(function (dlg) {
    var persistent = dlg.hasAttribute('data-persistent');
    // Selama mengisi form (ubah/tambah), Esc dan klik di luar tidak menutup agar isian tidak hilang.
    dlg.addEventListener('cancel', function (e) {
      if (persistent || dlg.classList.contains('is-editing')) { e.preventDefault(); }
    });
    if (!persistent) {
      dlg.addEventListener('click', function (e) {
        if (e.target === dlg && !dlg.classList.contains('is-editing')) { dlg.close(); }
      });
    }
    dlg.addEventListener('close', function () {
      dlg.classList.remove('is-confirming');
      if (!dlg.hasAttribute('data-always-edit') && dlg.classList.contains('is-editing')) { resetEdit(dlg); }
    });
    if (dlg.hasAttribute('data-autoopen') && dlg.showModal) {
      dlg.removeAttribute('open');
      dlg.showModal();
      // Fokus ke jendela itu sendiri agar tombol tutup tidak langsung tersorot.
      dlg.setAttribute('tabindex', '-1');
      dlg.focus({ preventScroll: true });
    }
  });

  /* ---- Form STO: baris barang dinamis dan hitung biaya otomatis ---- */
  var rupiah = function (cents) {
    return (cents / 100).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  };
  $$('form[data-sto-form]').forEach(function (form) {
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
    form._recalc = recalc;

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
      list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, String(nextIndex++)));
      var row = list.lastElementChild;
      var first = $('input', row);
      if (first) { first.focus(); }
      recalc();
    });
    recalc();
  });

  /* ---- Tampilkan / sembunyikan password ---- */
  $$('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.getAttribute('data-toggle-password'));
      if (!input) { return; }
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.classList.toggle('is-on', show);
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
  });

  /* ---- Status memuat pada form (cegah kirim ganda) ---- */
  // Tombol simpan bisa berada di luar <form> (atribut form="id"), mis. pada footer modal.
  function submitButton(form) {
    return $('[data-submit]', form) || (form.id ? $('[data-submit][form="' + form.id + '"]') : null);
  }
  var loadingForms = $$('form[data-loading]');
  loadingForms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
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
  });
  // Tombol Kembali pada browser (bfcache): pulihkan tampilan tombol.
  window.addEventListener('pageshow', function (e) {
    if (!e.persisted) { return; }
    loadingForms.forEach(function (form) {
      form.removeAttribute('data-busy');
      var btn = submitButton(form);
      if (btn && btn.getAttribute('data-original')) {
        btn.innerHTML = btn.getAttribute('data-original');
        btn.disabled = false;
      }
    });
  });
})();
