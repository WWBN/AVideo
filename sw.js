/* sw.js — Same-Origin Only (avoids opaque responses for cross-origin) */

importScripts('workbox-v6.5.3/workbox-sw.js');
workbox.setConfig({ modulePathPrefix: 'workbox-v6.5.3/', debug: false });
importScripts('workbox-v6.5.3/workbox-expiration.prod.js');

const webSiteRootURL = self.location.href.split('sw.js?')[0];
const FALLBACK_HTML_URL = webSiteRootURL + 'offline';

const CACHE_NAME = 'avideo-cache-ver-3.6';
const STATIC_ASSETS_CACHE = CACHE_NAME + '-static-assets';
const MAX_ENTRIES = 400;
const ONE_WEEK = 7 * 24 * 60 * 60; // seconds

// ---------- Helpers ----------
function hasCacheParameter(url) {
  return url.includes('cache=');
}

function isSameOrigin(request) {
  try { return new URL(request.url).origin === self.location.origin; }
  catch { return false; }
}

// Only cache same-origin static assets (no PHP except *.js.php)
function isStaticAsset(request) {
  if (!isSameOrigin(request)) return false;
  const url = request.url;
  const dest = request.destination;
  const isPhp = /\.php($|\?)/i.test(url);
  const isJsPhp = /\.js\.php($|\?)/i.test(url);

  return (
    (!isPhp || isJsPhp) &&
    (
      dest === 'script' ||
      dest === 'style'  ||
      dest === 'image'  ||
      /\.map($|\?)/i.test(url) ||
      /\.ico($|\?)/i.test(url) ||
      /\.woff2($|\?)/i.test(url)
    )
  );
}

// Only same-origin navigations (HTML)
function isDocument(request) {
  return isSameOrigin(request) && request.destination === 'document';
}

console.log('[SW] CACHE_NAME:', CACHE_NAME);

// ---------- Install ----------
self.addEventListener('install', (event) => {
  console.log('[SW] installed');
  event.waitUntil(
    caches.open(CACHE_NAME).then(async (cache) => {
      const cached = await cache.match(FALLBACK_HTML_URL);
      if (!cached) {
        try { await cache.add(FALLBACK_HTML_URL); } catch (e) { /* offline page optional */ }
      }
    })
  );
  self.skipWaiting?.();
});

// ---------- Activate ----------
self.addEventListener('activate', (event) => {
  console.log('[SW] activated');
  event.waitUntil(
    caches.keys().then((names) =>
      Promise.all(
        names.map((name) => {
          if (name !== CACHE_NAME && name !== STATIC_ASSETS_CACHE) {
            console.log('[SW] delete old cache:', name);
            return caches.delete(name);
          }
        })
      )
    )
  );
  self.clients?.claim?.();
});

// ---------- Strategies ----------
const cacheFirst = new workbox.strategies.CacheFirst({
  cacheName: STATIC_ASSETS_CACHE,
  plugins: [
    new workbox.cacheableResponse.CacheableResponsePlugin({ statuses: [200] }),
    new workbox.expiration.ExpirationPlugin({ maxEntries: MAX_ENTRIES, maxAgeSeconds: ONE_WEEK }),
  ],
});

const staleWhileRevalidate = new workbox.strategies.StaleWhileRevalidate({
  cacheName: STATIC_ASSETS_CACHE,
  plugins: [
    new workbox.cacheableResponse.CacheableResponsePlugin({ statuses: [200] }),
    new workbox.expiration.ExpirationPlugin({ maxEntries: MAX_ENTRIES, maxAgeSeconds: ONE_WEEK }),
  ],
});

const networkFirstDocs = new workbox.strategies.NetworkFirst({
  cacheName: CACHE_NAME, // keep docs in main cache
  plugins: [
    new workbox.cacheableResponse.CacheableResponsePlugin({ statuses: [200] }),
    new workbox.expiration.ExpirationPlugin({ maxEntries: MAX_ENTRIES, maxAgeSeconds: ONE_WEEK }),
  ],
});

// ---------- Routes ----------

// 1) Same-origin HTML documents → Network First (offline fallback)
workbox.routing.registerRoute(
  ({ request }) => isDocument(request),
  async ({ request, event }) => {
    try {
      const resp = await networkFirstDocs.handle({ request, event });
      if (resp) return resp;
    } catch (e) { /* fall through */ }

    // Offline fallback (from install precache)
    const cached = await caches.match(FALLBACK_HTML_URL);
    if (cached) return cached;

    return Response.error();
  }
);

// 2) Same-origin static assets → CacheFirst (when ?cache=...) or StaleWhileRevalidate
workbox.routing.registerRoute(
  ({ request }) => isStaticAsset(request),
  async ({ request, event }) => {
    const strategy = hasCacheParameter(request.url) ? cacheFirst : staleWhileRevalidate;
    try {
      const resp = await strategy.handle({ request, event });
      return resp;
    } catch (e) {
      // As a last resort, try network for the asset
      try { return await fetch(request); } catch { return Response.error(); }
    }
  }
);

// ---------- Global Catch (only for same-origin navigations) ----------
workbox.routing.setCatchHandler(async ({ event }) => {
  const req = event.request;

  if (isDocument(req)) {
    // If online fetch failed, try cached doc; otherwise offline page.
    const cache = await caches.open(CACHE_NAME);
    const cachedDoc = await cache.match(req);
    if (cachedDoc) return cachedDoc;

    const offline = await caches.match(FALLBACK_HTML_URL);
    if (offline) return offline;
  }
  return Response.error();
});

// ---------- Safety: do not intercept cross-origin at all ----------
// Workbox won’t route what we don’t match; since all matchers enforce isSameOrigin(),
// cross-origin (e.g., CDN) requests pass through to the network unmodified,
// avoiding opaque responses in non-no-cors contexts.
