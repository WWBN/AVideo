<?php

namespace Tests\Security\AiShortsAuthorizationFixtures;

class State
{
    public static $userId;
    public static $aiAccess;
    public static $enabled;
    public static $reads;
}

class User
{
    private $id;
    public function __construct($id) { $this->id = $id; }
    public static function getId() { return State::$userId; }
    public static function isAdmin() { return State::$userId === 1; }
    public function getIsAdmin() { return $this->id === 1; }
}

class Permissions
{
    public static function hasPermission($permission, $plugin) { return State::$aiAccess; }
    public static function canAdminVideos() { return State::$userId === 4; }
}

class AVideoPlugin
{
    public static function getObjectDataIfEnabled($plugin)
    {
        return State::$enabled ? new \stdClass() : false;
    }
    public static function isEnabledByName($plugin) { return State::$enabled; }
}

class Ai_responses
{
    public static function getTranscriptionVtt($id)
    {
        State::$reads[] = ['transcription', $id];
        return 'WEBVTT';
    }
}

class Ai_responses_json
{
    public static function getAllFromAIType($type, $id)
    {
        State::$reads[] = ['shorts', $id];
        return [];
    }
}

class Forbidden extends \RuntimeException {}

function forbiddenPage($message, $log = false) { throw new Forbidden($message); }
function getVideos_id() { return intval($_REQUEST['videos_id'] ?? 0); }
function isCommandLineInterface() { return false; }

function extractMethod($file, $signature)
{
    $source = file_get_contents($file);
    $start = strpos($source, $signature);
    if ($start === false) {
        throw new \RuntimeException('Missing method: ' . $signature);
    }
    $depth = 0;
    for ($i = strpos($source, '{', $start); $i < strlen($source); $i++) {
        if ($source[$i] === '{') {
            $depth++;
        } elseif ($source[$i] === '}' && --$depth === 0) {
            return substr($source, $start, $i - $start + 1);
        }
    }
    throw new \RuntimeException('Unclosed method: ' . $signature);
}

// Endpoint code up to $endMarker, minus its include lines. Fails loudly instead of ever
// evaluating an include, so a changed include line can never load the real site configuration.
function endpointSource($relativePath, $endMarker, array $includeLines)
{
    $source = file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath);
    $end = strpos($source, $endMarker);
    if (strpos($source, '<?php') !== 0 || $end === false) {
        throw new \RuntimeException('Unexpected endpoint layout: ' . $relativePath);
    }
    $source = substr($source, 5, $end - 5);
    foreach ($includeLines as $line) {
        $source = str_replace($line, '', $source, $count);
        if ($count !== 1) {
            throw new \RuntimeException('Include line not found in ' . $relativePath . ': ' . $line);
        }
    }
    if (preg_match('/\b(require|include)(_once)?\b/i', $source)) {
        throw new \RuntimeException('Endpoint still includes a file: ' . $relativePath);
    }
    return $source;
}

// Exercise production permission methods with in-memory identities; never load site configuration.
eval('namespace ' . __NAMESPACE__ . '; class Video {
    public static function isOwner($id, $user) { return $id === 900 && $user === 2; }
    public static function getVideoLight($id) { State::$reads[] = ["video", $id]; return []; }
    ' . extractMethod(dirname(__DIR__, 2) . '/objects/video.php', 'public static function canEdit(') . '
}');
eval('namespace ' . __NAMESPACE__ . '; class AI {
    const PERMISSION_CAN_USE_AI_SUGGESTIONS = 0;
    public static $typeShorts = "shorts";
    public static function getVTTLanguageCodes($id) { State::$reads[] = ["langs", $id]; return []; }
    ' . extractMethod(dirname(__DIR__, 2) . '/plugin/AI/AI.php', 'static function canUseAI(') . '
}');

class AiShortsAuthorizationTest extends \PHPUnit\Framework\TestCase
{
    /** @dataProvider accessCases */
    public function testShortsAuthorizationBeforeReadingAiData($method, $userId, $aiAccess, $enabled, $videoId, $allowed)
    {
        // Run all initialization and data reads, stopping before the rendering helpers.
        $source = endpointSource('plugin/AI/tabs/shorts.ajax.php', 'function getTranscriptionJson(', [
            "require_once '../../../videos/configuration.php';",
        ]);
        $denied = $this->runEndpoint($source, $method, $userId, $aiAccess, $enabled, $videoId);
        $this->assertSame(!$allowed, $denied);
        $this->assertSame($allowed ? [['transcription', 900], ['video', 900], ['shorts', 900]] : [], State::$reads);
    }

    /** @dataProvider accessCases */
    public function testLanguagesAuthorizationBeforeReadingAiData($method, $userId, $aiAccess, $enabled, $videoId, $allowed)
    {
        // Run up to and including the read of the video's existing subtitle languages.
        $source = endpointSource('plugin/AI/tabs/langs.php', "echo '<!-- currentLangCodes='", [
            "require_once '../../../videos/configuration.php';",
            "require_once \$global['systemRootPath'] . 'objects/bcp47.php';",
        ]);
        $denied = $this->runEndpoint($source, $method, $userId, $aiAccess, $enabled, $videoId);
        $this->assertSame(!$allowed, $denied);
        $this->assertSame($allowed ? [['langs', 900]] : [], State::$reads);
    }

    private function runEndpoint($endpointSource, $method, $userId, $aiAccess, $enabled, $videoId)
    {
        State::$userId = $userId;
        State::$aiAccess = $aiAccess;
        State::$enabled = $enabled;
        State::$reads = [];
        $savedRequest = $_REQUEST;
        $savedMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_REQUEST = ['videos_id' => $videoId];
        $_SERVER['REQUEST_METHOD'] = $method;
        // The endpoint code runs in this scope; give it an in-memory $global, never the site one.
        $global = ['langs_codes' => []];
        $denied = false;
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; ' . $endpointSource);
        } catch (Forbidden $exception) {
            $denied = true;
        } finally {
            ob_end_clean();
            $_REQUEST = $savedRequest;
            if ($savedMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $savedMethod;
            }
        }
        return $denied;
    }

    public static function accessCases()
    {
        return [
            'GET other AI creator' => ['GET', 3, true, true, 900, false],
            'POST other AI creator' => ['POST', 3, true, true, 900, false],
            'GET owner' => ['GET', 2, true, true, 900, true],
            'POST owner' => ['POST', 2, true, true, 900, true],
            'site administrator' => ['POST', 1, false, true, 900, true],
            'delegated videos administrator with AI permission' => ['POST', 4, true, true, 900, true],
            'delegated videos administrator without AI permission' => ['POST', 4, false, true, 900, false],
            'owner without AI permission' => ['POST', 2, false, true, 900, false],
            'AI plugin disabled' => ['POST', 2, true, false, 900, false],
            'anonymous even with permissive AI group' => ['GET', 0, true, true, 900, false],
            'missing video ID' => ['POST', 1, true, true, 0, false],
            'invalid video ID' => ['GET', 2, true, true, 'invalid', false],
            'nonexistent video for ordinary creator' => ['POST', 3, true, true, 901, false],
        ];
    }
}
