/* Run with node tests/Integration/companionChat.js (PHP must be on PATH). */
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const {spawnSync} = require('child_process');
const root = path.resolve(__dirname, '../..');
const endpoint = path.join(root, 'plugin/AI/tabs/companionChat.json.php').replace(/\\/g, '/');
const client = path.join(root, 'plugin/AI/companion.php').replace(/\\/g, '/');

// Both Companion URLs are fixed; only the environment decides between them.
// The local one must be picked by the same development/production switch the
// other AI endpoints use - never because a constant happens to be defined,
// which is how a developer's endpoint reaches production. A malformed override
// must fail closed instead of silently timing out.
const aiSource = fs.readFileSync(path.join(root, 'plugin/AI/AI.php'), 'utf8');
function aiConstant(name) {
    const match = aiSource.match(new RegExp(`const ${name} = '([^']*)'`));
    assert.ok(match, `AI::${name} must exist`);
    return match[1];
}
const productionUrl = aiConstant('COMPANION_BASE_URL');
const localUrl = aiConstant('COMPANION_LOCAL_BASE_URL');
const testDomain = aiConstant('TEST_DOMAIN');
const isTestEnvironment = aiSource.match(/ {4}static function isTestEnvironment[\s\S]*?\n {4}\}/);
assert.ok(isTestEnvironment, 'AI::isTestEnvironment must exist');

