/* Run with node tests/Integration/companionSubtitle.js (PHP must be on PATH).
 * Companion transcript -> SubtitleSwitcher track: file writing rules, the
 * Video chat tab action, and the tab's automatic/manual copy UI. */
const fs = require('fs');
const os = require('os');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const {spawnSync} = require('child_process');
const root = path.resolve(__dirname, '../..');
const client = path.join(root, 'plugin/AI/companion.php').replace(/\\/g, '/');
const endpoint = path.join(root, 'plugin/AI/tabs/companionChat.json.php').replace(/\\/g, '/');

function runPhp(fixture) {
    const result = spawnSync('php', [], {input: fixture, encoding: 'utf8'});
    assert.strictEqual(result.status, 0, result.stderr || result.stdout || String(result.error));
    return JSON.parse(result.stdout);
}

// --- storeSubtitle(): what lands in videos/{filename}/ -----------------------
const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'companion-subtitle-')).replace(/\\/g, '/');
const stored = runPhp(`<?php
    $dir = '${dir}';
    $global = ['bcp47' => ['pt_BR' => ['label' => 'Portuguese (Brazil)'], 'pt' => ['label' => 'Portuguese']]];
    $GLOBALS['externalOptions'] = '{}';
    $GLOBALS['cleared'] = [];
    function __($text) { return $text; }
    function _error_log($message, $type = 0) {}
    function _json_decode($json) { return json_decode($json); }
    function deleteVTTCache($filename) { $GLOBALS['cleared'][] = "vtt:$filename"; }
    class AVideoLog { static $ERROR = 1; static $WARNING = 2; static $SECURITY = 3; }
    class ObjectYPT { static function deleteCache($name) { $GLOBALS['cleared'][] = $name; } }
    class AVideoPlugin { static function isEnabledByName($name) { return true; } }
    class TestConfig { function getLanguage() { return 'pt_BR'; } }
    $config = new TestConfig();
    class Video {
        function __construct($title = '', $filename = '', $id = 0, $refreshCache = false) {}
        function getExternalOptions() { return $GLOBALS['externalOptions']; }
        function setExternalOptions($value) { $GLOBALS['externalOptions'] = $value; }
        function save($updateVideoGroups = false, $allowOfflineUser = false) {
            if (!$allowOfflineUser) { throw new Exception('Cron has no session: save must allow offline user'); }
            return 1;
        }
        static function getPathToFile($name, $createDir = false) { return $GLOBALS['dir'] . '/' . $name; }
        static function clearCache($id) { $GLOBALS['cleared'][] = "video:$id"; }
    }
    require '${client}';
    $store = new ReflectionMethod('CompanionAI', 'storeSubtitle');
    $store->setAccessible(true);
    $vtt = function ($text) { return "WEBVTT\\n\\n1\\n00:00:00.000 --> 00:00:02.000\\n$text\\n"; };
    $sub = function ($text, $version, $language = 'pt') use ($vtt) {
        return (object) ['vtt' => $vtt($text), 'transcript_version_id' => $version, 'language' => $language, 'segment_count' => 1];
    };
    $f = 'video_1';
    $out = [];
    $out['first'] = $store->invoke(null, 7, $f, $sub('Olá', 'v1'), false, true);
    $out['firstFile'] = file_get_contents("$dir/$f.pt.vtt");
    $out['cleared'] = $GLOBALS['cleared'];
    $out['state'] = json_decode($GLOBALS['externalOptions'])->companionSubtitle;
    $out['same'] = $store->invoke(null, 7, $f, $sub('Olá', 'v1'), false, true);
    file_put_contents("$dir/$f.pt.srt", 'stale');
    $out['reprocessed'] = $store->invoke(null, 7, $f, $sub('Olá de novo', 'v2'), false, true);
    $out['srtRemoved'] = !file_exists("$dir/$f.pt.srt");

    // Someone edits the Companion file by hand: it is no longer ours.
    file_put_contents("$dir/$f.pt.vtt", $vtt('corrigido a mao'));
    $out['editedAuto'] = $store->invoke(null, 7, $f, $sub('Olá', 'v3'), false, true);
    $out['editedKept'] = strpos(file_get_contents("$dir/$f.pt.vtt"), 'corrigido a mao') !== false;
    $out['skipState'] = json_decode($GLOBALS['externalOptions'])->companionSubtitle;
    $out['editedManual'] = $store->invoke(null, 7, $f, $sub('Olá', 'v3'), false, false);
    $out['overwrite'] = $store->invoke(null, 7, $f, $sub('Olá', 'v3'), true, false);
    $out['overwritten'] = strpos(file_get_contents("$dir/$f.pt.vtt"), 'Olá') !== false;

    // A reprocessing that detects another language replaces our old track.
    $out['newLanguage'] = $store->invoke(null, 7, $f, $sub('Hello', 'v4', 'en'), false, true);
    $out['oldTrackRemoved'] = !file_exists("$dir/$f.pt.vtt") && file_exists("$dir/$f.en.vtt");

    // Transcribed before Companion kept the language: the site's own.
    $GLOBALS['externalOptions'] = '{}';
    $out['noLanguage'] = $store->invoke(null, 8, 'video_2', $sub('Oi', 'v1', null), false, true);
    $out['siteLanguageFile'] = file_exists("$dir/video_2.pt_BR.vtt");

    // Tampered externalOptions (anyone who can edit the video writes it).
    $GLOBALS['externalOptions'] = json_encode(['companionSubtitle' => ['lang' => ['x'], 'sha256' => ['y'], 'transcriptVersionId' => 5]]);
    $out['tampered'] = $store->invoke(null, 8, 'video_2', $sub('Oi', 'v1', null), false, true);

    $GLOBALS['externalOptions'] = '{}';
    $out['bad'] = [
        $store->invoke(null, 9, 'video_3', (object) ['vtt' => "<script>alert(1)</script>\\n-->", 'transcript_version_id' => 'v1', 'language' => 'pt', 'segment_count' => 1], true, false)->code,
        $store->invoke(null, 9, 'video_3', (object) ['vtt' => "WEBVTT\\n\\n1\\n00:00:00.000 --> 00:00:01.000\\n\\xff\\xfe", 'transcript_version_id' => 'v1', 'language' => 'pt', 'segment_count' => 1], true, false)->code,
        $store->invoke(null, 9, 'video_3', (object) ['vtt' => 'WEBVTT' . str_repeat(' --> x', 1000000), 'transcript_version_id' => 'v1', 'language' => 'pt', 'segment_count' => 1], true, false)->code,
        $store->invoke(null, 9, 'video_3', (object) ['vtt' => $vtt('x'), 'transcript_version_id' => 'v1', 'language' => '../../x', 'segment_count' => 1], true, false)->lang,
    ];
    $out['badFiles'] = array_values(array_filter(scandir($dir), function ($name) { return strpos($name, 'video_3') === 0; }));
    $out['noSpeech'] = $store->invoke(null, 10, 'video_4', (object) ['vtt' => "WEBVTT\\n\\n", 'transcript_version_id' => 'v1', 'language' => 'pt', 'segment_count' => 0], false, true);
    $out['noSpeechState'] = json_decode($GLOBALS['externalOptions'])->companionSubtitle;
    $out['leftovers'] = array_values(array_filter(scandir($dir), function ($name) { return substr($name, -4) === '.tmp'; }));
    echo json_encode($out);
`);
assert.strictEqual(stored.first.code, 'imported');
assert.strictEqual(stored.first.lang, 'pt');
assert.match(stored.firstFile, /^WEBVTT\n\n1\n00:00:00.000 --> 00:00:02.000\nOlá\n$/);
assert.deepStrictEqual(stored.cleared, ['vtt:video_1', 'SubtitleSwitcher_getVideoTags7', 'video:7'], 'Every subtitle cache must be cleared');
assert.strictEqual(stored.state.transcriptVersionId, 'v1');
assert.strictEqual(stored.state.lang, 'pt');
assert.match(stored.state.sha256, /^[0-9a-f]{64}$/);
assert.strictEqual(stored.same.code, 'up_to_date', 'The same transcript is not written twice');
assert.strictEqual(stored.reprocessed.code, 'imported', 'A newer transcript replaces the Companion track');
assert.strictEqual(stored.srtRemoved, true, 'The stale .srt copy must go');
assert.strictEqual(stored.editedAuto.code, 'exists', 'An edited file is not Companion\'s any more');
assert.strictEqual(stored.editedKept, true, 'Automatic copies never replace an edited subtitle');
assert.strictEqual(stored.skipState.skipped, 'exists');
assert.strictEqual(stored.editedManual.code, 'exists', 'The manual button asks before replacing');
assert.strictEqual(stored.overwrite.code, 'imported');
assert.strictEqual(stored.overwritten, true);
assert.strictEqual(stored.newLanguage.lang, 'en');
assert.strictEqual(stored.oldTrackRemoved, true, 'The previous Companion language track is removed');
assert.strictEqual(stored.noLanguage.lang, 'pt_BR');
assert.strictEqual(stored.siteLanguageFile, true);
assert.strictEqual(stored.tampered.code, 'exists', 'Tampered state must not make a file look like Companion\'s');
assert.deepStrictEqual(stored.bad.slice(0, 3), ['invalid', 'invalid', 'invalid']);
assert.strictEqual(stored.bad[3], 'pt_BR', 'A language that is not a plain code never reaches the file name');
assert.deepStrictEqual(stored.badFiles, ['video_3.pt_BR.vtt'], 'Refused responses write nothing');
assert.strictEqual(stored.noSpeech.code, 'no_speech');
assert.strictEqual(stored.noSpeech.error, false);
assert.strictEqual(stored.noSpeechState.skipped, 'no_speech');
assert.deepStrictEqual(stored.leftovers, [], 'No temporary file is left behind');
fs.rmSync(dir, {recursive: true, force: true});
console.log('PASS: subtitle files are validated, written atomically, never replace foreign subtitles silently, and clear every cache.');

