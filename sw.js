/*
 self.addEventListener('install', (e) => {
 e.waitUntil(
 caches.open('fox-store').then((cache) => cache.addAll([
 '/pwa-examples/a2hs/',
 '/pwa-examples/a2hs/index.html',
 '/pwa-examples/a2hs/index.js',
 '/pwa-examples/a2hs/style.css',
 '/pwa-examples/a2hs/images/fox1.jpg',
 '/pwa-examples/a2hs/images/fox2.jpg',
 '/pwa-examples/a2hs/images/fox3.jpg',
 '/pwa-examples/a2hs/images/fox4.jpg',
 ])),
 );
 console.log('sw.js 1',e);
 });
 * 
 */


self.addEventListener('install', event => {
    console.log('sw.js 1', event);
    event.waitUntil(Promise.all([self.skipWaiting()]));
});

self.addEventListener('fetch', (e) => {
    console.log('sw.js 2', e.request.url);
    e.respondWith(
            caches.match(e.request).then((response) => response || fetch(e.request)),
            );
});