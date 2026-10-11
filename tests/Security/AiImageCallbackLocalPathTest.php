<?php

namespace Tests\Security\AiImageCallbackFixtures;

class AI
{
    public static $typeImage = 'image';
}

class Ai_responses_json
{
    public function __construct($id = 0) {}
    public function setResponse($response) {}
    public function setAi_type($type) {}
    public function setAi_responses_id($id) {}
    public function save() { return 1; }
}

class Video
{
    public static $saved = [];
    public static function saveImageInVideoLib($videos_id, $imageContent, $imageExt = 'png', $prefix = '')
    {
        self::$saved[] = $imageContent;
        return true;
    }
}

class Fetch
{
    public static $urls = [];
}

function url_get_contents($url)
{
    Fetch::$urls[] = $url;
    return 'image-bytes';
}

function rateLimitedLog($key, $message) {}

// Run the production isValidURL() without loading the live configuration.
$aiImageCallbackSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
preg_match('/function isValidURL\(\$url\)\r?\n\{.*?\r?\n\}/s', $aiImageCallbackSource, $aiImageCallbackMatch);
eval('namespace ' . __NAMESPACE__ . '; ' . $aiImageCallbackMatch[0]);
unset($aiImageCallbackSource, $aiImageCallbackMatch);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\AiImageCallbackFixtures\Fetch;
use Tests\Security\AiImageCallbackFixtures\Video;

/**
 * GHSA-92c3-6rq2-q65q: the AI image callback must only fetch http(s) URLs, never a local path.
 */
class AiImageCallbackLocalPathTest extends TestCase
{
    private $request;

    protected function setUp(): void
    {
        $this->request = $_REQUEST;
        Fetch::$urls = [];
        Video::$saved = [];
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->request;
    }

    private function runImageCallback($url)
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/AI/receiveAsync.json.php');
        $start = strpos($source, 'case AI::$typeImage:');
        $this->assertNotFalse($start);
        $start += strlen('case AI::$typeImage:');
        $end = strpos($source, 'break;', $start);
        $_REQUEST = ['response' => ['data' => [['url' => $url]]], 'msg' => ''];
        $token = (object) ['ai_responses_id' => 1, 'videos_id' => 1];
        $jsonDecoded = new \stdClass();
        eval('namespace Tests\Security\AiImageCallbackFixtures; ' . substr($source, $start, $end - $start));
        return $jsonDecoded;
    }

    public function testLocalPathsAreNeverFetched()
    {
        foreach (['/var/www/html/videos/cache/../../../../etc/hostname', '/etc/hostname', 'php://input'] as $url) {
            $result = $this->runImageCallback($url);
            $this->assertFalse($result->error, $url);
        }
        $this->assertSame([], Fetch::$urls);
        $this->assertSame([], Video::$saved);
    }

    public function testHttpImageUrlIsStillFetchedAndSaved()
    {
        $url = 'https://ai.example.com/images/result.png?sig=abc';
        $result = $this->runImageCallback($url);
        $this->assertFalse($result->error);
        $this->assertSame([$url], Fetch::$urls);
        $this->assertSame(['image-bytes'], Video::$saved);
    }
}
