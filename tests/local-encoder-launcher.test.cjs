const assert = require('node:assert/strict');
const test = require('node:test');
const launcher = require('../plugin/CustomizeAdvanced/localEncoderLauncher.js');

function harness(fragment = '#open=1', login = null) {
    const elements = {};
    function element() {
        return { handlers: {}, classList: { remove() {}, add() {} }, setAttribute() {}, removeAttribute() {},
            addEventListener(name, fn) { this.handlers[name] = fn; }, removeEventListener() {},
            remove() { this.removed = true; }, contentWindow: { postMessage() {} } };
    }
    for (const name of ['[data-open]', '[data-status]', '[data-check]', '[data-control]']) elements[name] = element();
    const frames = [];
    const handlers = {};
    const timers = new Map();
    let nextTimer = 1;
    const win = {
        AbortController,
        location: { href: 'https://avideo.test/subsite/plugin/CustomizeAdvanced/localEncoder.php', hash: fragment, replace(value) { this.redirect = value; } },
        navigator: { userAgent: 'Desktop Chrome', platform: 'Win32' },
        crypto: { getRandomValues(value) { value.fill(7); } },
        setTimeout(fn) { const id = nextTimer++; timers.set(id, fn); return id; },
        clearTimeout(id) { timers.delete(id); },
        addEventListener(name, fn) { handlers[name] = fn; },
        removeEventListener() {},
    };
    const panel = { querySelector(name) { return elements[name]; } };
    const doc = { getElementById() { return panel; },
        createElement: element, body: { appendChild(frame) { frames.push(frame); } } };
    const config = { login, site: 'https://avideo.test/subsite/', text: { checking: 'checking', ready: 'ready', unknown: 'unknown', opening: 'opening', invalid: 'invalid', open: 'open', computer: 'computer', connecting: 'connecting', loginFailed: 'loginFailed' } };
    launcher.init(config, win, doc);
    return { win, elements, frames, handlers, timers };
}

test('nested media query survives handoff without credentials or local-file paths', () => {
    const request = launcher.context('https://avideo.test/subsite/', '#open=1&url=' + encodeURIComponent('https://provider.test/watch?v=1&list=2'));
    const link = new URL(launcher.link(request));
    assert.equal(link.searchParams.get('site'), 'https://avideo.test/subsite/');
    assert.equal(link.searchParams.get('url'), 'https://provider.test/watch?v=1&list=2');
    for (const url of ['file:///private/file', 'javascript:alert(1)', 'https://user:secret@provider.test/']) {
        assert.throws(() => launcher.context('https://avideo.test/', '#url=' + encodeURIComponent(url)));
    }
});

test('only a matching frame, origin, nonce and protocol confirms the running app', () => {
    const state = harness();
    const sender = state.frames[0];
    const message = { origin: 'http://127.0.0.1:47800', source: sender.contentWindow,
        data: { type: 'avideo-local-encoder:ready', nonce: '07'.repeat(16), protocol: 1 } };
    for (const override of [{ origin: 'https://evil.test' }, { source: {} }, { data: { ...message.data, nonce: 'wrong' } }, { data: { ...message.data, protocol: 2 } }]) {
        state.handlers.message({ ...message, ...override });
        assert.equal(state.elements['[data-status]'].textContent, 'checking');
    }
    state.handlers.message(message);
    assert.equal(state.elements['[data-status]'].textContent, 'ready');
    assert.ok(state.elements['[data-open]'].href.startsWith('avideo-local-encoder://open?'));
    assert.ok(state.win.location.redirect.startsWith('http://127.0.0.1:47800/#avideo='));
    assert.equal(state.timers.size, 0);
    assert.ok(state.frames.every(frame => frame.removed));
});

test('blocked or absent discovery stays inconclusive and leaves native opening available', () => {
    const state = harness();
    [...state.timers.values()].forEach(fn => fn());
    assert.equal(state.elements['[data-status]'].textContent, 'unknown');
    assert.ok(state.elements['[data-open]'].href.startsWith('avideo-local-encoder://open?'));
    assert.equal(state.win.location.redirect, undefined);
    state.elements['[data-open]'].handlers.click();
    assert.equal(state.elements['[data-status]'].textContent, 'opening');
    assert.equal(state.win.location.redirect, undefined);
});

test('a late reply after timeout or cancellation cannot change state or redirect', () => {
    const state = harness();
    const source = state.frames[0].contentWindow;
    state.handlers.pagehide();
    state.handlers.message({ source, origin: 'http://127.0.0.1:47800', data: { type: 'avideo-local-encoder:ready', nonce: '07'.repeat(16), protocol: 1 } });
    assert.equal(state.win.location.redirect, undefined);
    assert.equal(state.timers.size, 0);
});

test('an invalid media URL keeps manual opening available without silently redirecting', () => {
    const state = harness('#open=1&url=' + encodeURIComponent('file:///private/video'));
    assert.equal(state.elements['[data-status]'].textContent, 'invalid');
    assert.ok(state.elements['[data-open]'].href.startsWith('avideo-local-encoder://open?'));
    assert.equal(state.frames.length, 0);
    assert.equal(state.win.location.redirect, undefined);
});

