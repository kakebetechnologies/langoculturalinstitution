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
  }
  if (burger) burger.addEventListener('click', function () { setNav(true); });
  if (scrim) scrim.addEventListener('click', function () { setNav(false); });
  if (close) close.addEventListener('click', function () { setNav(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setNav(false); });

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

  /* contribution wizard */
  var wiz = $('#wizard');
  if (wiz) {
    var steps = $$('.wstep', wiz), bars = $$('#wsteps i'), cur = 0;
    var go = function (n) {
      cur = Math.max(0, Math.min(steps.length - 1, n));
      steps.forEach(function (s, i) { s.classList.toggle('on', i === cur); });
      bars.forEach(function (b, i) { b.classList.toggle('on', i <= cur); });
      wiz.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
    $$('[data-next]', wiz).forEach(function (b) {
      b.addEventListener('click', function () {
        var ok = true;
        $$('[required]', steps[cur]).forEach(function (f) { if (!f.checkValidity()) { f.reportValidity(); ok = false; } });
        if (cur === 0 && !$('input[name=type]:checked', wiz)) { alert('Please choose what you would like to contribute.'); ok = false; }
        if (ok) go(cur + 1);
      });
    });
    $$('[data-prev]', wiz).forEach(function (b) { b.addEventListener('click', function () { go(cur - 1); }); });
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
    var pre = new URLSearchParams(location.search).get('type');
    if (pre) { var r = $('input[name=type][value=' + pre + ']', wiz); if (r) { r.checked = true; go(1); window.scrollTo(0, 0); } }
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
    var m = L.map(mapEl, { scrollWheelZoom: false }).setView([window.LCI_CENTER[0], window.LCI_CENTER[1]], 9);
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
