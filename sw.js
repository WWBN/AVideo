self.addEventListener('install', event => {
    //console.log('sw.js 1', event);
    event.waitUntil(Promise.all([self.skipWaiting()]));
});