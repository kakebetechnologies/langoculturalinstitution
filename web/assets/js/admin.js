(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* mobile sidebar */
  var side = $('#side'), btn = $('#sideBtn');
  if (btn) btn.addEventListener('click', function (e) { e.stopPropagation(); side.classList.toggle('open'); });
  document.addEventListener('click', function (e) { if (side && side.classList.contains('open') && !side.contains(e.target)) side.classList.remove('open'); });

  /* confirm dialogs on destructive forms */
  $$('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
  });

  /* rich text editor */
  $$('.rte').forEach(function (r) {
    var body = $('.rte__body', r), ta = $('textarea', r);
    $$('.rte__bar button', r).forEach(function (b) {
      b.addEventListener('mousedown', function (e) { e.preventDefault(); });
      b.addEventListener('click', function () {
        var cmd = b.dataset.cmd, val = b.dataset.val || null;
        if (cmd === 'createLink') { val = prompt('Link address (https://…)'); if (!val) return; }
        if (cmd === 'formatBlock' && val) val = '<' + val + '>';
        body.focus(); document.execCommand(cmd, false, val);
      });
    });
    var sync = function () { ta.value = body.innerHTML; };
    body.addEventListener('input', sync);
    r.closest('form').addEventListener('submit', sync);
    body.addEventListener('paste', function (e) {
      e.preventDefault();
      var t = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, t);
    });
    sync();
  });

  /* auto-generate a slug from the title field while the slug is untouched */
  $$('input[data-slug-from]').forEach(function (s) {
    var src = $('[name="' + s.dataset.slugFrom + '"]');
    if (!src || s.value) return;
    var touched = false;
    s.addEventListener('input', function () { touched = true; });
    src.addEventListener('input', function () {
      if (touched) return;
      s.value = src.value.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    });
  });

  /* warn before leaving a form with unsaved changes */
  var form = $('form.formgrid'), dirty = false;
  if (form) {
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  }
})();

/* notification bell: dropdown + live count */
(function () {
  'use strict';
  var bell = document.getElementById('bell');
  if (!bell) return;
  var btn = document.getElementById('bellBtn'), menu = document.getElementById('bellMenu'), badge = document.getElementById('bellN'), list = document.getElementById('bellList');
  var baseTitle = document.title.replace(/^\(\d+\+?\)\s/, '');
  function setCount(n) {
    badge.hidden = !(n > 0); badge.textContent = n > 99 ? '99+' : String(n);
    document.title = (n > 0 ? '(' + n + ') ' : '') + baseTitle;
  }
  setCount(parseInt(badge.textContent, 10) || 0);
  function close() { menu.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
  btn.addEventListener('click', function (e) { e.stopPropagation(); menu.hidden = !menu.hidden; btn.setAttribute('aria-expanded', menu.hidden ? 'false' : 'true'); });
  document.addEventListener('click', function (e) { if (!bell.contains(e.target)) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  function poll() {
    fetch(bell.dataset.poll, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (j) { if (j) { list.innerHTML = j.html; setCount(j.count); } })
      .catch(function () {});
  }
  setInterval(poll, 45000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
})();
