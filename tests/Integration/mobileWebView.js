/* Run with node tests/Integration/mobileWebView.js; no browser or server required. */
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const source = fs.readFileSync(path.join(__dirname, '../../view/js/swRegister.js'), 'utf8');

async function checkServiceWorkers(mobile, fromScript = false, rejectCleanup = false) {
    const removed = [];
    const registered = [];
    const logs = [];
    const root = 'https://localhost:8443/avideo/';
    const scopes = [root, 'https://localhost:8443/', root + 'other-app/', 'https://localhost:8443/second-avideo/'];
    const context = {
        URL, URLSearchParams,
        window: {location: {href: root + 'video/123'}},
        document: {currentScript: {getAttribute: () => 'swRegister.js?webSiteRootURL=' + encodeURIComponent(root)}},
        navigator: {
            userAgent: mobile ? 'Mozilla/5.0 AVideoMobileApp' : 'Mozilla/5.0',
            serviceWorker: {
                getRegistrations: () => Promise.resolve(scopes.map(scope => ({
                    scope,
                    unregister() {
                        removed.push(scope);
                        return rejectCleanup ? Promise.reject(new Error('Cleanup denied')) : Promise.resolve(true);
                    }
                }))),
                register: url => { registered.push(url); return Promise.resolve(); }
            }
        },
        console: {log: (...args) => logs.push(args)},
        setTimeout: () => { throw new Error('Valid localhost URL must not retry'); }
    };
    if (!fromScript) context.webSiteRootURL = root;
    vm.runInNewContext(source, context);
    await new Promise(resolve => setImmediate(resolve));
    if (mobile) {
        assert.deepStrictEqual(removed, [root], 'Keep registrations belonging to other applications');
        assert.deepStrictEqual(registered, [], 'Do not register a worker inside the app');
        if (rejectCleanup) assert.ok(logs.some(args => args[0].includes('ERROR')), 'Report cleanup failures');
    } else {
        assert.deepStrictEqual(removed, [], 'Browser registrations must remain intact');
        assert.strictEqual(registered.length, 1);
        assert.ok(registered[0].startsWith(root + 'sw.js?'));
    }
}

function checkMediaSession(supported) {
    const php = fs.readFileSync(path.join(__dirname, '../../plugin/PlayerSkins/mediaSession.php'), 'utf8');
    const script = php.slice(php.indexOf('<script>') + 8, php.indexOf('</script>'))
        .replace(/<\?php[\s\S]*?\?>/g, '{"title":"Fixture"}');
    const requests = [];
    const handlers = [];
    const context = {
        navigator: {mediaSession: {setActionHandler: action => handlers.push(action)}},
        console: {log() {}},
        player: {playlist: () => [], on() {}}, playerPlaylist: [], mediaId: 7,
        empty: value => !value || value.length === 0,
        webSiteRootURL: 'https://example.test/',
        $: {ajax: request => { requests.push(request); request.success({title: 'Updated'}); }}
    };
    if (supported) context.MediaMetadata = function (data) { this.title = data.title; };
    vm.createContext(context);
    vm.runInContext(script, context);
    context.updateMediaSessionMetadata();
    assert.strictEqual(requests.length, supported ? 1 : 0);
    assert.strictEqual(handlers.length, supported ? 9 : 0);
    if (supported) assert.strictEqual(context.navigator.mediaSession.metadata.title, 'Updated');
}

(async () => {
    await checkServiceWorkers(false);
    await checkServiceWorkers(false, true);
    await checkServiceWorkers(true);
    await checkServiceWorkers(true, true);
    await checkServiceWorkers(true, false, true);
    checkMediaSession(false);
    checkMediaSession(true);
    console.log('Mobile WebView regression: passed');
})().catch(error => { console.error(error); process.exitCode = 1; });
