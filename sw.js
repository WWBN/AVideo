importScripts('workbox-v6.5.3/workbox-sw.js');

workbox.setConfig({
    modulePathPrefix: 'workbox-v6.5.3/',
    debug: false
});

importScripts('workbox-v6.5.3/workbox-expiration.prod.js');
const webSiteRootURL = this.location.href.split('sw.js?')[0];
const FALLBACK_HTML_URL = webSiteRootURL + 'offline';
const CACHE_NAME = 'avideo-cache-ver-3.6';
const _maxEntries = 400;
const _1_WEEK = 7 * 24 * 60 * 60;

const staticAssetsCacheName = CACHE_NAME + '-static-assets';

function hasCacheParameter(url) {
    return url.includes('cache=');
}

function isRequestValid(request) {
    return (!request.url.match(/\.php/) || request.url.match(/\.js.php/)) && (request.destination === 'script' ||
    request.destination === 'style' ||
    request.destination === 'image' ||
    request.url.match(/\.map/) ||
    request.url.match(/\.ico/) ||
    request.url.match(/\.woff2/));
}

console.log('sw strategy CACHE_NAME', CACHE_NAME);

self.addEventListener('install', (event) => {
    console.log('Service worker installed');
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            const cachedResponse = await cache.match(FALLBACK_HTML_URL);
            if (!cachedResponse) {
                await cache.add(FALLBACK_HTML_URL);
            }
            //console.log('Service worker FALLBACK_HTML_URL added', FALLBACK_HTML_URL);
            // Add other static assets to precache here
        })
    );
});

self.addEventListener('activate', (event) => {
    console.log('Service worker activated');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log("Service worker: Clearing old cache", cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

const cacheFirst = new workbox.strategies.CacheFirst({
    cacheName: staticAssetsCacheName,
    plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
            statuses: [0, 200]
        }),
        new workbox.expiration.ExpirationPlugin({
            maxEntries: _maxEntries, // Adjust this value based on your needs.
            maxAgeSeconds: _1_WEEK, // 1 week
        }),
    ]
});

const staleWhileRevalidate = new workbox.strategies.StaleWhileRevalidate({
    cacheName: staticAssetsCacheName,
    plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
            statuses: [0, 200]
        }),
        new workbox.expiration.ExpirationPlugin({
            maxEntries: _maxEntries, // Adjust this value based on your needs.
            maxAgeSeconds: _1_WEEK, // 1 week
        }),
    ]
});

const networkFirst = new workbox.strategies.NetworkFirst({
    cacheName: staticAssetsCacheName,
    plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({
            statuses: [0, 200]
        }),
        new workbox.expiration.ExpirationPlugin({
            maxEntries: _maxEntries, // Adjust this value based on your needs.
            maxAgeSeconds: _1_WEEK, // 1 week
        }),
    ]
});

workbox.routing.registerRoute(
    ({ request }) => isRequestValid(request),
    async ({ request, event }) => {
        try {
            if (hasCacheParameter(request.url)) {
                //console.log('cacheFirst', request.url);
                return await cacheFirst.handle({ request, event });
            } else {
               // console.log('staleWhileRevalidate', request.url);
                return await staleWhileRevalidate.handle({ request, event });
            }
        } catch (error) {
            console.error('registerRoute networkFirst', request.url);
            return await networkFirst.handle({ request, event });
        }
    }
);
workbox.routing.setCatchHandler(async ({ event }) => {
    console.log('setCatchHandler called', event.request.url);
    if (event.request.destination === 'document') {
        try {
            const networkResponse = await fetch(event.request);
            console.log('networkResponse', networkResponse);
            if (networkResponse.ok) {
                const cache = await caches.open(CACHE_NAME);
                await cache.put(event.request, networkResponse.clone());
                return networkResponse;
            }
        } catch (error) {
            console.error(error);
        }
        // Redirect to the offline page if the user is offline
        if (navigator.onLine === false) {
            console.log('User is offline, redirecting to offline page');
            return Response.redirect(FALLBACK_HTML_URL);
        }
        // Return the cached response if it exists
        const cachedResponse = await caches.match(FALLBACK_HTML_URL);
        console.log('cachedResponse', cachedResponse);
        if (cachedResponse) {
            return cachedResponse;
        }
    }
    return Response.error();
});
