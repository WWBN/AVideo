const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function browser() {
    const timers = new Map(), sockets = [], requests = [], statuses = [], events = [], legacy = [];
    let now = 1000000, sequence = 0;
    const noop = () => {};
    const jq = () => new Proxy({}, { get: () => () => jq() });
    jq.ajax = options => requests.push(options);
    const c = { console: { log: noop, error: noop, debug: noop }, document: { title: 'Chat', dispatchEvent: event => events.push(event) },
        window: {}, $: jq, useSocketIO: true, webSocketURL: 'https://test.invalid', webSocketToken: 'old-token',
        webSiteRootURL: 'https://test.invalid/', webSocketSelfURI: '/', webSocketVideos_id: 0, webSocketLiveKey: '',
        webSocketTypes: { MSG_BATCH: 'MSG_BATCH', MSG_TO_ALL: 'MSG_TO_ALL', TESTING: 'TESTING' },
        isOnline: () => true, isUserOnline: () => false, inIframe: () => false,
        addGetParam: url => url, isValidURL: () => true, CustomEvent: function (type, init) { this.type = type; this.detail = init?.detail; },
        Date: { now: () => now },
        setTimeout: (fn, delay) => { const id = ++sequence; timers.set(id, { fn, at: now + (delay || 0) }); return id; },
        clearTimeout: id => timers.delete(id),
        WebSocket: function () { this.readyState = 0; this.sent = []; this.send = text => this.sent.push(JSON.parse(text)); this.close = () => { this.readyState = 3; this.onclose?.(); }; legacy.push(this); },
        io: () => {
            const listeners = {}, emitted = [];
            const socket = { id: 'socket-' + sockets.length, connected: false, listeners, emitted,
                on: (event, fn) => listeners[event] = fn, emit: (...args) => emitted.push(args),
                disconnect() { this.connected = false; listeners.disconnect?.('io client disconnect'); } };
            sockets.push(socket); return socket;
        }
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../script.js'), 'utf8'), c);
    c.setSocketIconStatus = status => statuses.push(status);
    c.parseSocketResponse = function () {
        if (c.yptSocketResponse.users_id_online !== undefined) c.socketApplyOnlineUsers(c.yptSocketResponse.users_id_online);
    };
    function advance(ms) {
        const target = now + ms; let safety = 0;
        while (true) {
            const entry = [...timers.entries()].filter(([, t]) => t.at <= target).sort((a, b) => a[1].at - b[1].at)[0];
            if (!entry) break;
            if (++safety > 10000) throw Error('Timer loop');
            timers.delete(entry[0]); now = entry[1].at; entry[1].fn();
        }
        now = target;
    }
    function ready() { c.socketConnectIO(); const s = sockets.at(-1); s.connected = true; s.listeners.connect(); s.listeners.yptReady(); return s; }
    return { c, timers, sockets, requests, statuses, events, legacy, advance, ready };
}

test('connect_error releases state and reconnects once with a new token', () => {
    const { c, sockets, requests, statuses, advance } = browser();
    c.socketConnectIO(); sockets[0].listeners.connect_error(new Error('offline'));
    assert.equal(c.socketConnectRequested, false); assert.equal(statuses.at(-1), 'disconnected');
    advance(2000); assert.equal(requests.length, 1);
    c.startSocket(); assert.equal(requests.length, 1);
    requests[0].success({ webSocketToken: 'fresh', webSocketURL: 'https://test.invalid' });
    assert.equal(sockets.length, 2); assert.equal(c.webSocketToken, 'fresh');
});

test('token request has a timeout, shares concurrent refreshes and retries on failure', () => {
    const { c, requests, advance } = browser();
    c.startSocket(); c.startSocket(); c.fetchWebSocketToken(() => {});
    assert.equal(requests.length, 1); assert.equal(requests[0].timeout, 10000);
    requests[0].error(); advance(2000); assert.equal(requests.length, 2);
    requests[1].success({ error: false }); advance(4000); assert.equal(requests.length, 3);
});

test('connected status and queued messages wait for authentication, preserving resource ID', () => {
    const { c, sockets, statuses, advance, events } = browser();
    c.sendSocketMessageToUser('hello', 'test', 7); c.socketConnectIO();
    const s = sockets[0]; s.connected = true; s.listeners.connect(); advance(0);
    assert.equal(statuses.at(-1), 'loading'); assert.equal(s.emitted.length, 0);
    s.listeners.yptReady(); assert.equal(statuses.at(-1), 'connected'); assert.equal(s.emitted.length, 1);
    s.listeners.message({ type: 'MSG_BATCH', users_id_online: [], messages: [] });
    assert.equal(c.socketResourceId, s.id);
    assert.equal(events.filter(e => e.type === 'YPTSocketReady').length, 1);
});

test('older Socket.IO servers become ready on the first authenticated message', () => {
    const { c, sockets, statuses } = browser(); c.socketConnectIO();
    const s = sockets[0]; s.connected = true; s.listeners.connect();
    s.listeners.message({ type: 'MSG_BATCH', users_id_online: [{ users_id: 7 }], messages: [] });
    assert.equal(statuses.at(-1), 'connected'); assert.equal(c.users_id_online[0].users_id, 7);
});

test('failed authentication and stale transport events cannot strand or close a new connection', () => {
    const { c, sockets, advance, requests } = browser(); c.socketConnectIO();
    const old = sockets[0]; old.connected = true; old.listeners.connect();
    advance(35000); assert.equal(c.socketReady, false); advance(2000);
    requests[0].success({ webSocketToken: 'fresh', webSocketURL: 'https://test.invalid' });
    const current = sockets[1]; current.connected = true; current.listeners.connect(); current.listeners.yptReady();
    old.listeners.disconnect('transport close'); assert.equal(c.socket, current); assert.equal(c.socketReady, true);
});

test('offline queue is bounded, uses one timer, expires and reports rejected sends', () => {
    const { c, timers, events, advance } = browser();
    for (let i = 0; i < 1000; i++) assert.equal(c.sendSocketMessageToUser({ i }, 'test', 7), true);
    assert.equal(c.sendSocketMessageToUser('overflow', 'test', 7), false);
    assert.equal(timers.size, 1); assert.equal(c.socketSendQueue.length, 1000);
    advance(30000); assert.equal(c.socketSendQueue.length, 0); assert.equal(timers.size, 0);
    assert.equal(events.filter(e => e.type === 'YPTSocketSendError').length, 2);
});

test('queued sends retain FIFO order, refresh tokens and yield between chunks', () => {
    const { c, ready, advance } = browser();
    for (let i = 0; i < 100; i++) c.sendSocketMessageToUser({ i }, 'test', 7);
    c.webSocketToken = 'fresh'; const s = ready();
    assert.equal(s.emitted.length, 32); advance(100);
    assert.deepEqual(s.emitted.map(e => e[1].msg.i), Array.from({ length: 100 }, (_, i) => i));
    assert.ok(s.emitted.every(e => e[1].webSocketToken === 'fresh'));
});

test('legacy WebSocket initializes and delivers queued sends after its first server response', () => {
    const { c, legacy, advance } = browser(); c.useSocketIO = false;
    c.sendSocketMessageToUser('hello', 'test', 7); c.socketConnectOld();
    const ws = legacy[0]; assert.equal(c.connWS, ws); ws.readyState = 1; ws.onopen();
    ws.onmessage({ data: JSON.stringify({ resourceId: 42, type: 'OPEN_CONNECTION' }) });
    assert.equal(c.isSocketActive(), true); assert.equal(ws.sent.length, 2); assert.equal(ws.sent[0].type, 'TESTING'); assert.equal(c.socketResourceId, 42);
    ws.close(); assert.equal(c.isSocketActive(), false); advance(2000);
});

test('queued payload is a snapshot and invalid payload does not block following messages', () => {
    const { c, ready } = browser(); const msg = { nested: { value: 1 } };
    c.sendSocketMessageToUser(msg, 'test', 7); msg.nested.value = 2;
    const circular = {}; circular.self = circular;
    assert.equal(c.sendSocketMessageToUser(circular, 'test', 7), false);
    const s = ready(); assert.equal(s.emitted.length, 1); assert.equal(s.emitted[0][1].msg.nested.value, 1);
});

test('online labels use IDs in arrays and legacy maps and clear departed users', () => {
    const { c } = browser(); const seen = []; c.setUserOnlineStatus = id => seen.push(id);
    c.socketApplyOnlineUsers([{ users_id: 7 }, { users_id: 42 }]); assert.deepEqual(seen, [7, 42]);
    seen.length = 0; c.socketApplyOnlineUsers({ 42: true }); assert.deepEqual(seen, [7, 42]);
    seen.length = 0; c.setInitialOnlineStatus(); assert.deepEqual(seen, [42]);
    seen.length = 0; c.socketApplyOnlineUsers([]); assert.deepEqual(seen, [42]);
});

test('one failed callback does not prevent later messages in the same batch', () => {
    const { c, ready } = browser(); const seen = []; const s = ready();
    c.processSocketJson = msg => { if (msg.fail) throw Error('plugin callback'); seen.push(msg.n); };
    s.listeners.message({ type: 'MSG_BATCH', messages: [null, { fail: true }, { n: 3 }] });
    assert.deepEqual(seen, [3]);
});