// --- companionChat.json.php copy_subtitle ------------------------------------
for (const [method, post, expected] of [
    ['POST', {auto: '1', overwrite: '1'}, [false, true]],
    ['POST', {overwrite: '1'}, [true, false]],
    ['POST', {}, [false, false]],
    ['GET', {}, 'POST required'],
]) {
    const response = runPhp(`<?php
        $global = ['bcp47' => ['en_US' => ['label' => 'English (United States)']]];
        function getVideos_id() { return 627; }
        function __($text) { return $text; }
        function _json_encode($value) { return json_encode($value); }
        function forbiddenPage($text) { echo json_encode(['error' => true, 'msg' => $text]); exit; }
        function forbidIfNotPost() { if ($_SERVER['REQUEST_METHOD'] !== 'POST') forbiddenPage('POST required'); }
        function forbidIfInvalidToken() { if (($_POST['globalToken'] ?? '') !== 'fresh-token') forbiddenPage('Invalid token'); }
        function durationToSeconds($duration) { return 0; }
        class AVideoPlugin { static function isEnabledByName($name) { return true; } }
        class AI { static function canUseAI() { return true; } }
        class User { static function getId() { return 1; } }
        class Video {
            function __construct($title = '', $filename = '', $videos_id = 0) {}
            static function canEdit($id) { return true; }
            function getDuration_in_seconds() { return 2859; }
            function getDuration() { return '00:47:39'; }
        }
        class CompanionAI {
            static function isConfigured() { return true; }
            static function status() { return (object) ['organization_status' => 'active']; }
            static function getVideoStatus($id, &$httpCode = null) { $httpCode = 200; return (object) ['status' => 'ready']; }
            static function getStoredChatConfig($id) { return null; }
            static function isSubtitleSwitcherEnabled() { return true; }
            static function getSubtitleState($id) { return (object) ['lang' => 'en_US', 'importedAt' => 1]; }
            static function importSubtitle($id, $overwrite = false, $automatic = false) {
                return (object) ['error' => false, 'code' => 'imported', 'args' => [$overwrite, $automatic]];
            }
        }
        $_SERVER['REQUEST_METHOD'] = '${method}';
        $_POST = json_decode('${JSON.stringify(Object.assign({globalToken: 'fresh-token'}, post))}', true);
        $_REQUEST = $_POST + ['action' => 'copy_subtitle'];
        eval('?>' . str_replace("require_once '../../../videos/configuration.php';", '', file_get_contents('${endpoint}')));
    `);
    if (typeof expected === 'string') {
        assert.strictEqual(response.msg, expected, 'State changes only through POST');
        continue;
    }
    assert.deepStrictEqual(response.subtitleResult.args, expected, 'auto=1 can never overwrite');
    assert.strictEqual(response.subtitleLanguageLabel, 'English (United States)');
    assert.strictEqual(response.subtitleSwitcherEnabled, true);
}
console.log('PASS: copy_subtitle requires POST, and the automatic attempt can never overwrite.');

