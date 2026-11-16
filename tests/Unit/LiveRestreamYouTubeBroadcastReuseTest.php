<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once \APP_ROOT . '/plugin/Live/standAloneFiles/restreamProfiles.php';

/**
 * A YouTube destination connected through restream.ypt.me gets a new broadcast (new video id)
 * every time its key is requested. Automatic restarts of the same live session send the
 * broadcast they already have back, so YouTube keeps the same video while it is still live.
 *
 * Run with: vendor/bin/phpunit tests/Unit/LiveRestreamYouTubeBroadcastReuseTest.php
 */
class LiveRestreamYouTubeBroadcastReuseTest extends TestCase
{
    public function testAutomaticRestartAsksToReuseTheBroadcastOfItsSession()
    {
        $this->assertSame('&liveTransmitionHistory_id=123&reuseBroadcast=1', \getLiveKeyBroadcastReuseQuery(123, 'decoded from request'));
        $this->assertSame('&liveTransmitionHistory_id=123&reuseBroadcast=1', \getLiveKeyBroadcastReuseQuery('123', ''));
        $this->assertSame('&liveTransmitionHistory_id=123&reuseBroadcast=1', \getLiveKeyBroadcastReuseQuery(123, null));
    }

    public function testManualStartStillGetsANewBroadcastButIdentifiesTheSession()
    {
        $this->assertSame('&liveTransmitionHistory_id=123', \getLiveKeyBroadcastReuseQuery(123, 'start'));
    }

    public function testRequestWithoutSessionKeepsTheOldBehavior()
    {
        $this->assertSame('', \getLiveKeyBroadcastReuseQuery(0, 'decoded from request'));
        $this->assertSame('', \getLiveKeyBroadcastReuseQuery(null, ''));
        $this->assertSame('', \getLiveKeyBroadcastReuseQuery('abc', ''));
        $this->assertSame('', \getLiveKeyBroadcastReuseQuery(-5, ''));
    }

    public function testQueryNeverCarriesAnythingButTheNumericSession()
    {
        $this->assertSame('&liveTransmitionHistory_id=12&reuseBroadcast=1', \getLiveKeyBroadcastReuseQuery('12&reuseBroadcast=0', ''));
    }

    public function testBroadcastIdValidation()
    {
        $this->assertTrue(\isValidYouTubeBroadcastId('jj0BB6AO2AM'));
        $this->assertTrue(\isValidYouTubeBroadcastId('a_b-C9xyz'));
        $this->assertFalse(\isValidYouTubeBroadcastId(''));
        $this->assertFalse(\isValidYouTubeBroadcastId('short'));
        $this->assertFalse(\isValidYouTubeBroadcastId('jj0BB6AO2AM&x=1'));
        $this->assertFalse(\isValidYouTubeBroadcastId("jj0BB6AO2AM\n"));
        $this->assertFalse(\isValidYouTubeBroadcastId('../../etc/passwd'));
        $this->assertFalse(\isValidYouTubeBroadcastId(str_repeat('a', 65)));
        $this->assertFalse(\isValidYouTubeBroadcastId(null));
        $this->assertFalse(\isValidYouTubeBroadcastId(['jj0BB6AO2AM']));
        $this->assertFalse(\isValidYouTubeBroadcastId(12345678));
    }

    public function testBroadcastIdIsTakenOnlyFromASuccessfulAnswer()
    {
        $ok = json_encode([
            'error' => false,
            'stream_key' => 'abcd-efgh-ijkl-mnop',
            'stream_url' => 'rtmp://a.rtmp.youtube.com/live2',
            'broadcast_id' => 'jj0BB6AO2AM',
            'live_url' => 'https://www.youtube.com/watch?v=jj0BB6AO2AM',
        ]);
        $this->assertSame('jj0BB6AO2AM', \getYouTubeBroadcastIdFromRestreamerResponse($ok));
        $this->assertSame('jj0BB6AO2AM', \getYouTubeBroadcastIdFromRestreamerResponse(json_decode($ok)));
    }

    public function testNoBroadcastIdFromFailedOrForeignAnswers()
    {
        // Older restream.ypt.me, Facebook/Twitch answers: no broadcast_id.
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(json_encode(['error' => false, 'stream_key' => 'k', 'stream_url' => 'rtmp://x/app'])));
        // Failed answers.
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(json_encode(['error' => true, 'stream_key' => 'k', 'broadcast_id' => 'jj0BB6AO2AM'])));
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(json_encode(['stream_key' => 'k', 'broadcast_id' => 'jj0BB6AO2AM'])));
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(json_encode(['error' => 0, 'stream_key' => 'k', 'broadcast_id' => 'jj0BB6AO2AM'])));
        // No key: nothing to reconnect to.
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(json_encode(['error' => false, 'stream_key' => '', 'broadcast_id' => 'jj0BB6AO2AM'])));
        // Invalid id.
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(json_encode(['error' => false, 'stream_key' => 'k', 'broadcast_id' => '../x/y/z'])));
        // Not JSON / empty.
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse('<html>error</html>'));
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(''));
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(false));
        $this->assertSame('', \getYouTubeBroadcastIdFromRestreamerResponse(null));
    }

    public function testRestreamerPassesTheSessionAndRequestTypeOnBothKeyRequests()
    {
        $source = file_get_contents(\APP_ROOT . '/plugin/Live/standAloneFiles/restreamer.json.php');
        $this->assertStringContainsString('function _getLiveKey($token, $liveTransmitionHistory_id = 0, $requestType = \'\')', $source);
        $this->assertStringContainsString('$getKeyURL .= getLiveKeyBroadcastReuseQuery($liveTransmitionHistory_id, $requestType);', $source);
        $this->assertSame(2, substr_count($source, '_getLiveKey($token, @$robj->liveTransmitionHistory_id, @$robj->type)'));
        $this->assertSame(0, substr_count($source, '_getLiveKey($token)'));
    }

    public function testGetLiveKeyAsksForReuseOnlyWhenRequestedAndRemembersTheAnswer()
    {
        $source = file_get_contents(\APP_ROOT . '/plugin/Live/view/Live_restreams/getLiveKey.json.php');
        $this->assertMatchesRegularExpression('/if \(!empty\(\$_REQUEST\[\'reuseBroadcast\'\]\) && \$liveTransmitionHistory_id > 0\)/', $source);
        $this->assertStringContainsString("\$array['reuse_broadcast_id'] = \$reuseBroadcastId;", $source);
        $this->assertStringContainsString('Live_restreams::saveYouTubeBroadcastFromRestreamerResponse($Live_restreams->getId(), $liveTransmitionHistory_id, $response);', $source);
        // The manually configured ("Local") branch must not be touched by the reuse logic.
        $localBranchEnd = strpos($source, '} else {');
        $this->assertNotFalse($localBranchEnd);
        $this->assertStringNotContainsString('reuse', strtolower(substr($source, 0, $localBranchEnd)));
    }
}
