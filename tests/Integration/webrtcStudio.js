/* Run: node tests/Integration/webrtcStudio.js. No camera, network or live server required. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function fixture({ studio = true } = {}) {
    const nodes = new Map(), ready = [], timers = new Map(), events = {}, managerEvents = {}, sent = [];
    const windowEvents = {}, deviceEvents = {}, streams = [], recorders = [], storage = new Map();
    let timerId = 0, mediaError = null, mediaCalls = 0, lastConstraints, pendingMedia;
    function node(id) {
        if (!nodes.has(id)) nodes.set(id, { id, classes: new Set(), attrs: {}, props: {}, value: '', text: '', options: [] });
        return nodes.get(id);
    }
    const body = node('body');
    body.classList = { add: name => body.classes.add(name), remove: name => body.classes.delete(name), contains: name => body.classes.has(name) };
    const document = { body, activeElement: null, getElementById: id => studio || id !== 'webrtcStatus' ? node(id) : null };
    function $(selector) {
        const n = typeof selector === 'string' ? node(selector.replace(/^#/, '')) : selector;
        const api = {
            ready: fn => ready.push(fn),
            addClass: names => { names.split(' ').forEach(x => n.classes.add(x)); return api; },
            removeClass: names => { names.split(' ').forEach(x => n.classes.delete(x)); return api; },
            hasClass: name => n.classes.has(name),
            toggleClass: (name, flag) => { if (flag) n.classes.add(name); else n.classes.delete(name); return api; },
            prop: (key, value) => { n.props[key] = value; return api; },
            attr: (key, value) => { if (value === undefined) return n.attrs[key]; n.attrs[key] = value; return api; },
            text: value => { n.text = value; return api; },
            val: value => { if (value === undefined) return n.value; n.value = value; return api; },
            empty: () => { n.options = []; n.value = ''; return api; },
            append: value => { n.options.push(value); return api; },
            click: fn => { n.click = fn; return api; },
            on: (event, fn) => { n[event] = fn; return api; },
            trigger: event => { if (event === 'focus') document.activeElement = n; return api; }
        };
        return api;
    }
    function stream(audio = true) {
        const tracks = ['video', ...(audio ? ['audio'] : [])].map(kind => ({
            kind, readyState: 'live', stop() { this.readyState = 'ended'; },
            addEventListener(event, fn) { this[event] = fn; }
        }));
        const result = { getTracks: () => tracks, getVideoTracks: () => tracks.filter(t => t.kind === 'video'), getAudioTracks: () => tracks.filter(t => t.kind === 'audio') };
        streams.push(result);
        return result;
    }
    class Recorder {
        constructor(source) { this.source = source; this.state = 'inactive'; recorders.push(this); }
        start() { this.state = 'recording'; }
        stop() { this.state = 'inactive'; this.ondataavailable({ data: { size: 1 } }); }
    }
    const socket = {
        connected: true, id: 'test-socket', on: (event, fn) => events[event] = fn,
        emit: (event, data) => sent.push({ event, data }), connect() { this.connected = true; },
        io: { on: (event, fn) => managerEvents[event] = fn }
    };
    const mediaDevices = {
        enumerateDevices: async () => [
            { kind: 'videoinput', deviceId: 'camera', label: 'Camera' },
            { kind: 'audioinput', deviceId: 'mic', label: 'Microphone' }
        ],
        getUserMedia: async constraints => {
            mediaCalls++; lastConstraints = constraints;
            if (mediaError) throw { name: mediaError };
            if (pendingMedia) return pendingMedia;
            return stream();
        },
        getDisplayMedia: async () => stream(false),
        addEventListener: (event, fn) => deviceEvents[event] = fn
    };
    const context = vm.createContext({
        $, document, console: { log() {}, warn() {}, error() {} }, Option: function (text, value) { this.text = text; this.value = value; },
        navigator: { mediaDevices, userAgent: 'Chrome test' }, screen: {},
        window: { innerWidth: 390, innerHeight: 844, MediaRecorder: Recorder, addEventListener: (event, fn) => windowEvents[event] = fn },
        MediaRecorder: Recorder, WebRTC2RTMPURL: 'https://example.invalid', rtmpURLEncrypted: 'test-key',
        io: () => socket, __: text => text, avideoToastError() {}, avideoToastWarning() {}, avideoToastSuccess() {},
        localStorage: { getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value), removeItem: key => storage.delete(key) },
        setTimeout: (fn, delay) => { timers.set(++timerId, { fn, delay }); return timerId; }, clearTimeout: id => timers.delete(id)
    });
    for (const file of ['api.js', 'events.js']) vm.runInContext(fs.readFileSync(path.join(__dirname, '../../plugin/WebRTC', file), 'utf8'), context);
    const run = source => vm.runInContext(source, context);
    ready.forEach(fn => fn());
    const fire = (event, value = {}) => events[event](value);
    fire('connect');
    fire('rtmp-status', { isRunning: false });
    return {
        run, node, sent, socket, fire, streams, recorders, windowEvents, deviceEvents, storage, managerEvents,
        mediaCalls: () => mediaCalls, constraints: () => lastConstraints,
        failMedia: name => mediaError = name,
        deferMedia: () => { let resolve; pendingMedia = new Promise(r => resolve = r); return () => resolve(stream()); },
        timeout: delay => { const entry = [...timers].find(([, value]) => value.delay === delay); assert.ok(entry, `Missing ${delay}ms timer`); timers.delete(entry[0]); entry[1].fn(); }
    };
}

(async () => {
    let f = fixture();
    assert.equal(f.mediaCalls(), 0, 'Loading Live must not activate the hidden Webcam tab');
    assert.equal(f.sent.filter(e => e.event === 'join').length, 0);
    await f.run('prepareWebcam()');
    assert.equal(f.mediaCalls(), 1, 'One capture request prepares the preview and unlocks labels');
    assert.equal(f.node('webrtcPreviewBadge').text, 'Not live');
    assert.equal(f.recorders.length, 0, 'Preview never forwards audio/video');
    assert.equal(f.node('startLive').props.disabled, false);
    f.run("startWebcamLive('test-key'); startWebcamLive('test-key')");
    assert.equal(f.sent.filter(e => e.event === 'join').length, 1, 'Double click publishes only once');
    f.fire('rtmp-status', { isRunning: false });
    assert.equal(f.run('isPublishing'), true, 'A stale offline status must not cancel a pending start');
    f.fire('live-start');
    assert.equal(f.run('isLive'), false, 'Wait for server confirmation before displaying Live');
    f.fire('rtmp-status', { isRunning: true });
    assert.equal(f.node('webrtcStateLabel').text, 'You are live');
    assert.equal(await f.run('startWebRTC()'), false, 'Cannot change devices while broadcasting');
    f.run("stopWebcamLive('test-key')");
    assert.equal(f.recorders[0].state, 'inactive');
    assert.equal(f.sent.filter(e => e.event === 'video-chunk').length, 0, 'Recorder final chunk is discarded after Stop');
    assert.equal(f.node('webrtcStateLabel').text, 'Ending broadcast');
    f.fire('live-stopped');
    assert.equal(f.run('isLive'), false);
    assert.equal(f.streams[0].getTracks()[0].readyState, 'live', 'End returns to preview');
    f.run('stopWebRTC()');
    assert.ok(f.streams[0].getTracks().every(t => t.readyState === 'ended'));
    assert.equal(f.windowEvents.orientationchange, undefined, 'Rotation must not turn a stopped camera back on');

    for (const error of ['NotAllowedError', 'NotFoundError', 'NotReadableError', 'OverconstrainedError', 'AbortError']) {
        f = fixture(); f.failMedia(error);
        assert.equal(await f.run('startWebRTC()'), false);
        assert.ok(f.node('webrtcStateMessage').text.length > 20);
        assert.equal(f.node('retryWebRTC').classes.has('hidden'), false);
        assert.equal(f.sent.filter(e => e.event === 'join').length, 0);
        f.failMedia(null); await f.run('retryWebRTC()');
        assert.equal(f.run('!!localStream'), true, `${error}: retry recovers the preview`);
    }
    f = fixture(); await f.run('prepareWebcam()');
    f.run("startWebcamLive('test-key')"); f.timeout(15000);
    assert.ok(f.sent.some(e => e.event === 'stop-live'), 'Timed-out publish is cancelled server-side');
    f.fire('live-start');
    assert.equal(f.run('webrtcStopPending'), true, 'A late start does not override cancellation');
    f.timeout(10000);
    assert.equal(f.node('stopLive').props.disabled, false, 'Unconfirmed Stop can be retried');
    f.run("stopWebcamLive('test-key')"); f.fire('rtmp-status', { isRunning: false });
    f.run("startWebcamLive('test-key')");
    assert.equal(f.run('isPublishing'), true, 'Can publish again after confirmed stop');

    f = fixture(); await f.run('prepareWebcam()'); f.run("startWebcamLive('test-key')");
    f.fire('rtmp-status', { isRunning: true });
    f.socket.connected = false; f.fire('disconnect', 'transport close');
    assert.equal(f.node('webrtcStateLabel').text, 'Reconnecting');
    assert.equal(f.recorders[0].state, 'inactive');
    f.recorders[0].ondataavailable({ data: { size: 1 } });
    assert.equal(f.sent.filter(e => e.event === 'video-chunk').length, 0, 'Offline chunks are never buffered');
    f.socket.connected = true; f.fire('connect'); f.fire('rtmp-status', { isRunning: false });
    assert.equal(f.run('isLive'), false);
    assert.equal(f.sent.filter(e => e.event === 'join').length, 1, 'Reconnection never silently republishes');
    assert.ok(f.managerEvents.reconnect_attempt, 'Use Socket.IO Manager reconnection events');

    f = fixture(); await f.run('prepareWebcam()'); f.run("startWebcamLive('test-key')");
    const audio = f.streams[0].getAudioTracks()[0]; audio.readyState = 'ended'; audio.ended();
    assert.equal(f.run('localStream'), null, 'Unplugged mic releases capture and stops publishing');
    assert.ok(f.sent.some(e => e.event === 'stop-live'));
    f = fixture(); const resolve = f.deferMedia(); const pending = f.run('startWebRTC()');
    f.windowEvents.pagehide(); resolve(); await pending;
    assert.equal(f.run('localStream'), null, 'Late permission results cannot reopen a closed studio');
    assert.ok(f.streams[0].getTracks().every(t => t.readyState === 'ended'));

    f = fixture(); await f.run("startWebRTC({videoDeviceId:'facing-environment'})");
    assert.equal(f.constraints().video.facingMode.exact, 'environment');
    assert.equal(f.constraints().video.deviceId, undefined, 'Facing mode must not be used as a device ID');
    await f.run('startWebRTC({useScreen:true})');
    assert.match(f.node('webrtcStateMessage').text, /no sound/);
    f.run("startWebcamLive('test-key')");
    assert.equal(f.run('isPublishing'), true, 'Existing screen-only broadcasts remain supported');
    f.windowEvents.pagehide();
    assert.equal(f.recorders[0].state, 'inactive');
    f = fixture(); await f.run('prepareWebcam()');
    f.run('window.MediaRecorder = null');
    f.run("startWebcamLive('test-key')");
    assert.equal(f.run('isPublishing'), false, 'Unsupported recorder fails immediately');
    assert.ok(f.sent.some(e => e.event === 'stop-live'), 'Failed recorder cancels the joined session');
    f.fire('live-stopped');
    assert.match(f.node('webrtcStateMessage').text, /Update your browser/, 'Keep recovery guidance after stop acknowledgement');
    f = fixture();
    f.run('navigator.mediaDevices = undefined');
    assert.equal(await f.run('startWebRTC()'), false);
    assert.match(f.node('webrtcStateMessage').text, /HTTPS/);
    f = fixture();
    f.storage.set('webrtcSelectedDevices', JSON.stringify({ videoDeviceId: 'camera', audioDeviceId: 'mic' }));
    await f.run('prepareWebcam()');
    assert.equal(f.constraints().video.deviceId.exact, 'camera');
    assert.equal(f.constraints().audio.deviceId.exact, 'mic');
    f = fixture();
    f.storage.set('webrtcSelectedDevices', JSON.stringify({ videoDeviceId: 'removed-camera', audioDeviceId: 'mic' }));
    await f.run('prepareWebcam()');
    assert.equal(f.constraints().video.deviceId, undefined, 'Missing saved devices fall back to defaults');
    assert.equal(f.storage.has('webrtcSelectedDevices'), false);
    f = fixture();
    f.socket.connected = false; f.fire('connect_error', { message: 'test network failure' });
    assert.equal(f.node('retryWebRTC').classes.has('hidden'), false);
    await f.run('retryWebRTC()');
    f.fire('connect'); f.fire('rtmp-status', { isRunning: false });
    assert.equal(f.node('startLive').props.disabled, false);
    f = fixture({ studio: false });
    assert.equal(f.mediaCalls(), 0, 'Server status page must not request camera permissions');
    console.log('PASS: WebRTC preview, start/stop, retry, permissions, device loss, reconnection, timeout, cleanup and screen sharing.');
})().catch(error => { console.error(error); process.exitCode = 1; });
