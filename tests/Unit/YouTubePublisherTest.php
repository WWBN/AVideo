<?php

namespace Tests\Unit;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

$GLOBALS['isStandAlone'] = true; // locale/function.php without a database or session
if (!function_exists('_isRTL')) {
    require_once \APP_ROOT . '/locale/function.php';
}
if (!function_exists('_substr')) {
    require_once \APP_ROOT . '/objects/functionsPHP.php';
}
require_once \APP_ROOT . '/plugin/SocialMediaPublisher/Objects/YouTubePublisher.php';

/**
 * YouTubePublisherTest
 *
 * plugin/SocialMediaPublisher/Objects/YouTubePublisher.php: metadata limits, YouTube error mapping,
 * what the browser may see of a job, and the resumable upload loop driven against mocked
 * YouTube HTTP answers (Guzzle MockHandler behind a real Google_Client). Nothing here talks to
 * YouTube; the real OAuth/upload flow needs manual testing with a connected account.
 *
 * Run with: vendor/bin/phpunit tests/Unit/YouTubePublisherTest.php
 */
class YouTubePublisherTest extends TestCase
{
    private $tmpFiles = array();

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $file) {
            @unlink($file);
        }
        $this->tmpFiles = array();
        parent::tearDown();
    }

    /* ---------------- metadata ---------------- */

    public function testTitleIsCleanedAndCutTo100Characters()
    {
        $this->assertSame('Hello world', \YouTubePublisher::sanitizeTitle("  <b>Hello</b>\n\t world  "));
        $this->assertSame('a b', \YouTubePublisher::sanitizeTitle('a < b >'));
        $this->assertSame('I 3 it', \YouTubePublisher::sanitizeTitle('I <3 it'));
        $this->assertSame('x', \YouTubePublisher::sanitizeTitle('<script>x</script><!-- c -->'));
        $this->assertSame('Tom & Jerry', \YouTubePublisher::sanitizeTitle('Tom &amp; Jerry'));
        $long = \YouTubePublisher::sanitizeTitle(str_repeat('é', 150));
        $this->assertSame(100, mb_strlen($long, 'UTF-8'));
        $this->assertTrue(mb_check_encoding($long, 'UTF-8'));
    }

    public function testEmptyTitleFallsBackToTheVideoTitleThenToUntitled()
    {
        $this->assertSame('Original', \YouTubePublisher::sanitizeTitle('  <i></i> ', 'Original'));
        $this->assertSame('Untitled', \YouTubePublisher::sanitizeTitle('', '<p></p>'));
    }

    public function testDescriptionKeepsLinesAndFitsTheByteLimit()
    {
        $this->assertSame("Line 1\nLine 2\n\nLine 3", \YouTubePublisher::sanitizeDescription('<p>Line 1<br>Line 2</p><p></p><p></p><p>Line 3</p>'));
        $this->assertSame('x  y', \YouTubePublisher::sanitizeDescription('x <> y'));
        $cut = \YouTubePublisher::sanitizeDescription(str_repeat('ção ', 2000));
        $this->assertLessThanOrEqual(\YouTubePublisher::DESCRIPTION_MAX_BYTES, strlen($cut));
        $this->assertTrue(mb_check_encoding($cut, 'UTF-8'));
    }

    public function testTagsAreDedupedCleanedAndKeptUnder500Characters()
    {
        $this->assertSame(array('music', 'live show', 'News'), \YouTubePublisher::sanitizeTags('music, Music, "live" show,, <b>News</b>'));
        $this->assertSame(array('a', 'b'), \YouTubePublisher::sanitizeTags(array('a', array('x'), 'b', '')));

        $tags = \YouTubePublisher::sanitizeTags(array_map(function ($i) {
            return "tag number {$i}";
        }, range(1, 100)));
        $total = 0;
        foreach ($tags as $i => $tag) {
            // a tag with spaces counts its quotes, and every tag after the first one its comma
            $total += mb_strlen($tag) + 2 + ($i ? 1 : 0);
        }
        $this->assertLessThanOrEqual(500, $total);
        $this->assertNotEmpty($tags);
    }

    public function testPrivacyAndCategoryAreWhitelisted()
    {
        $this->assertSame('public', \YouTubePublisher::sanitizePrivacy(' PUBLIC '));
        $this->assertSame('unlisted', \YouTubePublisher::sanitizePrivacy('unlisted'));
        $this->assertSame('private', \YouTubePublisher::sanitizePrivacy('everyone'));
        $this->assertSame('private', \YouTubePublisher::sanitizePrivacy(''));
        $this->assertSame('22', \YouTubePublisher::sanitizeCategoryId('22'));
        $this->assertSame('', \YouTubePublisher::sanitizeCategoryId('22; DROP'));
        $this->assertSame('', \YouTubePublisher::sanitizeCategoryId(''));
    }

    public function testOnlyVideosWithAFileCanBePublished()
    {
        $this->assertSame('', \YouTubePublisher::getUnsupportedTypeCode('video'));
        $this->assertSame('', \YouTubePublisher::getUnsupportedTypeCode('short'));
        $this->assertSame('', \YouTubePublisher::getUnsupportedTypeCode(''));
        $this->assertSame('embedded', \YouTubePublisher::getUnsupportedTypeCode('embed'));
        $this->assertSame('embedded', \YouTubePublisher::getUnsupportedTypeCode('linkVideo'));
        $this->assertSame('unsupportedType', \YouTubePublisher::getUnsupportedTypeCode('audio'));
        $this->assertSame('unsupportedType', \YouTubePublisher::getUnsupportedTypeCode('pdf'));
    }

    /* ---------------- errors ---------------- */

    private function apiError($httpCode, $reason)
    {
        return json_encode(array('error' => array('code' => $httpCode, 'message' => 'x', 'errors' => array(array('reason' => $reason, 'domain' => 'youtube.video')))));
    }

    public function testGoogleErrorsMapToActionableMessageCodes()
    {
        $cases = array(
            array(403, 'quotaExceeded', 'quota'),
            array(400, 'uploadLimitExceeded', 'channelLimit'),
            array(401, 'youtubeSignupRequired', 'noChannel'),
            array(401, 'authError', 'reconnect'),
            array(403, 'insufficientPermissions', 'permission'),
            array(400, 'invalidTitle', 'invalidTitle'),
            array(400, 'invalidDescription', 'invalidDescription'),
            array(400, 'invalidTags', 'invalidTags'),
            array(400, 'invalidCategoryId', 'invalidCategory'),
            array(403, 'forbiddenPrivacySetting', 'invalidPrivacy'),
            array(403, 'accessNotConfigured', 'misconfigured'),
            array(429, 'somethingNew', 'rateLimit'),
            array(503, 'backendError', 'network'),
            array(400, 'somethingNew', 'unknown'),
        );
        foreach ($cases as $case) {
            $parsed = \YouTubePublisher::parseGoogleError($this->apiError($case[0], $case[1]));
            $this->assertSame($case[0], $parsed['httpCode'], $case[1]);
            $this->assertSame($case[1], $parsed['reason']);
            $this->assertSame($case[2], \YouTubePublisher::classifyGoogleError($parsed['httpCode'], $parsed['reason']), $case[1]);
        }
        $oauth = \YouTubePublisher::parseGoogleError('{"error":"invalid_grant","error_description":"Token has been expired or revoked."}', 400);
        $this->assertSame('reconnect', \YouTubePublisher::classifyGoogleError($oauth['httpCode'], $oauth['reason']));
    }

    public function testEveryMessageCodeHasItsOwnText()
    {
        $unknown = \YouTubePublisher::getMessage('notACode');
        $codes = array('quota', 'channelLimit', 'rateLimit', 'noChannel', 'permission', 'reconnect', 'authServiceUnavailable', 'connectionMissing',
            'invalidTitle', 'invalidDescription', 'invalidTags', 'invalidCategory', 'invalidPrivacy', 'invalidMetadata', 'misconfigured',
            'network', 'sourceMissing', 'unsupportedType', 'embedded', 'notConverted', 'backgroundDisabled', 'processingFailed',
            'rejectedDuplicate', 'rejectedCopyright', 'rejectedLength', 'rejectedPolicy', 'deletedOnYouTube', 'interrupted', 'didNotStart',
            'queued', 'preparing', 'uploading', 'processing', 'published', 'canceled');
        $texts = array();
        foreach ($codes as $code) {
            $text = \YouTubePublisher::getMessage($code);
            $this->assertNotSame($unknown, $text, $code);
            $this->assertNotContains($text, $texts, $code);
            $texts[] = $text;
        }
    }

    public function testUploadExceptionsAreClassifiedForRetryResumeOrFailure()
    {
        $request = new Request('PUT', 'https://www.googleapis.com/upload');
        $this->assertSame('transient', \YouTubePublisher::classifyUploadException(new ConnectException('down', $request))['kind']);
        $this->assertSame('transient', \YouTubePublisher::classifyUploadException(new \Google_Service_Exception($this->apiError(503, 'backendError'), 503))['kind']);
        $this->assertSame('transient', \YouTubePublisher::classifyUploadException(new \RuntimeException('stream error'))['kind']);
        $this->assertSame('expired', \YouTubePublisher::classifyUploadException(new \Google_Service_Exception('Not Found', 404))['kind']);
        $this->assertSame('auth', \YouTubePublisher::classifyUploadException(new \Google_Service_Exception($this->apiError(401, 'authError'), 401))['kind']);

        $quota = \YouTubePublisher::classifyUploadException(new \Google_Service_Exception($this->apiError(403, 'quotaExceeded'), 403, null, array(array('reason' => 'quotaExceeded'))));
        $this->assertSame(array('fatal', 'quota'), array($quota['kind'], $quota['code']));
        $title = \YouTubePublisher::classifyUploadException(new \Google_Service_Exception($this->apiError(400, 'invalidTitle'), 400));
        $this->assertSame(array('fatal', 'invalidTitle'), array($title['kind'], $title['code']));
        // a programming error is not retried forever
        $this->assertSame(array('fatal', 'unknown'), array_values(array_slice(\YouTubePublisher::classifyUploadException(new \TypeError('bug')), 0, 2)));
    }

    public function testProcessingStatusMapsToFinalStates()
    {
        $this->assertSame('published', \YouTubePublisher::processingResult(array('status' => array('uploadStatus' => 'processed')))['state']);
        $this->assertSame('processing', \YouTubePublisher::processingResult(array('status' => array('uploadStatus' => 'uploaded')))['state']);
        $failed = \YouTubePublisher::processingResult(array('status' => array('uploadStatus' => 'failed', 'failureReason' => 'codec')));
        $this->assertSame(array('failed', 'processingFailed'), array($failed['state'], $failed['code']));
        $this->assertSame('rejectedDuplicate', \YouTubePublisher::processingResult(array('status' => array('uploadStatus' => 'rejected', 'rejectionReason' => 'duplicate')))['code']);
        $this->assertSame('rejectedLength', \YouTubePublisher::processingResult(array('status' => array('uploadStatus' => 'rejected', 'rejectionReason' => 'length')))['code']);
        $this->assertSame('rejectedPolicy', \YouTubePublisher::processingResult(array('status' => array('uploadStatus' => 'rejected', 'rejectionReason' => 'termsOfUse')))['code']);
    }

    /* ---------------- job shown to the browser ---------------- */

    public function testPublicJobNeverContainsTheResumeUriOrTechnicalDetail()
    {
        $job = array('state' => 'uploading', 'resumeUri' => 'https://upload.example/?upload_id=SECRET', 'detail' => 'HTTP 500', 'uploadedBytes' => 50, 'totalBytes' => 200, 'updatedAt' => time());
        $public = \YouTubePublisher::getPublicJob($job, true);
        $this->assertStringNotContainsString('SECRET', json_encode($public));
        $this->assertSame(25, (int) $public['percent']);
        $this->assertTrue($public['isActive']);
        $this->assertTrue($public['canCancel']);
        $this->assertFalse($public['canResume']);

        $redacted = \YouTubePublisher::redactDetails(json_encode(array('job' => $job, 'publisher_user_preferences_id' => 3)));
        $this->assertStringNotContainsString('SECRET', $redacted);
        $this->assertSame(3, json_decode($redacted, true)['publisher_user_preferences_id']);
    }

    public function testAJobWhoseWorkerDiedIsShownAsInterruptedAndCanBeResumed()
    {
        $now = time();
        $uploading = array('state' => 'uploading', 'updatedAt' => $now);
        $this->assertSame('uploading', \YouTubePublisher::getEffectiveState($uploading, true, $now));
        $interrupted = \YouTubePublisher::getPublicJob($uploading, false, $now);
        $this->assertSame(array('interrupted', 'interrupted', true), array($interrupted['state'], $interrupted['code'], $interrupted['canResume']));

        // the worker gets a few seconds to start
        $this->assertSame('queued', \YouTubePublisher::getEffectiveState(array('state' => 'queued', 'queuedAt' => $now - 5), false, $now));
        $late = \YouTubePublisher::getPublicJob(array('state' => 'queued', 'queuedAt' => $now - 600), false, $now);
        $this->assertSame(array('interrupted', 'didNotStart'), array($late['state'], $late['code']));

        // lock file not readable: the heartbeat decides
        $this->assertSame('uploading', \YouTubePublisher::getEffectiveState(array('state' => 'uploading', 'updatedAt' => $now - 30), null, $now));
        $this->assertSame('interrupted', \YouTubePublisher::getEffectiveState(array('state' => 'uploading', 'updatedAt' => $now - 3600), null, $now));
    }

    public function testRetryIsOfferedOnlyWhenRetryingCanWork()
    {
        $quota = \YouTubePublisher::getPublicJob(array('state' => 'failed', 'code' => 'quota'), false);
        $this->assertTrue($quota['isError']);
        $this->assertTrue($quota['canResume']);
        $this->assertFalse($quota['needsReconnect']);

        $title = \YouTubePublisher::getPublicJob(array('state' => 'failed', 'code' => 'invalidTitle'), false);
        $this->assertFalse($title['canResume']);

        $reconnect = \YouTubePublisher::getPublicJob(array('state' => 'failed', 'code' => 'reconnect'), false);
        $this->assertTrue($reconnect['needsReconnect']);
        $this->assertTrue($reconnect['canResume']);

        $published = \YouTubePublisher::getPublicJob(array('state' => 'published', 'url' => 'https://youtu.be/abc'), false);
        $this->assertSame(array(100, false, false, 'https://youtu.be/abc'), array((int) $published['percent'], $published['isError'], $published['canCancel'], $published['url']));
    }

    public function testUploadsMadeBeforeTheWorkerAreStillRecognized()
    {
        $job = \YouTubePublisher::getJobFromDetails(json_encode(array('publisher_user_preferences_id' => 1, 'response' => array('id' => 'old123'))));
        $this->assertSame('published', $job['state']);
        $this->assertSame('https://youtu.be/old123', $job['url']);
        $failed = \YouTubePublisher::getJobFromDetails(array('publisher_user_preferences_id' => 1, 'response' => array('error' => true, 'msg' => 'x')));
        $this->assertSame('failed', $failed['state']);
        $this->assertSame(array(), \YouTubePublisher::getJobFromDetails('not json'));
    }

    public function testTokenExpirationIsReadFromTheBrokerConnection()
    {
        $json = json_encode(array('restream.ypt.me' => array('accessToken' => array('access_token' => 'a', 'refresh_token' => 'r', 'expires_at' => 1000))));
        $this->assertSame(1000, \YouTubePublisher::getTokenExpiresAt($json));
        $json = json_encode(array('restream.ypt.me' => array('accessToken' => array('access_token' => 'a'), 'expires' => array('expires_at' => 2000))));
        $this->assertSame(2000, \YouTubePublisher::getTokenExpiresAt($json));
        $this->assertSame(0, \YouTubePublisher::getTokenExpiresAt('{}'));

        $this->assertTrue(\YouTubePublisher::tokenNeedsRefresh(1100, 1000));
        $this->assertFalse(\YouTubePublisher::tokenNeedsRefresh(5000, 1000));
        $this->assertFalse(\YouTubePublisher::tokenNeedsRefresh(0, 1000)); // unknown: try it, a 401 renews it
    }

    /* ---------------- resumable upload against mocked YouTube answers ---------------- */

    private function tempFile($bytes)
    {
        $file = tempnam(sys_get_temp_dir(), 'ytpub');
        file_put_contents($file, str_repeat('A', $bytes));
        $this->tmpFiles[] = $file;
        return $file;
    }

    private function mockClient(array $responses, array &$history, $token = 'token-1')
    {
        $mock = new MockHandler($responses);
        // records at the handler, after Google's auth middleware added the Authorization header
        $stack = HandlerStack::create(function ($request, $options) use ($mock, &$history) {
            $history[] = array('request' => $request);
            return $mock($request, $options);
        });
        $client = \YouTubePublisher::createClient($token);
        $client->setHttpClient(new \GuzzleHttp\Client(array('handler' => $stack, 'http_errors' => false)));
        return $client;
    }

    private function metadata()
    {
        return \YouTubePublisher::buildMetadata(array('title' => 'My video', 'description' => 'Desc', 'tags' => 'a, b', 'categoryId' => '22', 'privacy' => 'unlisted'));
    }

    private function session()
    {
        return new Response(200, array('Location' => 'https://upload.test/session-1'));
    }

    private function done($id = 'yt123')
    {
        return new Response(200, array('Content-Type' => 'application/json'), json_encode(array('kind' => 'youtube#video', 'id' => $id)));
    }

    public function testUploadSendsTheMetadataAndTheFileInChunks()
    {
        $size = \YouTubePublisher::CHUNK_SIZE + 100;
        $file = $this->tempFile($size);
        $history = array();
        $client = $this->mockClient(array(
            $this->session(),
            new Response(308),
            new Response(308, array('Range' => 'bytes=0-' . (\YouTubePublisher::CHUNK_SIZE - 1))),
            $this->done(),
        ), $history);
        $sessions = array();
        $progress = array();
        $handle = fopen($file, 'rb');
        $id = \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, $size, '', array(
            'onSession' => function ($uri) use (&$sessions) {
                $sessions[] = $uri;
            },
            'onProgress' => function ($bytes) use (&$progress) {
                $progress[] = $bytes;
            },
        ));
        fclose($handle);

        $this->assertSame('yt123', $id);
        $this->assertSame(array('https://upload.test/session-1'), $sessions);
        $this->assertSame(array(\YouTubePublisher::CHUNK_SIZE, $size), $progress);
        $this->assertCount(4, $history);

        $start = $history[0]['request'];
        $this->assertSame('POST', $start->getMethod());
        $this->assertStringContainsString('/upload/youtube/v3/videos', (string) $start->getUri());
        $this->assertStringContainsString('uploadType=resumable', (string) $start->getUri());
        $this->assertSame((string) $size, $start->getHeaderLine('x-upload-content-length'));
        $this->assertSame('Bearer token-1', $start->getHeaderLine('Authorization'));
        $meta = json_decode((string) $start->getBody(), true);
        $this->assertSame('My video', $meta['snippet']['title']);
        $this->assertSame(array('a', 'b'), $meta['snippet']['tags']);
        $this->assertSame('22', $meta['snippet']['categoryId']);
        $this->assertSame('unlisted', $meta['status']['privacyStatus']);

        $this->assertSame("bytes */{$size}", $history[1]['request']->getHeaderLine('content-range'));
        $this->assertSame('bytes 0-' . (\YouTubePublisher::CHUNK_SIZE - 1) . "/{$size}", $history[2]['request']->getHeaderLine('content-range'));
        $this->assertSame('bytes ' . \YouTubePublisher::CHUNK_SIZE . '-' . ($size - 1) . "/{$size}", $history[3]['request']->getHeaderLine('content-range'));
        $this->assertSame('https://upload.test/session-1', (string) $history[3]['request']->getUri());
    }

    public function testATemporaryErrorIsRetriedFromTheByteYouTubeConfirms()
    {
        $file = $this->tempFile(1000);
        $history = array();
        $client = $this->mockClient(array(
            $this->session(),
            new Response(308),
            new Response(503, array(), $this->apiError(503, 'backendError')),
            new Response(308, array('Range' => 'bytes=0-499')),
            $this->done(),
        ), $history);
        $sleeps = array();
        $handle = fopen($file, 'rb');
        $id = \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, '', array(
            'sleep' => function ($seconds) use (&$sleeps) {
                $sleeps[] = $seconds;
            },
        ));
        fclose($handle);
        $this->assertSame('yt123', $id);
        $this->assertCount(1, $sleeps);
        $this->assertSame('bytes */1000', $history[3]['request']->getHeaderLine('content-range'));
        $this->assertSame('bytes 500-999/1000', $history[4]['request']->getHeaderLine('content-range'));
    }

    public function testAnExpiredTokenIsRenewedDuringTheUpload()
    {
        $file = $this->tempFile(1000);
        $history = array();
        $client = $this->mockClient(array(
            $this->session(),
            new Response(308),
            new Response(401, array(), $this->apiError(401, 'authError')),
            new Response(308),
            $this->done(),
        ), $history);
        $forced = array();
        $handle = fopen($file, 'rb');
        $id = \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, '', array(
            'getToken' => function ($force) use (&$forced) {
                $forced[] = $force;
                return $force ? 'token-2' : null;
            },
        ));
        fclose($handle);
        $this->assertSame('yt123', $id);
        $this->assertContains(true, $forced);
        $this->assertSame('Bearer token-1', $history[2]['request']->getHeaderLine('Authorization'));
        $this->assertSame('Bearer token-2', $history[4]['request']->getHeaderLine('Authorization'));
    }

    public function testARevokedTokenAsksToReconnect()
    {
        $file = $this->tempFile(1000);
        $history = array();
        $client = $this->mockClient(array(
            $this->session(),
            new Response(308),
            new Response(401, array(), $this->apiError(401, 'authError')),
        ), $history);
        $handle = fopen($file, 'rb');
        try {
            \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, '', array(
                'getToken' => function ($force) {
                    return null;
                },
            ));
            $this->fail('expected an exception');
        } catch (\YouTubePublisherException $e) {
            $this->assertSame('reconnect', $e->getErrorCode());
            $this->assertFalse($e->isResumable());
        } finally {
            fclose($handle);
        }
    }

    public function testQuotaErrorWhenStartingIsReportedWithItsReason()
    {
        $file = $this->tempFile(1000);
        $history = array();
        // MediaFileUpload alone would only report "HTTP 403" here
        $client = $this->mockClient(array(
            new Response(403, array('Content-Type' => 'application/json'), $this->apiError(403, 'quotaExceeded')),
        ), $history);
        $handle = fopen($file, 'rb');
        try {
            \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, '');
            $this->fail('expected an exception');
        } catch (\YouTubePublisherException $e) {
            $this->assertSame('quota', $e->getErrorCode());
            $this->assertFalse($e->isResumable());
        } finally {
            fclose($handle);
        }
        $this->assertCount(1, $history);
    }

    public function testAnInterruptedUploadContinuesTheSameSession()
    {
        $file = $this->tempFile(1000);
        $history = array();
        $client = $this->mockClient(array(
            new Response(308, array('Range' => 'bytes=0-599')),
            $this->done(),
        ), $history);
        $sessions = array();
        $handle = fopen($file, 'rb');
        $id = \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, 'https://upload.test/old-session', array(
            'onSession' => function ($uri) use (&$sessions) {
                $sessions[] = $uri;
            },
        ));
        fclose($handle);
        $this->assertSame('yt123', $id);
        $this->assertSame(array(), $sessions);
        $this->assertSame('https://upload.test/old-session', (string) $history[0]['request']->getUri());
        $this->assertSame('bytes 600-999/1000', $history[1]['request']->getHeaderLine('content-range'));
    }

    public function testAnUploadAlreadyFinishedOnYouTubeIsNotSentAgain()
    {
        $file = $this->tempFile(1000);
        $history = array();
        $client = $this->mockClient(array($this->done('already')), $history);
        $handle = fopen($file, 'rb');
        $id = \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, 'https://upload.test/old-session');
        fclose($handle);
        $this->assertSame('already', $id);
        $this->assertCount(1, $history);
    }

    public function testAnExpiredSessionStartsAgainFromTheBeginning()
    {
        $file = $this->tempFile(1000);
        $history = array();
        $client = $this->mockClient(array(
            new Response(404, array(), 'Not Found'),
            $this->session(),
            new Response(308),
            $this->done(),
        ), $history);
        $sessions = array();
        $handle = fopen($file, 'rb');
        $id = \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, 'https://upload.test/expired', array(
            'onSession' => function ($uri) use (&$sessions) {
                $sessions[] = $uri;
            },
        ));
        fclose($handle);
        $this->assertSame('yt123', $id);
        $this->assertSame(array('https://upload.test/session-1'), $sessions);
        $this->assertSame('bytes 0-999/1000', $history[3]['request']->getHeaderLine('content-range'));
    }

    public function testANetworkThatStaysDownPausesTheUploadSoItCanBeResumed()
    {
        $file = $this->tempFile(1000);
        $history = array();
        $responses = array($this->session(), new Response(308));
        for ($i = 0; $i <= \YouTubePublisher::MAX_TRANSIENT_RETRIES; $i++) {
            $responses[] = new ConnectException('Connection timed out', new Request('PUT', 'https://upload.test/session-1'));
        }
        $client = $this->mockClient($responses, $history);
        $sleeps = 0;
        $handle = fopen($file, 'rb');
        try {
            \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, '', array(
                'sleep' => function () use (&$sleeps) {
                    $sleeps++;
                },
            ));
            $this->fail('expected an exception');
        } catch (\YouTubePublisherException $e) {
            $this->assertSame('network', $e->getErrorCode());
            $this->assertTrue($e->isResumable());
        } finally {
            fclose($handle);
        }
        $this->assertSame(\YouTubePublisher::MAX_TRANSIENT_RETRIES, $sleeps);
    }

    public function testCancelStopsBeforeTheNextChunk()
    {
        $file = $this->tempFile(1000);
        $history = array();
        $client = $this->mockClient(array($this->session(), new Response(308)), $history);
        $handle = fopen($file, 'rb');
        try {
            \YouTubePublisher::uploadFile($client, $this->metadata(), $handle, 1000, '', array(
                'isCanceled' => function () {
                    return true;
                },
            ));
            $this->fail('expected an exception');
        } catch (\YouTubePublisherException $e) {
            $this->assertSame('canceled', $e->getErrorCode());
        } finally {
            fclose($handle);
        }
        $this->assertCount(2, $history);
    }

    public function testTheClientNeverGetsTheRefreshToken()
    {
        // with a refresh_token Google_Client would try to refresh with a client secret this site does not have
        $client = \YouTubePublisher::createClient('abc');
        $this->assertSame(array('access_token' => 'abc'), $client->getAccessToken());
    }
}