// [SERVER_NAME, $global companionBaseUrl, $global companionFrontendBaseUrl,
//  expected API base, widget URL on the UI origin is accepted]
for (const [serverName, override, frontendOverride, expected, acceptsWidget] of [
    ['tube.example.com', null, null, productionUrl, true],
    [testDomain, null, null, localUrl, true],
    ['tube.example.com', 'https://staging.example/companion/', null, 'https://staging.example/companion/', true],
    ['tube.example.com', 'https://staging.example/companion', null, 'https://staging.example/companion/', true],
    ['tube.example.com', null, 'https://ui.example/', productionUrl, true],
    // The development UI origin stands on its own, so an already enabled chat
    // keeps rendering even while the API endpoint is misconfigured.
    [testDomain, 'not a url', null, '', true],
    // In production the UI falls back to the API origin, so a broken one
    // leaves nothing to validate against and the widget must not render.
    ['tube.example.com', 'javascript:alert(1)', null, '', false],
]) {
    const fixture = `<?php
        $_SERVER['SERVER_NAME'] = '${serverName}';
        $global = ['webSiteRootURL' => 'https://${serverName}/'${override === null ? '' : `, 'companionBaseUrl' => '${override}'`}${frontendOverride === null ? '' : `, 'companionFrontendBaseUrl' => '${frontendOverride}'`}];
        function _error_log($message, $type = 0) {}
        class AVideoLog { static $ERROR = 1; static $WARNING = 2; static $SECURITY = 3; }
        class AI {
            const COMPANION_BASE_URL = '${productionUrl}';
            const COMPANION_LOCAL_BASE_URL = '${localUrl}';
            const TEST_DOMAIN = '${testDomain}';
            static $isTest = 0;
${isTestEnvironment[0]}
        }
        class TestPlugin {
            function setDataObject($object) { $GLOBALS['saved'] = clone $object; return 1; }
        }
        class AVideoPlugin {
            static function isEnabledByName($name) { return true; }
            static function loadPlugin($name) { return new TestPlugin(); }
            static function getObjectDataIfEnabled($name) { return $GLOBALS['config']; }
        }
        $config = (object) ['AccessToken' => 'existing-test-token', 'CompanionApiToken' => ''];
        require '${client}';
        $method = new ReflectionMethod('CompanionAI', 'saveConnection');
        $method->setAccessible(true);
        $result = $method->invoke(null, (object) [
            'integration_api_key' => 'test-key', 'organization_id' => 'test-org', 'site_id' => 'test-site'
        ], 'test-fingerprint');
        $incomplete = $method->invoke(null, (object) ['integration_api_key' => 'k', 'organization_id' => 'o'], 'f');
        echo json_encode(['base' => CompanionAI::getBaseUrl(), 'result' => $result, 'incomplete' => $incomplete,
            'saved' => $saved, 'key' => CompanionAI::getIntegrationKey(),
            'frontend' => CompanionAI::getFrontendBaseUrl(),
            'frontendEmbed' => CompanionAI::isValidEmbedUrl(CompanionAI::getFrontendBaseUrl() . 'widget/abc'),
            'apiEmbed' => CompanionAI::isValidEmbedUrl(CompanionAI::getBaseUrl() . 'widget/abc'),
            'javascriptEmbed' => CompanionAI::isValidEmbedUrl('javascript:alert(document.domain)'),
            'foreignEmbed' => CompanionAI::isValidEmbedUrl('https://evil.example/widget')]);
    `;
    const result = spawnSync('php', [], {input: fixture, encoding: 'utf8'});
    assert.strictEqual(result.status, 0, result.stderr || String(result.error));
    const response = JSON.parse(result.stdout);
    assert.strictEqual(response.base, expected);
    assert.strictEqual(response.result, 1);
    assert.strictEqual(response.incomplete, false, 'An incomplete connect response must not be persisted');
    assert.strictEqual(response.saved.AccessToken, 'existing-test-token');
    assert.strictEqual(response.saved.CompanionApiToken, 'test-key');
    assert.strictEqual(response.saved.CompanionOrganizationId, 'test-org');
    assert.strictEqual(response.saved.CompanionSiteId, 'test-site');
    assert.strictEqual(response.saved.CompanionConnectionFingerprint, 'test-fingerprint');
    assert.strictEqual(response.key, 'test-key', 'New key is usable in the same request');
    // videos.externalOptions is writable by anyone who can edit the video, and
    // this URL becomes an <iframe src> on the public watch page - so the
    // validation has to accept exactly what the Companion UI mints, no more.
    assert.strictEqual(response.frontendEmbed, acceptsWidget, 'A widget URL on the UI origin must be accepted');
    assert.strictEqual(response.javascriptEmbed, false, 'A javascript: widget URL must never be accepted');
    assert.strictEqual(response.foreignEmbed, false, 'A widget URL outside Companion must never be accepted');
    if (frontendOverride !== null) {
        assert.strictEqual(response.frontend, frontendOverride, 'The configured UI origin wins');
        assert.strictEqual(response.apiEmbed, false, 'The API origin is not the UI origin once configured apart');
    } else if (serverName === testDomain) {
        // The browser talks to the UI container while PHP talks to the API
        // container, so the two origins differ in development - validating the
        // widget URL against the API origin rejects every legitimate URL.
        assert.notStrictEqual(response.frontend, response.base, 'Development UI and API origins differ');
        assert.strictEqual(response.apiEmbed, false);
    } else if (expected !== '') {
        assert.strictEqual(response.frontend, response.base, 'Production serves UI and API from one origin');
        assert.strictEqual(response.apiEmbed, true);
    }
}

