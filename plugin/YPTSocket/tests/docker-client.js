// Run only in the local AVideo container's network. All DNS resolves to loopback.
const https = require('node:https');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { io } = require('socket.io-client');
const agent = new https.Agent({ lookup: (_, options, callback) => {
    if (options.all) callback(null, [{ address: '127.0.0.1', family: 4 }]);
    else callback(null, '127.0.0.1', 4);
} });
const noop = () => {};
const timers = new Set(), statuses = [], delivered = [], connections = [];
let requestCount = 0, readyCount = 0;
const jq = () => new Proxy({}, { get: () => () => jq() });
jq.ajax = options => {
    requestCount++;
    if (requestCount === 1) { setImmediate(options.error); return; }
    const url = new URL(options.url); assert.equal(url.hostname, 'vlu.me');
    const request = https.get(url, { agent, timeout: options.timeout, headers: { 'User-Agent': 'Mozilla/5.0 Local client regression test' } }, response => {
        let body = ''; response.on('data', chunk => body += chunk);
        response.on('end', () => { let result; try { result = JSON.parse(body); } catch { options.error(); return; } options.success(result); });
    });
    request.on('timeout', () => request.destroy()); request.on('error', options.error);
};
const context = { console: { log: noop, debug: noop, error: noop }, $: jq, window: {},
    document: { title: 'Socket client regression', dispatchEvent: event => { if (event.type === 'YPTSocketReady') readyCount++; } },
    CustomEvent: function (type, options) { this.type = type; this.detail = options?.detail; },
    setTimeout: (callback, delay) => { const timer = setTimeout(() => { timers.delete(timer); callback(); }, delay); timers.add(timer); return timer; },
    clearTimeout: timer => { clearTimeout(timer); timers.delete(timer); },
    useSocketIO: true, webSiteRootURL: 'https://vlu.me/', webSocketURL: '', webSocketToken: '',
    webSocketSelfURI: 'https://vlu.me/plugin/Chat2/?socketClientRegression=1', webSocketVideos_id: 0, webSocketLiveKey: '',
    isOnline: () => true, isUserOnline: () => false, inIframe: () => false,
    isValidURL: value => !!new URL(value), addGetParam: (value, key, entry) => { const url = new URL(value); url.searchParams.set(key, entry); return url.href; },
    webSocketTypes: require('../AVideo-Socket/SocketMessageType'),
    io: (value, options) => {
        const url = new URL(value); assert.equal(url.hostname, 'vlu.me');
        if (!connections.length) url.port = '1'; // Actual refused transport, not a mocked event.
        const socket = io(url.href, { ...options, agent }); connections.push(socket); return socket;
    }
};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../script.js'), 'utf8'), context);
context.setSocketIconStatus = status => statuses.push(status);
context.parseSocketResponse = () => { const json = context.yptSocketResponse; if (json.users_id_online) context.socketApplyOnlineUsers(json.users_id_online); };
context.registerSocketCallback('socketClientReviewEcho', message => delivered.push(message.seq));
function enqueue(start, end) {
    for (let seq = start; seq < end; seq++) assert.equal(context.sendSocketMessage({type: 'TESTING', msg: {seq}, callback: 'socketClientReviewEcho'}), true);
}
async function until(predicate) {
    const deadline = Date.now() + 20000;
    while (!predicate() && Date.now() < deadline) await new Promise(resolve => setTimeout(resolve, 25));
    assert.ok(predicate(), 'Client did not recover before deadline');
}
(async () => {
    enqueue(0, 90); context.startSocket(); await until(() => delivered.length === 90);
    assert.ok(statuses.includes('disconnected')); assert.ok(requestCount >= 3); assert.equal(readyCount, 1);
    const firstResource = context.socketResourceId;
    context.socket.io.engine.close(); enqueue(90, 130);
    await until(() => delivered.length === 130 && readyCount === 2);
    assert.notEqual(context.socketResourceId, firstResource);
    assert.deepEqual(delivered, Array.from({length: 130}, (_, seq) => seq));
    assert.equal(context.socketSendQueue.length, 0);
    console.log(JSON.stringify({ tokenFailureRecovered: true, transportRefusalRecovered: true, droppedTransportRecovered: true,
        authenticatedConnections: readyCount, orderedEchoes: delivered.length, pendingMessages: context.socketSendQueue.length }));
})().catch(error => { console.error(error.message); process.exitCode = 1; }).finally(() => {
    context.socketDisposeTransport(); timers.forEach(clearTimeout); connections.forEach(s => s.disconnect()); agent.destroy();
});
