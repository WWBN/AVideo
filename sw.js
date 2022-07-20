importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.5.2/workbox-sw.js');

const webSiteRootURL = this.location.href.split('sw.js?')[0];
const FALLBACK_HTML_URL = webSiteRootURL + 'offline';
const CACHE_NAME = 'avideo-cache-ver-1.0';

const precahedFiles = [
    FALLBACK_HTML_URL,
    webSiteRootURL + 'node_modules/video.js/dist/video-js.min.css',
    webSiteRootURL + 'node_modules/video.js/dist/video.min.js',
    webSiteRootURL + 'plugin/PlayerSkins/loopbutton.css',
    webSiteRootURL + 'plugin/PlayerSkins/player.css',
    webSiteRootURL + 'plugin/VideoResolutionSwitcher/videojs-resolution-switcher.css',
    webSiteRootURL + 'plugin/VideoResolutionSwitcher/videojs-resolution-switcher-v7/videojs-resolution-switcher-v7.js',
    webSiteRootURL + 'plugin/VideoResolutionSwitcher/script.js',
    webSiteRootURL + 'plugin/PlayerSkins/loopbutton.css',
    webSiteRootURL + 'plugin/PlayerSkins/skins/avideo.css',
    webSiteRootURL + 'plugin/PlayerSkins/player.js',
    webSiteRootURL + 'plugin/PlayerSkins/shareButton.css',
    webSiteRootURL + 'plugin/VideoOffline/offlineVideo.css',
    webSiteRootURL + 'plugin/VideoOffline/offlineVideo.js',
    webSiteRootURL + 'plugin/PlayerSkins/autoplayButton.css',
    webSiteRootURL + 'plugin/PlayerSkins/autoplayButton.js',
    webSiteRootURL + 'node_modules/pouchdb/dist/pouchdb.min.js',
    webSiteRootURL + 'view/js/videojs-persistvolume/videojs.persistvolume.js',
    webSiteRootURL + 'plugin/VideoHLS/downloadProtection.js'
];

workbox.setConfig({
    debug: false
});

function routeStaticFiles( { request }) {
    var process =
            request.destination === 'image' ||
            request.destination === 'script';
    if (process) {
        console.log('Cache it routeStaticFiles', request.destination, request.url.endsWith("/"), request);
    } else {
        console.log('Do NOT Cache it routeStaticFiles', request.destination, request.url, request);
    }
    return process;
}


const ignoreQueryStringPlugin = {
    cachedResponseWillBeUsed: async({cacheName, request, matchOptions, cachedResponse, event}) => {
        //console.log('ignoreQueryStringPlugin 1', request.url);
        if (cachedResponse) {
            return cachedResponse;
        }
        //console.log('ignoreQueryStringPlugin 2', request.destination, cacheName, request, matchOptions, cachedResponse, event);
        // this will match same url/diff query string where the original failed
        return caches.match(request.url, {ignoreSearch: true, cacheName: CACHE_NAME});
    }
};
const networkFallbackStrategyPlugin = {
    handlerDidError: async (args) => {
        console.log('networkFallbackStrategyPlugin', args, caches);
        return await caches.match(FALLBACK_HTML_URL, {cacheName: CACHE_NAME});
    }
};
const networkWithFallbackStrategy = {networkTimeoutSeconds: 5, plugins: [networkFallbackStrategyPlugin], cacheName: CACHE_NAME};

const CacheFirst = new workbox.strategies.CacheFirst({cacheName: CACHE_NAME});
const NetworkFirst = new workbox.strategies.NetworkFirst({networkTimeoutSeconds: 2, cacheName: CACHE_NAME});
const NetworkOnly = new workbox.strategies.NetworkOnly({cacheName: CACHE_NAME, plugins: [networkWithFallbackStrategy]});
const CacheOnly = new workbox.strategies.CacheOnly({cacheName: CACHE_NAME, plugins: [ignoreQueryStringPlugin]});
const StaleWhileRevalidate = new workbox.strategies.StaleWhileRevalidate({cacheName: CACHE_NAME, matchOptions: {ignoreSearch: true}});

async function getStrategy(args) {
    if (args.request.destination == 'document') {
        if (webSiteRootURL === args.request.url) {
            try {
                //console.log('getStrategy NetworkOnly 1.1', args.request.destination, args.request.url, args.request);
                return await NetworkOnly.handle(args);
            } catch (e) {
                //console.log('getStrategy NetworkOnly 1.2', args.request.destination, args.request.url, args.request, e);
                return await caches.match(FALLBACK_HTML_URL, {cacheName: CACHE_NAME});
            }
        } else if (webSiteRootURL+'offline'  === args.request.url) {
            return await NetworkFirst.handle(args);
        } else {
            //console.log('getStrategy NetworkOnly 2', args.request.destination, args.request.url, args.request);
            return await NetworkOnly.handle(args);
        }
    }
    if (
            args.request.destination == 'font' || 
            args.request.destination == 'manifest' ||
            args.request.destination == 'image' ||
            args.request.destination == 'script' ||
            args.request.destination == 'style' ||
            args.request.destination == 'video') {

        //console.log('getStrategy 0', args.request.destination, args.request);
        return await CacheFirst.handle(args);
    }
    let domain = (new URL(args.request.url));
    var extension = domain.pathname.split('.').pop().toLowerCase();
    if (
            extension === 'php' ||
            extension === 'm3u8' ||
            extension === 'key') {
        //console.log('getStrategy NetworkOnly 2', args.request.destination, args.request.url, args.request);
        return await NetworkOnly.handle(args);
    }
    if (extension === 'ts') {
        return await NetworkFirst.handle(args);
    }
    console.log('getStrategy 1', args.request.destination, extension, args.request);
    return await StaleWhileRevalidate.handle(args);
}
/*
 for (var i in precahedFiles) {
 console.log('precaching',precahedFiles[i]);
 workbox.precaching.precacheAndRoute([{url: precahedFiles[i], revision: 1}]);
 }
 */

workbox.routing.registerRoute(/.*/, getStrategy);

self.addEventListener('install', event => {
    //console.log('sw.js 1', event);
    //event.waitUntil(Promise.all([self.skipWaiting()]));
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => {

        //return cache.addAll(precahedFiles);

        for (var i in precahedFiles) {
            var file = precahedFiles[i];
            if(typeof file !== 'string'){
                continue;
            }
            try {
                //console.log('cache.adding', i, file);
                cache.add(file).then(function(){
                    //console.log('cache.added');
                }).catch(function(e){
                    //console.log('cache.add error', e);
                });
            } catch (e) {
                console.log('cache.add Could not add ', file, e);
            }
        }
        return true;
    }));
});

self.addEventListener('fetch', (e) => {
    return;
    //console.log('sw.js 2', e.request.url);
    //e.respondWith(fetch(e.request));
});