for (const [action, connected, expectedError] of [['status', true, false], ['status', false, true], ['unknown', true, true]]) {
    const fixture = `<?php
        function getVideos_id() { return 627; }
        function __($text) { return $text; }
        function _json_encode($value) { return json_encode($value); }
        function forbiddenPage($text) { throw new Exception($text); }
        class AVideoPlugin { static function isEnabledByName($name) { return true; } }
        class AI { static function canUseAI() { return true; } }
        function durationToSeconds($duration) { return 0; }
        class Video {
            function __construct($title = '', $filename = '', $videos_id = 0) {}
            static function canEdit($id) { return true; }
            function getDuration_in_seconds() { return 2859; }
            function getDuration() { return '00:47:39'; }
        }
        class CompanionAI {
            static function isConfigured() { return true; }
            static function status() { return ${connected ? '(object) ["free_trial_available" => true]' : 'null'}; }
            static function getVideoStatus($id, &$httpCode = null) {
                $httpCode = 200;
                ${connected ? 'return (object) ["status" => "ready"];' : 'throw new Exception("Do not query video status after connection failure");'}
            }
            static function getStoredChatConfig($id) { return null; }
        }
        $_REQUEST['action'] = '${action}';
        $source = file_get_contents('${endpoint}');
        $source = str_replace("require_once '../../../videos/configuration.php';", '', $source);
        eval('?>' . $source);
    `;
    const result = spawnSync('php', [], {input: fixture, encoding: 'utf8'});
    assert.strictEqual(result.status, 0, result.stderr || String(result.error));
    const response = JSON.parse(result.stdout);
    assert.strictEqual(response.error, expectedError);
    if (action === 'status' && connected) assert.strictEqual(response.videoStatus.status, 'ready');
    if (!connected) {
        assert.match(response.msg, /Could not connect/);
        assert.strictEqual(response.videoStatus, undefined);
    }
}

const view = fs.readFileSync(path.join(root, 'plugin/AI/tabs/companionChat.php'), 'utf8');
const js = view.split('<script>')[1].split('</script>')[0]
    .replace(/<\?php echo \(int\) \$videos_id; \?>/g, '627')
    .replace(/<\?php echo json_encode\(__\((.*?)\)\); \?>/g, (_, literal) => literal);
const elements = {};
function $(selector) {
    if (!elements[selector]) elements[selector] = {
        visible: false, value: '', properties: {},
        hide() { this.visible = false; return this; },
        show() { this.visible = true; return this; },
        text(value) { this.value = value; return this; },
        prop(name, value) {
            if (value === undefined) return this.properties[name];
            this.properties[name] = value;
            return this;
        },
        ready() {}
    };
    return elements[selector];
}
let request;
$.ajax = options => { request = options; };
let overlayClosed = false;
const context = {
    $, document: {}, webSiteRootURL: '/', getGlobalToken: () => 'test-token',
    avideoToastError() {}, avideoAlertError(message) { alerted = message; },
    modal: {showPleaseWait() {}, hidePleaseWait() { overlayClosed = true; }}
};
let alerted = '';
vm.createContext(context);
vm.runInContext(js, context);
context.loadCompanionChat();
assert.strictEqual(request.data.action, 'status');
assert.strictEqual(request.dataType, 'json');
request.success({error: true, msg: 'Connection unavailable'});
assert.strictEqual($('#companionChatLoading').visible, false);
assert.strictEqual($('#companionChatContent').visible, false);
assert.strictEqual($('#companionChatNotConfigured').value, 'Connection unavailable');
request.error();
request.complete();
context.companionAjax('submit', {}, () => {});
assert.strictEqual(request.data.action, 'token', 'Mutations first refresh the expired page token');
const tokenRequest = request;
context.companionAjax('status', {}, () => {});
request.complete();
assert.strictEqual(context.companionActionBusy, true, 'Status polling cannot release a pending mutation');
request = tokenRequest;
context.companionAjax('submit', {}, () => {});
assert.strictEqual(request, tokenRequest, 'Duplicate clicks cannot submit twice');
request.success({globalToken: 'fresh-video-token'});
assert.strictEqual(request.data.action, 'submit');
assert.strictEqual(request.data.globalToken, 'fresh-video-token');
request.error({status: 403});
request.complete();
assert.strictEqual(overlayClosed, true, 'HTTP errors must release the busy overlay');
assert.match($('#companionChatNotConfigured').value, /session expired/);
assert.strictEqual($('#companionChatContent').visible, false);
context.companionAjax('enable_chat', {}, () => {});
assert.strictEqual(request.data.action, 'token');
request.error({status: 500});
assert.strictEqual(context.companionActionBusy, false, 'Token failure must allow retry');

