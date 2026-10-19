// node --test tests/social-media-publisher-message-origin.test.cjs
// plugin/SocialMediaPublisher/script.js saves an OAuth connection received by postMessage. Only the
// restream.ypt.me popup (or our own parent page forwarding it) may do that, and only while this
// window opened the popup; any other window could otherwise plant its own token in the user's account.
const assert = require('node:assert/strict');
const test = require('node:test');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.join(__dirname, '..');
const pluginScript = fs.readFileSync(path.join(root, 'plugin/SocialMediaPublisher/script.js'), 'utf8');

function sandbox(isIframe) {
    const posts = [];
    const handlers = {};
    const top = {};
    const win = {
        location: { origin: 'https://site.test' },
        addEventListener(name, fn) { (handlers[name] = handlers[name] || []).push(fn); },
        open() { return { closed: false, close() {} }; },
        setInterval() { return 1; },
        clearInterval() {},
    };
    win.parent = isIframe ? top : win;
    const $ = function () { return { val() { return ''; }, prop() { return this; }, focus() {} }; };
    $.ajax = function (options) { posts.push(options); };
    $.trim = (s) => String(s).trim();
    const context = {
        window: win,
        $,
        console: { warn() {}, log() {} },
        yptURL: 'https://restream.ypt.me/',
        webSiteRootURL: 'https://site.test/',
        modal: { showPleaseWait() {}, hidePleaseWait() {} },
        avideoToastWarning() {}, avideoToastSuccess() {}, avideoAlertError() {}, avideoToastInfo() {},
        openWindowWithPost() { return { closed: false, close() {} }; },
        __: (s) => s,
        URL,
        setTimeout, clearTimeout,
    };
    vm.createContext(context);
    vm.runInContext(pluginScript, context);
    // loading the file twice must not register the listener twice
    vm.runInContext(pluginScript, context);
    return { context, win, top, posts, handlers };
}

const connection = { provider: 'youtube', name: 'youtube: me@example.com', parameters: '{"restream.ypt.me":{}}' };

function fire(env, origin, source) {
    for (const fn of env.handlers.message) {
        fn({ origin, source, data: connection });
    }
}

test('the listener is registered once even when the script is loaded twice', () => {
    const env = sandbox(false);
    assert.equal(env.handlers.message.length, 1);
});

test('a connection from the broker popup is saved while this window opened it', () => {
    const env = sandbox(false);
    vm.runInContext("openYPT('youtube')", env.context);
    fire(env, 'https://restream.ypt.me', {});
    assert.equal(env.posts.length, 1);
    assert.match(env.posts[0].url, /Publisher_user_preferences\/add\.json\.php$/);
    assert.equal(env.posts[0].data.provider, 'youtube');
});

test('a message from any other origin is ignored', () => {
    const env = sandbox(false);
    vm.runInContext("openYPT('youtube')", env.context);
    fire(env, 'https://attacker.test', {});
    fire(env, 'https://restream.ypt.me.attacker.test', {});
    assert.equal(env.posts.length, 0);
});

test('a broker message is ignored when this window did not open the popup', () => {
    const env = sandbox(false);
    fire(env, 'https://restream.ypt.me', {});
    assert.equal(env.posts.length, 0);
});

test('inside the modal iframe, only the forward from our own parent page is accepted', () => {
    const env = sandbox(true);
    vm.runInContext("openYPT('youtube')", env.context);
    fire(env, 'https://site.test', {}); // same origin but not the parent window
    assert.equal(env.posts.length, 0);
    fire(env, 'https://site.test', env.top);
    assert.equal(env.posts.length, 1);
});

test('view/js/script.js forwards only messages that came from the broker', () => {
    const source = fs.readFileSync(path.join(root, 'view/js/script.js'), 'utf8');
    assert.match(source, /typeof e\.data\.provider !== 'undefined' && isRestreamerBrokerOrigin\(e\.origin\)/);
    const fn = source.match(/function isRestreamerBrokerOrigin\(origin\) \{[\s\S]*?\n\}/);
    assert.ok(fn, 'isRestreamerBrokerOrigin() not found');
    const context = { URL };
    vm.createContext(context);
    vm.runInContext(fn[0], context);
    assert.equal(context.isRestreamerBrokerOrigin('https://restream.ypt.me'), true);
    assert.equal(context.isRestreamerBrokerOrigin('https://attacker.test'), false);
    context.yptURL = 'https://broker.example/';
    assert.equal(context.isRestreamerBrokerOrigin('https://broker.example'), true);
    assert.equal(context.isRestreamerBrokerOrigin('https://restream.ypt.me'), false);
});
