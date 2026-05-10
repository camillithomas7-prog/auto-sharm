// Service Worker minimale Auto Sharm — abilita installabilità PWA + cache statica leggera
const SW_VERSION = 'as-v1';
const STATIC_CACHE = 'as-static-' + SW_VERSION;
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/assets/logo.png',
  '/assets/icon-192.png',
  '/assets/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((c) => c.addAll(STATIC_ASSETS).catch(() => null))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((k) => k !== STATIC_CACHE).map((k) => caches.delete(k))
    )).then(() => self.clients.claim())
  );
});

// Network-first per le pagine, cache fallback. Cache-first per asset statici.
self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // assets/ + icone → cache-first
  if (/^\/(assets|favicon\.ico|manifest\.json)/.test(url.pathname) || /\.(png|jpe?g|webp|svg|ico|woff2?)$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(req).then((cached) => cached || fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(STATIC_CACHE).then((c) => c.put(req, copy));
        return res;
      }).catch(() => cached))
    );
    return;
  }

  // pagine HTML → network-first
  event.respondWith(
    fetch(req).then((res) => {
      const copy = res.clone();
      caches.open(STATIC_CACHE).then((c) => c.put(req, copy));
      return res;
    }).catch(() => caches.match(req).then((cached) => cached || caches.match('/')))
  );
});