// A refused enable leaves the chat disabled on the server, so the switch must
// not stay where the user put it.
$('#companionChatToggle').prop('checked', true);
context.companionToggleChat(true);
assert.strictEqual(request.data.action, 'token');
request.success({globalToken: 'toggle-token'});
assert.strictEqual(request.data.action, 'enable_chat');
request.success({error: true, msg: 'Could not enable chat for this video yet'});
assert.strictEqual($('#companionChatToggle').prop('checked'), false, 'A failed enable must switch back off');
assert.strictEqual(alerted, 'Could not enable chat for this video yet');
request.complete();

// The same in reverse: a refused disable must leave the switch on.
$('#companionChatToggle').prop('checked', false);
context.companionToggleChat(false);
request.success({globalToken: 'toggle-token'});
assert.strictEqual(request.data.action, 'disable_chat');
request.success({error: true, msg: 'Could not disable chat'});
assert.strictEqual($('#companionChatToggle').prop('checked'), true, 'A failed disable must switch back on');
request.complete();

console.log('PASS: Companion status dispatch, connection failure, unknown action, and frontend error cleanup.');

// A missing video (404) is different from a failed lookup (timeout/auth/5xx).
for (const code of [0, 401, 404, 500]) {
    const fixture = `<?php
        function getVideos_id() { return 627; }
        function __($text) { return $text; }
        function _json_encode($value) { return json_encode($value); }
        function forbiddenPage($text) { throw new Exception($text); }
        class AVideoPlugin { static function isEnabledByName($name) { return true; } }
        class AI { static function canUseAI() { return true; } }
        function durationToSeconds($duration) { return 0; }
        class Video {
            function __construct($title = '', $filename = '', $videos_id = 0) {}
            static function canEdit($id) { return true; }
            function getDuration_in_seconds() { return 2859; }
            function getDuration() { return '00:47:39'; }
        }
        class CompanionAI {
            static function isConfigured() { return true; }
            static function status() { return (object) ['organization_status' => 'active']; }
            static function getVideoStatus($id, &$httpCode = null) { $httpCode = ${code}; return null; }
            static function getStoredChatConfig($id) { return null; }
        }
        $_REQUEST['action'] = 'status';
        $source = str_replace("require_once '../../../videos/configuration.php';", '', file_get_contents('${endpoint}'));
        eval('?>' . $source);
    `;
    const result = spawnSync('php', [], {input: fixture, encoding: 'utf8'});
    assert.strictEqual(result.status, 0, result.stderr);
    const response = JSON.parse(result.stdout);
    assert.strictEqual(response.error, code !== 404);
    assert.strictEqual(!!response.videoStatusError, code !== 404);
}
console.log('PASS: fresh mutation tokens, duplicate clicks, expired sessions, token failure, and video lookup errors.');

for (const allowed of [true, false]) {
    const fixture = `<?php
        function getVideos_id() { return 627; }
        function _json_encode($value) { return json_encode($value); }
        function forbiddenPage($text) { echo json_encode(['error' => true]); exit; }
        function forbidIfNotPost() { if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('POST required'); }
        function getToken($ttl, $salt, $videoId) {
            if ($ttl !== 300 || $videoId !== 627) throw new Exception('Token must be short lived and video scoped');
            return 'fresh-token';
        }
        class AVideoPlugin { static function isEnabledByName($name) { return true; } }
        class AI { static function canUseAI() { return true; } }
        class Video { static function canEdit($id) { return ${allowed ? 'true' : 'false'}; } }
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST['action'] = 'token';
        $source = str_replace("require_once '../../../videos/configuration.php';", '', file_get_contents('${endpoint}'));
        eval('?>' . $source);
    `;
    const result = spawnSync('php', [], {input: fixture, encoding: 'utf8'});
    assert.strictEqual(result.status, 0, result.stderr);
    const response = JSON.parse(result.stdout);
    assert.strictEqual(response.error, !allowed);
    assert.strictEqual(response.globalToken, allowed ? 'fresh-token' : undefined);
}
console.log('PASS: token refresh requires video-edit permission and preserves the 300-second lifetime.');
