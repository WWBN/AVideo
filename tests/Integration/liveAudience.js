/* Run with node tests/Integration/liveAudience.js; tests the collector without a live broadcast. */
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const source = fs.readFileSync(require('path').join(__dirname, '../../plugin/Live/view/audience.php'), 'utf8');
// Replace server-rendered fixture values, keeping the real browser collector code.
const script = source.split('<script>')[1].split('</script>')[0].replace(/<\?php echo ([\s\S]*?); \?>/g,
    (_, expression) => expression.includes('getToken') ? '"fixture-token"' : expression.includes('json_encode') ? '"fixture-key"' : '1');
const requests = [], timers = [];
let paused = true;
function $(callback) { callback(); }
$.ajax = request => requests.push(request);
const context = {
    $, webSiteRootURL: 'https://example.invalid/',
    window: {setTimeout: (callback, delay) => timers.push({callback, delay})},
    player: {paused: () => paused, ended: () => false}
};
vm.runInNewContext(script, context);
assert.strictEqual(requests.length, 0, 'A paused player should not collect an audience sample');
paused = false;
timers.shift().callback();
assert.strictEqual(requests.length, 1, 'Playback should send a sample');
assert.strictEqual(requests[0].type, 'POST');
assert.strictEqual(requests[0].data.key, 'fixture-key');
assert.strictEqual(requests[0].data.live_servers_id, 1);
assert.strictEqual(requests[0].data.globalToken, 'fixture-token');
assert.strictEqual(timers.length, 0, 'Never overlap pending requests');
requests[0].complete();
assert.strictEqual(timers[0].delay, 15000, 'Both successful and failed requests schedule another sample');
timers.shift().callback();
assert.strictEqual(requests.length, 2, 'Collect again regardless of Socket.IO status');
vm.runInNewContext(script, context);
assert.strictEqual(requests.length, 2, 'Duplicate footer inclusion must not create a second collector');
requests[1].complete();
delete context.player;
timers.shift().callback();
assert.strictEqual(requests.length, 2, 'Wait safely for a player to become available');
assert.strictEqual(timers.length, 1, 'A page opened before a broadcast starts keeps waiting');
console.log('PASS: live audience playback, retry, timing and duplicate-collector checks.');
