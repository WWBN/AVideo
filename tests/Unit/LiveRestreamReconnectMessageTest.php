<?php

namespace Tests\Unit\RestreamReconnectMessage;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/plugin/Live/standAloneFiles/restreamProfiles.php';

class ObjectYPT
{
}

function __($text)
{
    return $text;
}

function object_to_array($value)
{
    return json_decode(json_encode($value), true);
}

/**
 * Account connections that restream.ypt.me can no longer use (expired/revoked token, consent
 * without the YouTube permission) must reach the owner as an actionable message instead of the
 * provider's raw error (Light and Truth TV, 2026-11-18).
 */
class LiveRestreamReconnectMessageTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(__NAMESPACE__ . '\\Live_restreams', false)) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/Objects/Live_restreams.php');
            $source = substr($source, strpos($source, 'class Live_restreams'));
            eval('namespace ' . __NAMESPACE__ . '; use \\stdClass;' . $source);
        }
    }

    private function parameters($provider)
    {
        return json_encode(['restream.ypt.me' => [$provider => ['access_token' => ['access_token' => 'FAKE', 'refresh_token' => 'FAKE_REFRESH', 'expires_at' => 1773880448]]]]);
    }

    private function googleError($code, $message, $status)
    {
        return (object) ['error' => (object) ['code' => $code, 'message' => $message, 'status' => $status]];
    }

    public function testRevokedGoogleRefreshTokenAsksToReconnectYouTube(): void
    {
        $response = (object) [
            'error' => true,
            'access_token' => (object) [
                'access_token' => 'FAKE',
                'msg' => 'Token refresh failed: Unable to refresh the access token. HTTP error 400. Raw Provider API response: {"error": "invalid_grant", "error_description": "Token has been expired or revoked."}.',
            ],
            'msg' => $this->googleError(401, 'Request had invalid authentication credentials.', 'UNAUTHENTICATED'),
        ];
        $info = Live_restreams::getReconnectInfoFromRestreamerResponse($response, 'youtube : channel@example.com', $this->parameters('youtube'));

        $this->assertSame('expired', $info->reason);
        $this->assertSame('youtube', $info->provider);
        $this->assertSame('YouTube', $info->providerLabel);
        $this->assertStringContainsString('"youtube : channel@example.com"', $info->msg);
        $this->assertStringContainsString('reconnect your YouTube account', $info->msg);
        $this->assertStringNotContainsString('invalid_grant', $info->msg);
        $this->assertStringNotContainsString('FAKE', $info->msg);
    }

    public function testConsentWithoutTheYouTubePermissionIsExplained(): void
    {
        $response = (object) [
            'error' => true,
            'access_token' => (object) ['access_token' => 'FAKE', 'msg' => 'Token refresh successful: Access token updated.'],
            'msg' => $this->googleError(403, 'Request had insufficient authentication scopes.', 'PERMISSION_DENIED'),
        ];
        $info = Live_restreams::getReconnectInfoFromRestreamerResponse($response, 'youtube : channel@example.com', $this->parameters('youtube'));

        $this->assertSame('permission', $info->reason);
        $this->assertStringContainsString('permission box', $info->msg);
    }

    public function testExpiredFacebookAndTwitchSessionsAreRecognized(): void
    {
        $facebook = (object) ['error' => true, 'msg' => "Error validating access token: Session has expired on Sunday.\n"];
        $info = Live_restreams::getReconnectInfoFromRestreamerResponse($facebook, 'facebook : Church', $this->parameters('facebook'));
        $this->assertSame('expired', $info->reason);
        $this->assertSame('Facebook', $info->providerLabel);

        // get_twitch.php returns false in msg and keeps the Helix error in rawData.
        $twitch = (object) [
            'error' => true,
            'msg' => false,
            'rawData' => '{"error":"Unauthorized","status":401,"message":"Invalid OAuth token"}',
            'access_token' => (object) ['msg' => 'Token refresh failed: Invalid refresh token'],
        ];
        $info = Live_restreams::getReconnectInfoFromRestreamerResponse($twitch, 'twitch : church', $this->parameters('twitch'));
        $this->assertSame('expired', $info->reason);
        $this->assertSame('twitch', $info->provider);

        // getLiveKey.json.php passes the already decoded parameters
        $info = Live_restreams::getReconnectInfoFromRestreamerResponse($twitch, 'twitch : church', json_decode($this->parameters('twitch')));
        $this->assertSame('Twitch', $info->providerLabel);
    }

    public function testOtherFailuresKeepTheirOriginalMessage(): void
    {
        $quota = (object) ['error' => true, 'msg' => $this->googleError(403, 'The user is not enabled for live streaming.', 'PERMISSION_DENIED')];
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse($quota, 'youtube : a', $this->parameters('youtube')));

        $generic = (object) ['error' => true, 'msg' => 'Could not check or prepare the YouTube broadcast. Please retry.'];
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse($generic, 'youtube : a', $this->parameters('youtube')));

        $success = (object) ['error' => false, 'msg' => '', 'stream_key' => 'FAKE'];
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse($success, 'youtube : a', $this->parameters('youtube')));
    }

    public function testManualDestinationsAndUnreadableAnswersAreIgnored(): void
    {
        $revoked = (object) ['error' => true, 'msg' => 'invalid_grant'];
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse($revoked, 'manual', json_encode(['other' => true])));
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse($revoked, 'manual', ''));
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse(null, 'youtube : a', $this->parameters('youtube')));
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse('not json', 'youtube : a', $this->parameters('youtube')));
    }

    public function testProviderFailuresAreNotReplacedByRefreshDiagnostics(): void
    {
        foreach (array('quotaExceeded', 'liveStreamingNotEnabled') as $reason) {
            foreach (array('Refresh failed: No refresh token available.', 'Token refresh failed: invalid_grant') as $refreshMessage) {
                $response = (object) [
                    'error' => true,
                    'msg' => (object) ['error' => (object) ['code' => 403, 'errors' => [(object) ['reason' => $reason]]]],
                    'access_token' => (object) ['msg' => $refreshMessage],
                ];
                $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse($response, 'youtube : a', $this->parameters('youtube')));
            }
        }
        $network = (object) [
            'error' => true,
            'msg' => 'Could not check or prepare the YouTube broadcast. Please retry.',
            'access_token' => (object) ['msg' => 'Refresh failed: No refresh token available.'],
        ];
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse($network, 'youtube : a', $this->parameters('youtube')));
    }

    public function testRefreshErrorIsUsedWhenTheProviderReturnedNoDetails(): void
    {
        foreach (array('invalid_grant', 'No refresh token available') as $message) {
            $response = (object) ['error' => true, 'msg' => false, 'access_token' => (object) ['msg' => $message]];
            $info = Live_restreams::getReconnectInfoFromRestreamerResponse($response, 'youtube : a', $this->parameters('youtube'));
            $this->assertSame('expired', $info->reason);
        }
    }

    public function testStoredNameIsDecodedForPlainTextDisplay(): void
    {
        $response = (object) ['error' => true, 'msg' => 'invalid_grant'];
        $info = Live_restreams::getReconnectInfoFromRestreamerResponse($response, 'youtube : Rock &amp; Roll&#039;s &lt;Live&gt;', $this->parameters('youtube'));
        $this->assertStringContainsString('"youtube : Rock & Roll\'s <Live>"', $info->msg);
        $this->assertStringNotContainsString('&amp;', $info->msg);
    }

    public function testStructuredAndStringProviderErrorsAreRecognized(): void
    {
        $error = ['error' => ['code' => 401, 'message' => 'Request had invalid authentication credentials.', 'status' => 'UNAUTHENTICATED']];
        foreach (array($error, json_decode(json_encode($error)), json_encode($error, JSON_PRETTY_PRINT)) as $message) {
            $info = Live_restreams::getReconnectInfoFromRestreamerResponse((object) ['error' => true, 'msg' => $message], 'youtube : a', $this->parameters('youtube'));
            $this->assertSame('expired', $info->reason);
        }
        $unrelated = (object) ['error' => true, 'msg' => ['error' => ['code' => 4010, 'message' => 'Provider unavailable']]];
        $this->assertNull(Live_restreams::getReconnectInfoFromRestreamerResponse($unrelated, 'youtube : a', $this->parameters('youtube')));
    }
}
