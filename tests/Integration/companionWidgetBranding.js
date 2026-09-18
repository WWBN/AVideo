/* Run with node tests/Integration/companionWidgetBranding.js (PHP on PATH). */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {spawnSync} = require('node:child_process');
const root = path.resolve(__dirname, '../..').replace(/\\/g, '/');
const aiSource = fs.readFileSync(`${root}/plugin/AI/AI.php`, 'utf8');
const footer = aiSource.match(/ {4}static function getCompanionChatWidgetHTML[\s\S]*?\n {4}\}/)[0];

function render({api = 'https://companion.example/', ui = api, config = '', embed = `${ui}widget/.signed-key`, local = false, language = 'en'} = {}) {
    const data = Buffer.from(JSON.stringify({api, ui, config, embed, local, language})).toString('base64');
    const fixture = `<?php
        $data = json_decode(base64_decode('${data}'), true);
        $global = ['systemRootPath' => '${root}/', 'companionBaseUrl' => $data['api'], 'companionFrontendBaseUrl' => $data['ui']];
        $_SERVER['SCRIPT_NAME'] = '/view/modeYoutube.php';
        function isVideo() { return true; }
        function getVideos_id() { return 4522; }
        function getURL($path) { return '/' . $path; }
        function _json_decode($value) { return json_decode($value); }
        function __($text, $allowHTML = false) {
            global $t;
            $text = $t[$text] ?? $text;
            return $allowHTML ? $text : str_replace(["'", '"', '<', '>'], ['&apos;', '&quot;', '&lt;', '&gt;'], $text);
        }
        if ($data['language'] === 'pt_BR') require '${root}/locale/pt_BR.php';
        class User { static function isAdmin() { return true; } }
        class AVideoPlugin {
            static function isEnabledByName($name) { return true; }
            static function getObjectDataIfEnabled($name) { return (object) []; }
        }
        class Video {
            function __construct(...$args) {}
            function getExternalOptions() {
                global $data;
                return json_encode(['companionChat' => ['enabled' => true, 'embedUrl' => $data['embed'], 'configUrl' => $data['config']]]);
            }
        }
        class AI {
            static function isTestEnvironment() { return $GLOBALS['data']['local']; }
${footer}
        }
        require '${root}/plugin/AI/companion.php';
        echo AI::getCompanionChatWidgetHTML();
    `;
    const result = spawnSync('php', [], {input: fixture, encoding: 'utf8'});
    assert.equal(result.status, 0, result.stderr || String(result.error));
    return result.stdout;
}

const legacy = render();
assert.ok(legacy.includes('data-config-url="https://companion.example/api/v1/public/widget/.signed-key"'));
assert.ok(legacy.includes('I&#039;m here to help'));
assert.ok(!legacy.includes('&amp;apos;'), 'Translate plain text before escaping it once for HTML');
assert.ok(render({language: 'pt_BR'}).includes('Alguma dúvida sobre este vídeo? Estou aqui para ajudar 🙂'));
assert.ok(render({config: 'https://companion.example/api/v1/public/widget/saved-key'})
    .includes('data-config-url="https://companion.example/api/v1/public/widget/saved-key"'));
assert.ok(render({api: 'http://host.docker.internal:8000/', ui: 'http://localhost:3100/', local: true})
    .includes('data-config-url="http://localhost:8000/api/v1/public/widget/.signed-key"'));
assert.ok(render({api: 'https://api.example/companion/', ui: 'https://ui.example/'})
    .includes('data-config-url="https://api.example/companion/api/v1/public/widget/.signed-key"'));
assert.ok(render({config: 'https://foreign.example/api/v1/public/widget/key'})
    .includes('data-config-url="https://companion.example/api/v1/public/widget/.signed-key"'));
for (const embed of ['https://foreign.example/widget/key', 'javascript:alert(1)', 'https://companion.example/widget/key?extra=1']) {
    assert.equal(render({embed}), '', 'Keep existing embed validation before resolving branding');
}
console.log('PASS: legacy/current launcher branding, public local API URL, translations and existing URL validation.');
