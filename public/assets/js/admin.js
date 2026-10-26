/* BK Enfab admin UI (vanilla JS + Bootstrap). */
(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var csrfName = ($('meta[name=csrf-name]') || {}).content;
  var csrfHash = ($('meta[name=csrf-hash]') || {}).content;

  // ---- AJAX helper with CSRF token
  window.bkePost = function (url, data) {
    var fd = data instanceof FormData ? data : new FormData();
    if (!(data instanceof FormData) && data) Object.keys(data).forEach(function (k) {
      var v = data[k];
      if (Array.isArray(v)) v.forEach(function (x) { fd.append(k + '[]', x); }); else fd.append(k, v);
    });
    fd.append(csrfName, csrfHash);
    return fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json().then(function (j) { if (j.csrf) csrfHash = j.csrf; if (!r.ok) throw new Error(j.error || 'Request failed'); return j; }); });
  };

  // ---- Confirmation dialogs for destructive actions
  var modalEl = $('#confirmModal'), pending = null;
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (!el || !modalEl || el.dataset.confirmed) return;
    e.preventDefault();
    pending = el;
    $('#confirmBody').textContent = el.getAttribute('data-confirm');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  });
  if (modalEl) $('#confirmOk').addEventListener('click', function () {
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
    if (!pending) return;
    pending.dataset.confirmed = '1';
    if (pending.tagName === 'A') { location.href = pending.href; return; }
    var form = pending.form || (pending.getAttribute('form') && document.getElementById(pending.getAttribute('form')));
    window.__skipDirty = true;
    if (form) { if (form.requestSubmit) form.requestSubmit(pending); else form.submit(); }
    pending = null;
  });

  // ---- Select all rows
  $$('[data-check-all]').forEach(function (cb) {
    cb.addEventListener('change', function () { $$('input[name="ids[]"]', cb.closest('form')).forEach(function (x) { x.checked = cb.checked; }); });
  });

  // ---- Slug auto-generation (until the slug is edited manually)
  function slugify(s) { return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 190); }
  $$('[data-slug-from]').forEach(function (slug) {
    var src = document.getElementById(slug.getAttribute('data-slug-from'));
    if (!src || slug.readOnly) return;
    var manual = slug.value !== '';
    slug.addEventListener('input', function () { manual = slug.value !== ''; });
    src.addEventListener('input', function () { if (!manual) slug.value = slugify(src.value); });
  });

  // ---- Character counters + SERP preview
  $$('[data-counter]').forEach(function (el) {
    var max = +el.getAttribute('data-counter');
    var out = document.createElement('div'); out.className = 'counter form-text';
    el.insertAdjacentElement('afterend', out);
    var upd = function () { var n = el.value.length; out.textContent = n + ' / ' + max + ' characters'; out.classList.toggle('text-danger', n > max); };
    el.addEventListener('input', upd); upd();
  });
  var serp = $('[data-serp]');
  if (serp) {
    var t = $('#fld-meta_title'), d = $('#fld-meta_description'), title = $('#fld-title') || $('#fld-name');
    var upd = function () {
      $('.serp-title', serp).textContent = (t && t.value) || (title && title.value) || 'Page title';
      $('.serp-desc', serp).textContent = (d && d.value) || 'Description will be generated from the content.';
    };
    [t, d, title].forEach(function (x) { if (x) x.addEventListener('input', upd); }); upd();
  }

  // ---- Image preview on file select
  $$('[data-image-input]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var f = inp.files[0]; if (!f || !/^image\//.test(f.type)) return;
      var img = inp.closest('.image-field').querySelector('.img-preview');
      if (!img) { img = document.createElement('img'); img.className = 'img-preview'; inp.closest('.image-field').prepend(img); }
      img.src = URL.createObjectURL(f);
    });
  });

  // ---- Minimal rich text editor (output is sanitized server-side)
  var cmds = [
    ['bold', 'B', 'Bold'], ['italic', 'I', 'Italic'], ['h2', 'H2', 'Heading 2'], ['h3', 'H3', 'Heading 3'], ['p', '¶', 'Paragraph'],
    ['insertUnorderedList', '• List', 'Bulleted list'], ['insertOrderedList', '1. List', 'Numbered list'], ['blockquote', '❝', 'Quote'],
    ['createLink', 'Link', 'Insert link'], ['unlink', 'Unlink', 'Remove link'], ['removeFormat', 'Clear', 'Clear formatting'], ['source', '</>', 'Edit HTML']
  ];
  $$('textarea[data-richtext]').forEach(function (ta) {
    var wrap = document.createElement('div'); wrap.className = 'rte' + (ta.classList.contains('is-invalid') ? ' is-invalid' : '');
    var bar = document.createElement('div'); bar.className = 'rte-toolbar'; bar.setAttribute('role', 'toolbar'); bar.setAttribute('aria-label', 'Formatting');
    var area = document.createElement('div'); area.className = 'rte-area prose'; area.contentEditable = 'true';
    area.setAttribute('role', 'textbox'); area.setAttribute('aria-multiline', 'true');
    var label = document.querySelector('label[for="' + ta.id + '"]'); if (label) area.setAttribute('aria-label', label.textContent.trim());
    area.innerHTML = ta.value;
    cmds.forEach(function (c) {
      var b = document.createElement('button'); b.type = 'button'; b.textContent = c[1]; b.title = c[2]; b.setAttribute('aria-label', c[2]);
      b.addEventListener('click', function () {
        if (c[0] === 'source') {
          var src = ta.style.display !== 'none';
          if (src) { area.innerHTML = ta.value; ta.style.display = 'none'; area.style.display = ''; b.classList.remove('on'); }
          else { ta.value = area.innerHTML; ta.style.display = ''; area.style.display = 'none'; b.classList.add('on'); }
          return;
        }
        area.focus();
        if (['h2', 'h3', 'p', 'blockquote'].indexOf(c[0]) > -1) document.execCommand('formatBlock', false, c[0]);
        else if (c[0] === 'createLink') { var u = prompt('Link URL (https://… or /page)'); if (u && /^(https?:\/\/|\/|mailto:|tel:)/i.test(u)) document.execCommand('createLink', false, u); }
        else document.execCommand(c[0], false, null);
        ta.value = area.innerHTML;
      });
      bar.appendChild(b);
    });
    area.addEventListener('input', function () { ta.value = area.innerHTML; });
    area.addEventListener('paste', function (e) { // paste as plain text to avoid Word/HTML junk
      e.preventDefault();
      var text = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, text);
    });
    ta.parentNode.insertBefore(wrap, ta);
    wrap.appendChild(bar); wrap.appendChild(area); wrap.appendChild(ta);
    ta.style.display = 'none'; ta.removeAttribute('data-richtext');
    ta.form && ta.form.addEventListener('submit', function () { if (ta.style.display === 'none') ta.value = area.innerHTML; });
  });

  // ---- Warn about unsaved changes
  $$('form[data-dirty-check]').forEach(function (f) {
    var dirty = false;
    f.addEventListener('input', function () { dirty = true; });
    f.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) { if (dirty && !window.__skipDirty) { e.preventDefault(); e.returnValue = ''; } });
  });

  // ---- Drag-and-drop ordering (images, sections, menu items)
  $$('[data-sortable]').forEach(function (list) {
    var url = list.getAttribute('data-sortable'), dragged = null;
    list.classList.add('sortable-list');
    list.addEventListener('dragstart', function (e) { dragged = e.target.closest('.sort-item'); if (dragged) { dragged.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; } });
    list.addEventListener('dragend', function () {
      if (!dragged) return;
      dragged.classList.remove('dragging'); dragged = null;
      var ids = $$('.sort-item', list).map(function (x) { return x.getAttribute('data-id'); });
      bkePost(url, { ids: ids }).then(function () { toast('Order saved'); }).catch(function (err) { toast(err.message, true); });
    });
    list.addEventListener('dragover', function (e) {
      e.preventDefault();
      var over = e.target.closest('.sort-item');
      if (!dragged || !over || over === dragged) return;
      var r = over.getBoundingClientRect();
      var after = (list.getAttribute('data-axis') === 'y') ? (e.clientY > r.top + r.height / 2) : (e.clientX > r.left + r.width / 2);
      over.parentNode.insertBefore(dragged, after ? over.nextSibling : over);
    });
    // Keyboard alternative: move buttons
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-move]'); if (!btn) return;
      var item = btn.closest('.sort-item'), dir = btn.getAttribute('data-move');
      if (dir === 'up' && item.previousElementSibling) item.parentNode.insertBefore(item, item.previousElementSibling);
      if (dir === 'down' && item.nextElementSibling) item.parentNode.insertBefore(item.nextElementSibling, item);
      var ids = $$('.sort-item', list).map(function (x) { return x.getAttribute('data-id'); });
      bkePost(url, { ids: ids }).then(function () { toast('Order saved'); btn.focus(); }).catch(function (err) { toast(err.message, true); });
    });
  });

  // ---- Drop zone uploads
  $$('[data-dropzone]').forEach(function (zone) {
    var input = $('input[type=file]', zone), form = zone.closest('form');
    ['dragenter', 'dragover'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('over'); }); });
    zone.addEventListener('drop', function (e) { input.files = e.dataTransfer.files; form.submit(); });
    input.addEventListener('change', function () { if (input.files.length) form.submit(); });
  });

  function toast(msg, bad) {
    var t = document.createElement('div');
    t.className = 'position-fixed bottom-0 end-0 m-3 alert ' + (bad ? 'alert-danger' : 'alert-success') + ' shadow';
    t.setAttribute('role', 'status'); t.textContent = msg; t.style.zIndex = 3000;
    document.body.appendChild(t); setTimeout(function () { t.remove(); }, 2500);
  }
  window.bkeToast = toast;
})();

/* Settings: keep colour picker and hex text field in sync */
(function () {
  document.querySelectorAll('[data-color-for]').forEach(function (picker) {
    var text = document.getElementById(picker.getAttribute('data-color-for'));
    if (!text) return;
    picker.addEventListener('input', function () { text.value = picker.value; text.dispatchEvent(new Event('input', { bubbles: true })); });
    text.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(text.value)) picker.value = text.value; });
  });
})();
