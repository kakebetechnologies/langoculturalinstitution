(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* mobile navigation */
  var nav = $('#nav'), burger = $('#burger'), scrim = $('#scrim'), close = $('#navclose');
  function setNav(open) {
    if (!nav) return;
    nav.classList.toggle('open', open); scrim.classList.toggle('show', open);
    if (close) close.classList.toggle('show', open);
    document.body.style.overflow = open ? 'hidden' : '';
    if (burger) burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  if (burger) burger.addEventListener('click', function () { setNav(true); });
  if (scrim) scrim.addEventListener('click', function () { setNav(false); });
  if (close) close.addEventListener('click', function () { setNav(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setNav(false); });

  /* mobile drawer: tapping a section opens its sub-menu (accordion); desktop keeps hover menus */
  var mq = window.matchMedia('(max-width: 991px)');
  $$('.nav a').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (!mq.matches) return;
      var li = a.parentNode;
      var isSection = li.classList.contains('has-sub') && li.firstElementChild === a;
      if (isSection && !li.classList.contains('open')) {
        e.preventDefault();
        $$('.nav > li.open').forEach(function (o) { o.classList.remove('open'); o.firstElementChild.setAttribute('aria-expanded', 'false'); });
        li.classList.add('open'); a.setAttribute('aria-expanded', 'true');
        return;
      }
      setNav(false);
    });
  });
  var onChange = function () { if (!mq.matches) setNav(false); };
  if (mq.addEventListener) mq.addEventListener('change', onChange);

  /* hero slider */
  var slides = $$('.hero__slide'), dots = $$('.hero__dots button'), idx = 0, timer;
  function show(i) {
    idx = (i + slides.length) % slides.length;
    slides.forEach(function (s, n) { s.classList.toggle('on', n === idx); });
    dots.forEach(function (d, n) { d.classList.toggle('on', n === idx); });
  }
  if (slides.length > 1) {
    dots.forEach(function (d, n) { d.addEventListener('click', function () { show(n); restart(); }); });
    var restart = function () { clearInterval(timer); timer = setInterval(function () { show(idx + 1); }, 7000); };
    restart();
  }

  /* reveal on scroll + animated counters */
  var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      e.target.classList.add('in');
      if (e.target.dataset.count) countUp(e.target);
      io.unobserve(e.target);
    });
  }, { threshold: .15 }) : null;
  $$('.reveal, [data-count]').forEach(function (el) { io ? io.observe(el) : el.classList.add('in'); });
  function countUp(el) {
    var end = parseInt(el.dataset.count, 10) || 0, t0 = null, dur = 1600;
    function step(t) {
      if (!t0) t0 = t;
      var p = Math.min(1, (t - t0) / dur), v = Math.floor(end * (1 - Math.pow(1 - p, 3)));
      el.textContent = v.toLocaleString('en-US');
      if (p < 1) requestAnimationFrame(step); else el.textContent = end.toLocaleString('en-US');
    }
    requestAnimationFrame(step);
  }

  /* back to top */
  var tl = $('#toplink');
  if (tl) {
    window.addEventListener('scroll', function () { tl.classList.toggle('show', window.scrollY > 700); }, { passive: true });
    tl.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  }

  /* contribution form: optional GPS */
  var gps = $('#gps');
  if (gps) gps.addEventListener('click', function () {
    var out = $('#gpsout');
    if (!navigator.geolocation) { out.textContent = 'Location is not available on this device.'; return; }
    out.textContent = 'Finding your location…';
    navigator.geolocation.getCurrentPosition(function (p) {
      $('#lat').value = p.coords.latitude.toFixed(6); $('#lng').value = p.coords.longitude.toFixed(6);
      out.textContent = 'Location added: ' + p.coords.latitude.toFixed(4) + ', ' + p.coords.longitude.toFixed(4);
    }, function () { out.textContent = 'We could not get your location. You can still describe the place in words.'; }, { enableHighAccuracy: true, timeout: 12000 });
  });

  /* clan picker: reveal the free-text field for "My clan is not listed" */
  $$('[data-clanpick]').forEach(function (box) {
    var sel = $('select', box), txt = $('input', box);
    var sync = function () {
      var other = sel.value === 'other';
      box.classList.toggle('other', other);
      txt.required = other;
      if (!other) txt.value = txt.value;
    };
    sel.addEventListener('change', function () { sync(); if (sel.value === 'other') txt.focus(); });
    sync();
  });

  /* optional account creation on the contribute form */
  $$('[data-acct]').forEach(function (box) {
    var cb = $('input[type=checkbox]', box), pw = $('input[type=password]', box);
    var sync = function () { box.classList.toggle('on', cb.checked); pw.required = cb.checked; };
    cb.addEventListener('change', function () { sync(); if (cb.checked) pw.focus(); });
    sync();
  });

  /* AI translator */
  var tf = $('#transform');
  if (tf) {
    var tin = $('#tx-in'), tout = $('#tx-out'), tunk = $('#tx-unk'), tgo = $('#tx-go');
    var dirOf = function () { var r = $('input[name=dir]:checked', tf); return r ? r.value : 'en-lango'; };
    var labels = function () {
      var en2lg = dirOf() === 'en-lango';
      $('#tx-in-label').textContent = en2lg ? 'English' : 'Leb Lango';
      $('#tx-out-label').textContent = en2lg ? 'Leb Lango' : 'English';
    };
    var run = function () {
      var text = tin.value.trim();
      if (!text) { tout.innerHTML = '<span class="trans__ph">The translation appears here.</span>'; tunk.textContent = ''; return; }
      tgo.disabled = true; tout.classList.add('busy');
      fetch(LCI.base + '/api/translate', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': LCI.csrf }, body: JSON.stringify({ text: text, direction: dirOf() }) })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (j.error) { tout.textContent = j.error; tunk.textContent = ''; return; }
          tout.textContent = j.translation;
          tunk.innerHTML = '';
          if (j.unknown_words && j.unknown_words.length) {
            tunk.appendChild(document.createTextNode('Not in the dictionary yet: '));
            j.unknown_words.forEach(function (w) {
              var a = document.createElement('a'); a.textContent = w; a.href = LCI.base + '/contribute?type=language&word=' + encodeURIComponent(w);
              tunk.appendChild(a); tunk.appendChild(document.createTextNode(' '));
            });
          }
          if (history.replaceState) history.replaceState(null, '', '?dir=' + encodeURIComponent(dirOf()) + '&text=' + encodeURIComponent(text));
        })
        .catch(function () { tout.textContent = 'Sorry — the translator could not be reached. Please try again.'; })
        .then(function () { tgo.disabled = false; tout.classList.remove('busy'); });
    };
    tf.addEventListener('submit', function (e) { e.preventDefault(); run(); });
    $$('input[name=dir]', tf).forEach(function (r) { r.addEventListener('change', function () { labels(); if (tin.value.trim()) run(); }); });
    $$('#tx-sugg button').forEach(function (b) {
      b.addEventListener('click', function () {
        var r = $('input[name=dir][value=' + b.dataset.dir + ']', tf); if (r) r.checked = true;
        tin.value = b.dataset.text; labels(); run(); tin.focus();
      });
    });
    labels();
  }

  /* history page: chapters (details), table of contents, expand / collapse */
  var chapters = $$('details.chap');
  if (chapters.length) {
    var openFor = function (id) { var d = id && document.getElementById(id); if (d && d.tagName === 'DETAILS') d.open = true; };
    openFor(location.hash.slice(1));
    window.addEventListener('hashchange', function () { openFor(location.hash.slice(1)); });
    $$('[data-open]').forEach(function (a) { a.addEventListener('click', function () { openFor(a.dataset.open); }); });
    $$('[data-expand]').forEach(function (b) {
      b.addEventListener('click', function () { var all = b.dataset.expand === 'all'; chapters.forEach(function (d) { d.open = all; }); });
    });
    var links = $$('#toc a'), targets = links.map(function (a) { return document.getElementById(a.getAttribute('href').slice(1)); });
    if ('IntersectionObserver' in window) {
      var spy = new IntersectionObserver(function (es) {
        es.forEach(function (en) {
          if (!en.isIntersecting) return;
          links.forEach(function (a, i) { a.classList.toggle('on', targets[i] === en.target); });
        });
      }, { rootMargin: '-20% 0px -70% 0px' });
      targets.forEach(function (t) { if (t) spy.observe(t); });
    }
  }

  /* donation amount presets */
  $$('.donate-amts input').forEach(function (r) {
    r.addEventListener('change', function () { var a = $('#amount'); if (a) a.value = r.value; });
  });

  /* assistant chat */
  var chat = $('#chat');
  if (chat) {
    var log = $('#chatlog'), form = $('#chatform'), input = $('#chatq');
    var add = function (cls, html) {
      var d = document.createElement('div'); d.className = 'msg ' + cls; d.innerHTML = html;
      log.appendChild(d); log.scrollTop = log.scrollHeight; return d;
    };
    var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var ask = function (q) {
      add('me', esc(q));
      var wait = add('bot', '…');
      fetch(LCI.base + '/api/assistant', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': LCI.csrf }, body: JSON.stringify({ q: q, lang: LCI.lang }) })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          var h = j.answer_html || esc(j.answer || '');
          if (j.sources && j.sources.length) h += '<small>Sources: ' + j.sources.map(function (s) { return '<a href="' + esc(s.url) + '">' + esc(s.title) + '</a>'; }).join(' · ') + '</small>';
          h += '<small><button type="button" data-flag="' + (j.id || 0) + '">Flag this answer as incorrect</button></small>';
          wait.innerHTML = h;
        }).catch(function () { wait.textContent = 'Sorry — something went wrong. Please try again.'; });
    };
    form.addEventListener('submit', function (e) { e.preventDefault(); var q = input.value.trim(); if (!q) return; input.value = ''; ask(q); });
    $$('.sugg button').forEach(function (b) { b.addEventListener('click', function () { ask(b.textContent); }); });
    log.addEventListener('click', function (e) {
      var f = e.target.closest('[data-flag]'); if (!f) return;
      fetch(LCI.base + '/api/assistant/flag', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': LCI.csrf }, body: JSON.stringify({ id: f.dataset.flag }) });
      f.parentNode.textContent = 'Thank you — our cultural reviewers will take a look.';
    });
  }

  /* heritage / species / district map (Leaflet is loaded only on pages that need it) */
  var mapEl = $('#map');
  if (mapEl && window.L && window.LCI_PINS) {
    var m = L.map(mapEl, { scrollWheelZoom: false }).setView([window.LCI_CENTER[0], window.LCI_CENTER[1]], window.LCI_ZOOM || 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxZoom: 18 }).addTo(m);
    var pts = [];
    window.LCI_PINS.forEach(function (p) {
      var mk = L.circleMarker([p.lat, p.lng], { radius: 9, color: '#1c120d', weight: 2, fillColor: p.color || '#a8431f', fillOpacity: .95 }).addTo(m);
      mk.bindPopup('<strong>' + esc2(p.title) + '</strong>' + (p.sub ? '<br>' + esc2(p.sub) : '') + (p.url ? '<br><a href="' + p.url + '">View</a>' : ''));
      pts.push([p.lat, p.lng]);
    });
    if (pts.length > 1) m.fitBounds(pts, { padding: [40, 40], maxZoom: 11 });
    function esc2(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  }
})();

/* lightbox for gallery images */
(function () {
  var links = document.querySelectorAll('a[data-lb]');
  if (!links.length) return;
  var lb = document.createElement('div'); lb.className = 'lb'; lb.innerHTML = '<img alt=""><p></p>'; document.body.appendChild(lb);
  var img = lb.querySelector('img'), cap = lb.querySelector('p');
  links.forEach(function (a) {
    a.addEventListener('click', function (e) { e.preventDefault(); img.src = a.href; cap.textContent = a.dataset.lb; lb.classList.add('on'); });
  });
  lb.addEventListener('click', function () { lb.classList.remove('on'); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') lb.classList.remove('on'); });
})();