// --- Video chat tab: status text, one silent attempt, confirm before replace --
const view = fs.readFileSync(path.join(root, 'plugin/AI/tabs/companionChat.php'), 'utf8');
const js = view.split('<script>')[1].split('</script>')[0]
    .replace(/<\?php echo \(int\) \$videos_id; \?>/g, '627')
    .replace(/<\?php echo json_encode\(__\((.*?)\)\); \?>/g, (_, literal) => literal)
    .replace(/<\?php echo __\('([^']*)'\); \?>/g, (_, text) => text);
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
const requests = [];
$.ajax = options => { requests.push(options); };
let confirmAnswer = true;
const events = [];
const context = {
    $, document: {}, webSiteRootURL: '/', Object, Promise,
    avideoToastError() {}, avideoToastSuccess(message) { events.push(['toast', message]); },
    avideoAlertError(message) { events.push(['alert', message]); },
    avideoConfirm(message) { events.push(['confirm', message]); return Promise.resolve(confirmAnswer); },
    modal: {showPleaseWait() { events.push(['wait']); }, hidePleaseWait() { events.push(['done']); }}
};
vm.createContext(context);
vm.runInContext(js, context);
function answer(response) {
    const request = requests.shift();
    if (request.data.action === 'token') {
        request.success({globalToken: 'fresh-token'});
        request.complete && request.complete();
        return answer(response);
    }
    request.success(response);
    request.complete();
    return request.data;
}