test('an opening request in the same guide updates the handoff instead of using stale media', () => {
    const state = harness('');
    const oldSource = state.frames[0].contentWindow;
    state.win.location.hash = '#open=1&url=' + encodeURIComponent('https://provider.test/new?v=2&x=3');
    state.handlers.hashchange();
    const native = new URL(state.elements['[data-open]'].href);
    assert.equal(native.searchParams.get('url'), 'https://provider.test/new?v=2&x=3');
    state.handlers.message({ source: oldSource, origin: 'http://127.0.0.1:47800', data: { type: 'avideo-local-encoder:ready', nonce: '07'.repeat(16), protocol: 1 } });
    assert.equal(state.win.location.redirect, undefined);
});

function ready(state, type = 'avideo-local-encoder:ready', extra = {}) {
    state.handlers.message({ source: state.frames[0].contentWindow, origin: 'http://127.0.0.1:47800',
        data: { type, protocol: 1, nonce: '07'.repeat(16), login_challenge: 'c'.repeat(43), ...extra } });
}

test('authorization codes use same-origin POST and exact-frame messages, never navigation or native links', async () => {
    const state = harness('#open=1', { url: 'https://avideo.test/subsite/login.json.php', token: 'csrf-test' });
    const requests = [], messages = [];
    state.win.fetch = async (url, options) => { requests.push({ url, options }); return { ok: true, json: async () => ({ error: false, code: 'b'.repeat(64) }) }; };
    state.frames[0].contentWindow.postMessage = (data, target) => messages.push({ data, target });
    ready(state);
    await new Promise(setImmediate);
    assert.equal(state.win.location.redirect, undefined);
    assert.equal(requests[0].options.method, 'POST');
    assert.equal(new URLSearchParams(requests[0].options.body).get('globalToken'), 'csrf-test');
    assert.equal(messages[0].data.code, 'b'.repeat(64));
    assert.equal(messages[0].target, 'http://127.0.0.1:47800');
    state.handlers.message({ source: {}, origin: 'http://127.0.0.1:47800', data: { type: 'avideo-local-encoder:authorized', nonce: '07'.repeat(16), protocol: 1, ok: true } });
    await new Promise(setImmediate);
    assert.equal(state.win.location.redirect, undefined);
    ready(state, 'avideo-local-encoder:authorized', { ok: true });
    await new Promise(setImmediate);
    assert.ok(state.win.location.redirect.endsWith('&state=' + '07'.repeat(16)));
    assert.ok(!state.win.location.redirect.includes('b'.repeat(64)));
    assert.ok(!state.elements['[data-open]'].href.includes('b'.repeat(64)));
});

test('missing or expired browser sign-in falls back to manual connection', async () => {
    const state = harness('#open=1', { url: 'https://avideo.test/subsite/login.json.php', token: 'expired' });
    state.win.fetch = async () => ({ ok: false });
    ready(state);
    await new Promise(setImmediate);
    assert.ok(state.win.location.redirect.startsWith('http://127.0.0.1:47800/#avideo='));
    assert.ok(!state.win.location.redirect.includes('&state='));
});

test('page navigation cancels a pending authorization, including a real pagehide event', async () => {
    const state = harness('#open=1', { url: 'https://avideo.test/subsite/login.json.php', token: 'csrf-test' });
    let finish;
    state.win.fetch = () => new Promise(resolve => { finish = resolve; });
    ready(state);
    state.handlers.pagehide({ type: 'pagehide' });
    finish({ ok: true, json: async () => ({ error: false, code: 'b'.repeat(64) }) });
    await new Promise(setImmediate);
    assert.equal(state.win.location.redirect, undefined);
    assert.ok(state.frames.every(frame => frame.removed));
});


test('an older running instance does not win over an instance supporting automatic login', async () => {
    const state = harness('#open=1', { url: 'https://avideo.test/subsite/login.json.php', token: 'test' });
    state.win.fetch = async () => ({ ok: false });
    ready(state, 'avideo-local-encoder:ready', { login_challenge: undefined });
    assert.equal(state.win.location.redirect, undefined);
    state.handlers.message({ source: state.frames[1].contentWindow, origin: 'http://127.0.0.1:47801',
        data: { type: 'avideo-local-encoder:ready', nonce: '07'.repeat(16), protocol: 1, login_challenge: 'c'.repeat(43) } });
    await new Promise(setImmediate);
    assert.ok(state.win.location.redirect.startsWith('http://127.0.0.1:47801/'));
    assert.equal(state.timers.size, 0);
});


test('native opening resumes automatic sign-in after the previously closed app becomes ready', async () => {
    const state = harness('#open=1', { url: 'https://avideo.test/subsite/login.json.php', token: 'test' });
    [...state.timers.values()].forEach(fn => fn());
    state.elements['[data-open]'].handlers.click();
    const frame = state.frames[10];
    const sent = [];
    frame.contentWindow.postMessage = data => sent.push(data);
    state.win.fetch = async () => ({ ok: true, json: async () => ({ error: false, code: 'b'.repeat(64) }) });
    const message = { source: frame.contentWindow, origin: 'http://127.0.0.1:47800', data: {
        type: 'avideo-local-encoder:ready', protocol: 1, nonce: '07'.repeat(16), login_challenge: 'c'.repeat(43) } };
    state.handlers.message(message);
    await new Promise(setImmediate);
    assert.equal(sent[0].type, 'avideo-local-encoder:authorize');
    assert.equal(state.win.location.redirect, undefined);
    state.handlers.message({ ...message, data: { ...message.data, type: 'avideo-local-encoder:authorized', ok: true } });
    await new Promise(setImmediate);
    assert.ok(state.win.location.redirect.endsWith('&state=' + '07'.repeat(16)));
});
