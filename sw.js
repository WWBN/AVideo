importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.5.2/workbox-sw.js');

const webSiteRootURL = this.location.href.split('sw.js?')[0];
const FALLBACK_HTML_URL = webSiteRootURL + 'offline';
const CACHE_NAME = 'avideo-cache-ver-1.2';
console.log('sw CACHE_NAME',CACHE_NAME);
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
    webSiteRootURL + 'plugin/VideoHLS/downloadProtection.js',
    webSiteRootURL + 'view/css/flagstrap/css/flags.css',
    webSiteRootURL + 'view/css/custom/default.css',
    webSiteRootURL + 'node_modules/jquery/dist/jquery.min.js',
    webSiteRootURL + 'node_modules/jquery-lazy/jquery.lazy.min.js',
    webSiteRootURL + 'node_modules/jquery-lazy/jquery.lazy.plugins.min.js',
    webSiteRootURL + 'node_modules/moment/moment.js',
    webSiteRootURL + 'view/js/script.js',
    webSiteRootURL + 'node_modules/jquery-ui-dist/jquery-ui.min.js',
    webSiteRootURL + 'view/bootstrap/js/bootstrap.min.js',
    webSiteRootURL + 'node_modules/sweetalert/dist/sweetalert.min.js',
];

workbox.setConfig({
    debug: false
});


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
        //console.log('networkFallbackStrategyPlugin', args, caches);
        return await caches.match(FALLBACK_HTML_URL, {cacheName: CACHE_NAME});
    }
};
const networkWithFallbackStrategy = {networkTimeoutSeconds: 5, plugins: [networkFallbackStrategyPlugin], cacheName: CACHE_NAME};

const CacheFirst = new workbox.strategies.CacheFirst({cacheName: CACHE_NAME});
const NetworkFirst = new workbox.strategies.NetworkFirst({networkTimeoutSeconds: 2, cacheName: CACHE_NAME});
const NetworkOnly = new workbox.strategies.NetworkOnly({cacheName: CACHE_NAME, plugins: [networkWithFallbackStrategy]});
const CacheOnly = new workbox.strategies.CacheOnly({cacheName: CACHE_NAME, plugins: [ignoreQueryStringPlugin]});
//const StaleWhileRevalidate = new workbox.strategies.StaleWhileRevalidate({cacheName: CACHE_NAME, matchOptions: {ignoreSearch: true}});
const StaleWhileRevalidate = new workbox.strategies.StaleWhileRevalidate({cacheName: CACHE_NAME});

async function getStrategyType(strategyName, args, fallback) {
    if (args.request.url == webSiteRootURL) {
        console.log('getStrategyType', strategyName, args.request.url, fallback);
    }
    try {
        switch (strategyName) {
            case 'CacheFirst':
                //console.log('getStrategyType',strategyName, args.request.url, fallback);
                return await CacheFirst.handle(args);
                break;
            case 'NetworkFirst':
                //console.log('getStrategyType',strategyName, args.request.url, fallback);
                return await NetworkFirst.handle(args);
                break;
            case 'NetworkOnly':
                //console.log('getStrategyType',strategyName, args.request.url, fallback);
                return await NetworkOnly.handle(args);
                break;
            case 'CacheOnly':
                //console.log('getStrategyType',strategyName, args.request.url, fallback);
                return await CacheOnly.handle(args);
                break;
            default:
                //console.log('getStrategyType',strategyName, args.request.url, fallback);
                return await StaleWhileRevalidate.handle(args);
                break;
        }
    } catch (e) {
        console.log('getStrategyType ERROR', strategyName, args.request.url, fallback, e);
        if (fallback) {
            console.log('getStrategyType fallback', FALLBACK_HTML_URL);
            return await caches.match(FALLBACK_HTML_URL, {cacheName: CACHE_NAME});
        }
    }
}