(async () => {
    context.companionRenderSubtitle({subtitleSwitcherEnabled: false, subtitle: null});
    assert.match($('#companionSubtitleStatus').value, /SubtitleSwitcher/);
    assert.strictEqual($('#companionCopySubtitleBtn').prop('disabled'), true);
    assert.strictEqual(requests.length, 0, 'No automatic copy without the plugin');

    context.companionRenderSubtitle({subtitleSwitcherEnabled: true, subtitle: null});
    assert.strictEqual(requests.length, 1, 'A ready video with no subtitle gets one silent attempt');
    const auto = answer({subtitleResult: {error: true, code: 'exists'}});
    assert.strictEqual(auto.action, 'copy_subtitle');
    assert.strictEqual(auto.auto, 1);
    assert.strictEqual(auto.overwrite, undefined);
    assert.deepStrictEqual(events.filter(e => e[0] !== 'done'), [], 'The silent attempt shows no dialog');
    context.companionRenderSubtitle({subtitleSwitcherEnabled: true, subtitle: null});
    assert.strictEqual(requests.length, 0, 'Only one silent attempt per page view');

    context.companionRenderSubtitle({subtitleSwitcherEnabled: true, subtitle: {skipped: 'exists', lang: 'en_US'}});
    assert.match($('#companionSubtitleStatus').value, /already existed/);
    context.companionRenderSubtitle({subtitleSwitcherEnabled: true, subtitle: {importedAt: 1, lang: 'en_US'}, subtitleLanguageLabel: 'English (United States)'});
    assert.strictEqual($('#companionSubtitleStatus').value, 'Subtitle on the player: English (United States)');

    // Manual copy over a foreign subtitle: ask first, then overwrite.
    events.length = 0;
    context.companionCopySubtitle(false, false);
    const manual = answer({subtitleResult: {error: true, code: 'exists', msg: 'Replace it?'}});
    assert.strictEqual(manual.overwrite, 0);
    await new Promise(resolve => setImmediate(resolve));
    assert.deepStrictEqual(events.find(e => e[0] === 'confirm'), ['confirm', 'Replace it?']);
    const replaced = answer({subtitleResult: {error: false, code: 'imported', msg: 'Copied.'}});
    assert.strictEqual(replaced.overwrite, 1, 'Confirming resends with overwrite');
    assert.deepStrictEqual(events.find(e => e[0] === 'toast'), ['toast', 'Copied.']);

    // Declining leaves the file alone.
    confirmAnswer = false;
    context.companionCopySubtitle(false, false);
    answer({subtitleResult: {error: true, code: 'exists', msg: 'Replace it?'}});
    await new Promise(resolve => setImmediate(resolve));
    assert.strictEqual(requests.length, 0, 'Cancel sends nothing');

    // A click while another action runs must not leave "please wait" open.
    events.length = 0;
    context.companionActionBusy = true;
    context.companionCopySubtitle(false, false);
    assert.deepStrictEqual(events, [], 'Busy: no overlay, no request');
    assert.strictEqual(requests.length, 0);
    context.companionActionBusy = false;
    console.log('PASS: the tab shows the subtitle state, tries once silently, and confirms before replacing.');
})().catch(error => { console.error(error); process.exit(1); });
