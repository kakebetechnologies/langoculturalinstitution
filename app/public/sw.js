/* Service worker for the installable web version (PWA) of the Lango App.
   - Static files of the app: cache first, so the app opens instantly and offline.
   - Pages: network first, falling back to the cached app shell when offline.
   - Public API reads (/api/v1/...): network first, falling back to the last copy so lists open offline.
   - Anything with an Authorization header (a signed-in member's own data) is never stored here. */
const CACHE = 'lango-pwa-v1';

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(caches.open(CACHE).then((c) => c.addAll(['./', './manifest.webmanifest']).catch(() => {})));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim()),
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET' || req.headers.has('Authorization')) return;
  const url = new URL(req.url);
  const keep = (res) => {
    if (res && res.ok) {
      const copy = res.clone();
      caches.open(CACHE).then((c) => c.put(req, copy));
    }
    return res;
  };

  if (url.pathname.includes('/api/v1/')) {
    event.respondWith(fetch(req).then(keep).catch(() => caches.match(req)));
    return;
  }
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).then(keep).catch(() => caches.match(req).then((hit) => hit || caches.match('./'))));
    return;
  }
  if (url.origin === self.location.origin) {
    event.respondWith(caches.match(req).then((hit) => hit || fetch(req).then(keep)));
  }
});
