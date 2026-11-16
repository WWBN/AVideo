<?php

namespace Tests\Unit\YouTubeKeyEndpoint;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/plugin/Live/standAloneFiles/restreamProfiles.php';

class Fixture
{
    public static $answer;
    public static $posts;
    public static $saves;
}
class LiveTransmition
{
    public static function getFromDbByUser($user) { return ['title' => 'Fixture', 'description' => '', 'users_id' => $user, 'live_servers_id' => 1]; }
}
class Live
{
    public static function getPosterThumbsImage($user, $server) { return 'https://example.test/poster.jpg'; }
}
class Live_restreams
{
    public function getUsers_id() { return 5; }
    public function getId() { return 7; }
    public static function getYouTubeBroadcastIdToReuse($id, $history) { return $history === 123 ? 'oldVideo123' : ''; }
    public static function saveYouTubeBroadcastFromRestreamerResponse($id, $history, $answer) { Fixture::$saves[] = [$id, $history, $answer]; }
}
function postVariables($url, $data, $httpOnly) { Fixture::$posts[] = [$url, $data]; return Fixture::$answer; }
function _error_log($message) {}

class LiveRestreamYouTubeKeyEndpointTest extends TestCase
{
    private $savedGlobal;
    private $savedRequest;

    protected function setUp(): void
    {
        $this->savedGlobal = $GLOBALS['global'];
        $this->savedRequest = $_REQUEST;
        $GLOBALS['global'] = [];
        Fixture::$posts = Fixture::$saves = [];
        Fixture::$answer = json_encode(['error' => false, 'broadcast_id' => 'oldVideo123', 'stream_key' => 'FAKE_KEY', 'reused_broadcast' => true, 'broadcast_status_check_supported' => true]);
        if (!\function_exists(__NAMESPACE__ . '\\invokeOAuthEndpoint')) {
            $endpointSource = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/view/Live_restreams/getLiveKey.json.php'));
            $endpointStart = strpos($endpointSource, '$lt = LiveTransmition::getFromDbByUser');
            $endpointEnd = strrpos($endpointSource, 'echo $response;') + strlen('echo $response;');
            $this->assertNotFalse($endpointStart);
            $endpointBody = substr($endpointSource, $endpointStart, $endpointEnd - $endpointStart);
            $endpointBody = str_replace('exit;', 'return;', $endpointBody);
            eval('namespace ' . __NAMESPACE__ . '; function invokeOAuthEndpoint($Live_restreams) { global $global; $parametersJson = (object) ["restream.ypt.me" => (object) ["youtube" => (object) []]];' . $endpointBody . '}');
        }
    }

    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->savedGlobal;
        $_REQUEST = $this->savedRequest;
    }

    private function runRequest($query)
    {
        $_REQUEST = $query;
        ob_start();
        try { invokeOAuthEndpoint(new Live_restreams()); return ob_get_contents(); }
        finally { ob_end_clean(); }
    }

    public function testCurrentStreamerAddsItsOwnCapabilityAndKeepsReuseSessionScoped(): void
    {
        $answer = json_decode($this->runRequest(['liveTransmitionHistory_id' => 123, 'reuseBroadcast' => 1]));
        $this->assertTrue($answer->broadcast_validation_supported);
        $this->assertSame('oldVideo123', Fixture::$posts[0][1]['reuse_broadcast_id']);
        $this->assertCount(1, Fixture::$saves);
        Fixture::$posts = [];
        $this->runRequest(['liveTransmitionHistory_id' => 124, 'reuseBroadcast' => 1]);
        $this->assertArrayNotHasKey('reuse_broadcast_id', Fixture::$posts[0][1]);
    }

    public function testManualStartAndOlderBrokerKeepTheirPreviousContract(): void
    {
        Fixture::$answer = json_encode(['error' => false, 'stream_key' => 'FAKE_KEY']);
        $answer = json_decode($this->runRequest(['liveTransmitionHistory_id' => 123]));
        $this->assertFalse(isset($answer->broadcast_validation_supported));
        $this->assertArrayNotHasKey('reuse_broadcast_id', Fixture::$posts[0][1]);
    }

    public function testStatusUsesOnlyTheReadOnlyBrokerUrlAndNeverSaves(): void
    {
        Fixture::$answer = json_encode(['error' => false, 'broadcast_id' => 'oldVideo123', 'broadcast_status' => 'complete']);
        $answer = json_decode($this->runRequest(['liveTransmitionHistory_id' => 123, 'checkYouTubeBroadcast' => 'oldVideo123']));
        $this->assertSame('complete', $answer->broadcast_status);
        $this->assertSame('https://restream.ypt.me/youtube_broadcast_status.php', Fixture::$posts[0][0]);
        $this->assertSame('oldVideo123', Fixture::$posts[0][1]['check_broadcast_id']);
        $this->assertArrayNotHasKey('reuse_broadcast_id', Fixture::$posts[0][1]);
        $this->assertSame([], Fixture::$saves);
    }

    public function testInvalidOrStaleStatusChecksNeverReachTheBroker(): void
    {
        foreach (['otherVideo123', ['oldVideo123']] as $id) {
            $answer = json_decode($this->runRequest(['liveTransmitionHistory_id' => 123, 'checkYouTubeBroadcast' => $id]));
            $this->assertTrue($answer->error);
        }
        $this->assertSame([], Fixture::$posts);
    }

    public function testNonJsonAndFailedAnswersArePreservedWithoutWarnings(): void
    {
        foreach (['<html>Unavailable</html>', '', 'null', '{"error":true}'] as $answer) {
            Fixture::$answer = $answer;
            $this->assertSame($answer, $this->runRequest(['liveTransmitionHistory_id' => 123]));
        }
    }
}