function ruleMatches(rules, extension, request) {
    var ruleIsValid = true;
    //console.log('ruleMatches start', extension, request.url);
    for (var i in rules) {
        var rule = rules[i];
        if (rule) {
            if (i == 'extension') {
                ruleIsValid = rule === extension;
                if (!ruleIsValid) {
                    return false;
                }
                //console.log('ruleMatches', i);
            }
            if (i == 'destination') {
                ruleIsValid = request.destination === rule;
                if (!ruleIsValid) {
                    return false;
                }
                //console.log('ruleMatches', i, rule, request.url);
            }
            if (i == 'url') {
                ruleIsValid = request.url === rule;
                if (!ruleIsValid) {
                    return false;
                }
                //console.log('ruleMatches', i, rule);
            }
        }
    }
    //console.log('ruleMatches end');
    return ruleIsValid;
}

async function processStrategy(strategy, args, extension, strategyName) {
    for (var i in strategy) {
        var rules = strategy[i];
        if (ruleMatches(rules, extension, args.request)) {
            //console.log('processStrategy', args.request.url, extension, strategyName);
            return await getStrategyType(strategyName, args, rules.fallback);
        }
    }
    return false;
}
async function processStrategyDefault(args, extension) {
    //console.log('processStrategyDefault', extension, args.request.destination, args.request.url);
    return await StaleWhileRevalidate.handle(args);
}

async function getStrategy(args) {
    var strategiesNetworkOnly = [];
    strategiesNetworkOnly.push({extension: false, destination: 'document', url: webSiteRootURL, fallback: true});
    strategiesNetworkOnly.push({extension: 'key', destination: false, url: false, fallback: false});
    strategiesNetworkOnly.push({extension: 'php', destination: false, url: false, fallback: false});

    var strategiesNetworkFirst = [];
    strategiesNetworkFirst.push({extension: false, destination: 'document', url: webSiteRootURL + 'offline', fallback: false});
    strategiesNetworkFirst.push({extension: 'ts', destination: false, url: false, fallback: false});
    strategiesNetworkFirst.push({extension: 'mp4', destination: false, url: false, fallback: false});
    strategiesNetworkFirst.push({extension: 'mp4', destination: false, url: false, fallback: false});
    strategiesNetworkFirst.push({extension: 'webm', destination: false, url: false, fallback: false});

    var strategiesCacheFirst = [];
    strategiesCacheFirst.push({extension: false, destination: 'font', url: false, fallback: false});
    strategiesCacheFirst.push({extension: false, destination: 'manifest', url: false, fallback: false});

    var strategiesStaleWhileRevalidate = [];
    strategiesStaleWhileRevalidate.push({extension: false, destination: 'style', url: false, fallback: false});
    strategiesStaleWhileRevalidate.push({extension: false, destination: 'script', url: false, fallback: false});
    strategiesStaleWhileRevalidate.push({extension: false, destination: 'image', url: false, fallback: false});
    strategiesStaleWhileRevalidate.push({extension: false, destination: false, url: webSiteRootURL + 'plugin/Live/stats.json.php?Menu', fallback: false});

    let domain = (new URL(args.request.url));
    var extension = domain.pathname.split('.').pop().toLowerCase();
    //console.log('getStrategy', extension, args);
    return await processStrategy(strategiesCacheFirst, args, extension, 'CacheFirst') ||
            await processStrategy(strategiesStaleWhileRevalidate, args, extension, 'StaleWhileRevalidate') ||
            await processStrategy(strategiesNetworkFirst, args, extension, 'NetworkFirst') ||
            await processStrategy(strategiesNetworkOnly, args, extension, 'NetworkOnly') ||
            await processStrategyDefault(args, extension);

}

workbox.routing.registerRoute(/.*/, getStrategy);

self.addEventListener('install', event => {
    //console.log('sw.js 1', event);
    //event.waitUntil(Promise.all([self.skipWaiting()]));
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => {
        //return cache.addAll(precahedFiles);
        try {
            //console.log('cache.adding', i, file);
            cache.addAll(precahedFiles).then(function () {
                //console.log('cache.added');
            }).catch(function (e) {
                //console.log('cache.add error', e);
            });
        } catch (e) {
            //console.log('cache.add Could not add ', file, e);
        }
        return true;
    }));
});

self.addEventListener('fetch', (e) => {
    return;
    //console.log('sw.js 2', e.request.url);
    //e.respondWith(fetch(e.request));
});