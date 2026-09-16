/* Run with node tests/Integration/playerStartup.js; no server or media download required. */
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const script = fs.readFileSync(path.join(__dirname, '../../view/js/script.js'), 'utf8');
const addView = fs.readFileSync(path.join(__dirname, '../../view/js/addView.js'), 'utf8');

function between(source, start, end) {
    return source.slice(source.indexOf(start), source.indexOf(end, source.indexOf(start)));
}

async function testCookieResume(query, expectedTime) {
    const reports = [];
    const cookies = {
        addView_PHPSESSID: 'test-session', addView_videos_id: '4522',
        addView_playerCurrentTime: '5000', addView_seconds_watching_video: '12'
    };
    const context = {
        webSiteRootURL: '/', mediaId: '4522', urlParams: new URLSearchParams(query),
        forceCurrentTime: null, _addViewFromCookie_addingtime: false,
        isVideoAddViewCount: false, Cookies: {get: key => cookies[key]},
        _addView: (...args) => reports.push(args), setTimeout() {},
        addViewSetCookie: (...args) => { context.cleared = args; }
    };
    vm.createContext(context);
    vm.runInContext(between(addView, 'async function addViewFromCookie()', 'async function addViewSetCookie('), context);
    await context.addViewFromCookie();
    assert.strictEqual(context.forceCurrentTime, expectedTime);
    assert.deepStrictEqual(reports, [['4522', '5000', '12']], 'Keep reporting the previous watch time');
    assert.deepStrictEqual(context.cleared, [false, false, false, false]);
}

async function testPlayback(blockSound) {
    let muted = false;
    let paused = true;
    const seeks = [];
    const muteChanges = [];
    const timers = [];
    let plays = 0;
    const context = {
        console: {debug() {}, log() {}},
        isTryingToPlay: false, userIsControling: false, promisePlaytry: 20,
        promisePlaytryNetworkFail: 0, playerNotReadyTry: 0,
        cancelAllPlaybackTimeouts() {}, playerIsPlayingAds: () => false,
        playerIsReadyToPlay: () => true, clearUserGestureToPlayHandlers() {},
        setCurrentTime: time => seeks.push(time), tryToPlay() {}, setPlayerListners() {},
        setTimeout: callback => timers.push(callback), inIframe: () => false,
        showUnmutePopup: () => { context.popup = true; },
        player: {
            muted(value) {
                if (value !== undefined) { muted = value; muteChanges.push(value); }
                return muted;
            },
            play() {
                plays++;
                if (blockSound && !muted) {
                    const error = new Error('Autoplay requires muted playback');
                    error.name = 'NotAllowedError';
                    return Promise.reject(error);
                }
                paused = false;
                return Promise.resolve();
            },
            paused: () => paused, networkState: () => 1, isAudio: () => false
        }
    };
    vm.createContext(context);
    vm.runInContext(between(script, 'function playerPlay(currentTime)', 'function showUnmutePopup()'), context);
    context.playerPlay(0);
    await new Promise(resolve => setImmediate(resolve));
    timers.forEach(callback => callback());
    assert.deepStrictEqual(seeks, [0], 'Explicit t=0 must seek too');
    assert.strictEqual(paused, false);
    assert.strictEqual(plays, blockSound ? 2 : 1);
    assert.deepStrictEqual(muteChanges, blockSound ? [true] : [], 'Do not unmute the fallback without a gesture');
    assert.strictEqual(Boolean(context.popup), blockSound);
}

function testStartupTime(functionName, endMarker) {
    for (const time of [0, 4121, undefined]) {
        const seeks = [];
        const context = {
            console: {log() {}}, forceCurrentTime: null,
            setCurrentTime: value => seeks.push(value), isAutoplayEnabled: () => false
        };
        vm.createContext(context);
        vm.runInContext(between(script, 'function ' + functionName + '(currentTime)', endMarker), context);
        context[functionName](time);
        assert.deepStrictEqual(seeks, time === undefined ? [] : [time], 'Seek even when autoplay is off; leave live time unset');
    }
}

(async () => {
    await testCookieResume('?t=4121&autoplay=1', null);
    await testCookieResume('?t=0', null);
    await testCookieResume('', '5000');
    await testPlayback(true);
    await testPlayback(false);
    testStartupTime('playerPlayIfAutoPlay', 'function cancelAllPlaybackTimeouts()');
    testStartupTime('playerPlayMutedIfAutoPlay', 'function playNext(');
    console.log('PASS: URL time overrides cookie resume, t=0, watch-time reporting, and muted autoplay fallback.');
})().catch(error => { console.error(error); process.exitCode = 1; });
