<?php

namespace Tests\Security\LiveRestreamHistoryFixtures;

require_once dirname(__DIR__) . '/fixtures/live-restream-endpoints.php';

class HistoryStore
{
    public static $rows = [];
}

// Runs the production LiveTransmitionHistory::isFromUserOrCompany() against an in-memory table.
$liveHistorySource = file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/Objects/LiveTransmitionHistory.php');
$liveHistoryStart = strpos($liveHistorySource, 'static function isFromUserOrCompany(');
$liveHistoryMethod = 'static function isFromUserOrCompany($id, $users_id) { throw new \RuntimeException("LiveTransmitionHistory::isFromUserOrCompany() not found"); }';
if ($liveHistoryStart !== false) {
    $liveHistoryDepth = 0;
    for ($liveHistoryIndex = strpos($liveHistorySource, '{', $liveHistoryStart); $liveHistoryIndex < strlen($liveHistorySource); $liveHistoryIndex++) {
        if ($liveHistorySource[$liveHistoryIndex] === '{') {
            $liveHistoryDepth++;
        } elseif ($liveHistorySource[$liveHistoryIndex] === '}' && --$liveHistoryDepth === 0) {
            break;
        }
    }
    $liveHistoryMethod = substr($liveHistorySource, $liveHistoryStart, $liveHistoryIndex - $liveHistoryStart + 1);
}
eval('namespace ' . __NAMESPACE__ . ';
class LiveTransmitionHistory {
    private $users_id; private $users_id_company;
    public function __construct($id) {
        $row = HistoryStore::$rows[$id] ?? [];
        $this->users_id = $row["users_id"] ?? null;
        $this->users_id_company = $row["users_id_company"] ?? null;
    }
    public function getUsers_id() { return $this->users_id; }
    public function getUsers_id_company(): int { return intval($this->users_id_company); }
    ' . $liveHistoryMethod . '
}');
unset($liveHistorySource, $liveHistoryStart, $liveHistoryMethod, $liveHistoryDepth, $liveHistoryIndex);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\LiveRestreamHistoryFixtures\HistoryStore;
use Tests\Security\LiveRestreamHistoryFixtures\LiveTransmitionHistory;
use Tests\Security\LiveRestreamHistoryFixtures\RestreamEndpointState;
use function Tests\Security\LiveRestreamHistoryFixtures\runRestreamEndpoint;

/**
 * GHSA-233c-vfmw-6rw9: the restream endpoints checked only the destination owner, so a streamer paired their own
 * destination with another user's live_transmitions_history_id and verifyTokenForAction.json.php returned that
 * live's stream key and m3u8.
 */
class LiveRestreamHistoryOwnershipTest extends TestCase
{
    protected function setUp(): void
    {
        RestreamEndpointState::$admin = false;
        RestreamEndpointState::$canStream = true;
        RestreamEndpointState::$minted = RestreamEndpointState::$fetched = RestreamEndpointState::$messages = [];
        HistoryStore::$rows = [
            42 => ['users_id' => '1', 'users_id_company' => null],
            43 => ['users_id' => '5', 'users_id_company' => null],
            44 => ['users_id' => '6', 'users_id_company' => '5'],
        ];
    }

    public function testOnlyTheOwnerOrItsCompanyMatches()
    {
        $this->assertTrue(LiveTransmitionHistory::isFromUserOrCompany(43, 5));
        $this->assertTrue(LiveTransmitionHistory::isFromUserOrCompany(44, 5));
        $this->assertTrue(LiveTransmitionHistory::isFromUserOrCompany(44, 6));
        $this->assertFalse(LiveTransmitionHistory::isFromUserOrCompany(42, 5));
        $this->assertFalse(LiveTransmitionHistory::isFromUserOrCompany(43, 6));
        $this->assertFalse(LiveTransmitionHistory::isFromUserOrCompany(999, 5));
        $this->assertFalse(LiveTransmitionHistory::isFromUserOrCompany(43, 0));
        $this->assertFalse(LiveTransmitionHistory::isFromUserOrCompany(0, 5));
    }

    public function testEveryTokenMintingEndpointChecksTheTransmission()
    {
        $root = dirname(__DIR__, 2) . '/plugin/Live/view/';
        $endpoints = [
            'Live_restreams/getAction.json.php' => '$obj->live_transmitions_history_id',
            'getRestream.json.php' => '$obj->live_transmitions_history_id',
            'Live_restreams/getLogContent.json.php' => '$live_transmitions_history_id',
        ];
        foreach ($endpoints as $file => $variable) {
            $source = file_get_contents($root . $file);
            $adminBlock = strpos($source, 'if (!User::isAdmin()) {');
            $check = strpos($source, "LiveTransmitionHistory::isFromUserOrCompany({$variable}, User::getId())");
            $this->assertNotFalse($adminBlock, $file);
            $this->assertNotFalse($check, $file);
            $this->assertGreaterThan($adminBlock, $check, $file);
            // the check must run before the restreamer is called with the token
            $this->assertLessThan(strpos($source, 'url_get_contents('), $check, $file);
        }
    }

    public static function tokenEndpoints()
    {
        return [
            ['Live_restreams/getAction.json.php', 'live_restreams_id'],
            ['getRestream.json.php', 'restreams_id'],
            ['Live_restreams/getLogContent.json.php', 'live_restreams_id'],
        ];
    }

    /** @dataProvider tokenEndpoints */
    public function testForeignTransmissionIsRejectedBeforeSigningLoggingOrFetching($endpoint, $destinationParameter)
    {
        $response = runRestreamEndpoint($endpoint, ['live_transmitions_history_id' => 42, $destinationParameter => 15, 'action' => 'start']);
        $this->assertTrue($response['denied']);
        $this->assertSame([], RestreamEndpointState::$minted);
        $this->assertSame([], RestreamEndpointState::$fetched);
        $this->assertStringNotContainsString('tokenForAction=', implode("\n", RestreamEndpointState::$messages));
    }

    /** @dataProvider tokenEndpoints */
    public function testOwnAndCompanyTransmissionsRemainAuthorized($endpoint, $destinationParameter)
    {
        foreach ([43, 44] as $history) {
            RestreamEndpointState::$minted = [];
            $response = runRestreamEndpoint($endpoint, ['live_transmitions_history_id' => $history, $destinationParameter => 15, 'action' => 'start']);
            $this->assertFalse($response['error']);
            $this->assertSame($history, RestreamEndpointState::$minted[0][0]);
            $this->assertSame(15, RestreamEndpointState::$minted[0][1]);
        }
    }

    /** @dataProvider tokenEndpoints */
    public function testForeignDestinationIsRejectedBeforeSigning($endpoint, $destinationParameter)
    {
        $response = runRestreamEndpoint($endpoint, ['live_transmitions_history_id' => 43, $destinationParameter => 16, 'action' => 'start']);
        $this->assertTrue($response['denied']);
        $this->assertSame([], RestreamEndpointState::$minted);
        $this->assertSame([], RestreamEndpointState::$fetched);
    }

    /** @dataProvider tokenEndpoints */
    public function testAdministratorsRetainCrossAccountAccess($endpoint, $destinationParameter)
    {
        RestreamEndpointState::$admin = true;
        $response = runRestreamEndpoint($endpoint, ['live_transmitions_history_id' => 42, $destinationParameter => 16, 'action' => 'start']);
        $this->assertFalse($response['error']);
        $this->assertSame(42, RestreamEndpointState::$minted[0][0]);
        $this->assertSame(16, RestreamEndpointState::$minted[0][1]);
    }

    public function testLogRecordIdsOverrideUntrustedRequestIdsBeforeAuthorization()
    {
        foreach (['Live_restreams/getAction.json.php', 'Live_restreams/getLogContent.json.php'] as $endpoint) {
            RestreamEndpointState::$minted = RestreamEndpointState::$fetched = [];
            $response = runRestreamEndpoint($endpoint, ['live_restreams_logs_id' => 8, 'live_transmitions_history_id' => 43, 'live_restreams_id' => 15, 'action' => 'log']);
            $this->assertTrue($response['denied']);
            $this->assertSame([], RestreamEndpointState::$minted);
            $this->assertSame([], RestreamEndpointState::$fetched);

            $response = runRestreamEndpoint($endpoint, ['live_restreams_logs_id' => 9, 'live_transmitions_history_id' => 42, 'live_restreams_id' => 16, 'action' => 'log']);
            $this->assertFalse($response['error']);
            $this->assertSame(44, RestreamEndpointState::$minted[0][0]);
            $this->assertSame(15, RestreamEndpointState::$minted[0][1]);
        }
    }
}
