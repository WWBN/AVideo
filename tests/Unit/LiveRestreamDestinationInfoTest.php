<?php

namespace Tests\Unit\RestreamDestinationInfo;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/plugin/Live/Objects/LiveRestreamDestinationInfo.php';

class ObjectYPT
{
    public static $dir = '';
    public static function getTmpCacheDir()
    {
        return self::$dir;
    }
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
 * A YouTube destination silently connected to the wrong channel (TIC, 2026-11-21): the Restream
 * panel must show which channel each destination sends to and what happened at the last live,
 * without asking anything at live time (lives can start automatically).
 */
class LiveRestreamDestinationInfoTest extends TestCase
{
    const CHANNEL_ID = 'UCabcdefghijklmnopqrstuv';

    protected function setUp(): void
    {
        if (!class_exists(__NAMESPACE__ . '\\Live_restreams', false)) {
            class_alias(\LiveRestreamDestinationInfo::class, __NAMESPACE__ . '\\LiveRestreamDestinationInfo');
            $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/Objects/Live_restreams.php');
            $source = substr($source, strpos($source, 'class Live_restreams'));
            eval('namespace ' . __NAMESPACE__ . '; use \\stdClass;' . $source);
        }
        ObjectYPT::$dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'restream-info-' . uniqid() . DIRECTORY_SEPARATOR;
    }

    private function youtubeParameters($channel = null)
    {
        $connected = ['youtube' => ['access_token' => ['access_token' => 'FAKE']], 'provider' => 'youtube'];
        if ($channel !== null) {
            $connected['channel'] = $channel;
        }
        return json_encode(['restream.ypt.me' => $connected]);
    }

    private function channel($overrides = [])
    {
        return array_merge(['id' => self::CHANNEL_ID, 'title' => 'TIC AV', 'handle' => '@TICAV',
            'thumbnail' => 'https://yt3.ggpht.com/a=s88', 'live_streaming' => 'unknown'], $overrides);
    }

    private function texts($hints)
    {
        return implode(' | ', array_column($hints, 'text'));
    }

    public function testProviderAndChannelAreReadFromTheSavedConnection(): void
    {
        $parameters = $this->youtubeParameters($this->channel());
        $this->assertSame('youtube', \LiveRestreamDestinationInfo::getConnectedProvider($parameters));
        $channel = \LiveRestreamDestinationInfo::getChannelFromParameters($parameters);
        $this->assertSame(self::CHANNEL_ID, $channel['id']);
        $this->assertSame('https://www.youtube.com/channel/' . self::CHANNEL_ID, $channel['url']);
        $this->assertSame('TIC AV (@TICAV)', \LiveRestreamDestinationInfo::getChannelLabel($channel));
        $this->assertSame('', \LiveRestreamDestinationInfo::getConnectedProvider('{"x":1}'));
        $this->assertNull(\LiveRestreamDestinationInfo::getChannelFromParameters($this->youtubeParameters()));
        $facebook = json_encode(['restream.ypt.me' => ['facebook' => ['access_token' => 'x'], 'channel' => $this->channel()]]);
        $this->assertNull(\LiveRestreamDestinationInfo::getChannelFromParameters($facebook), 'a YouTube-shaped channel is not a Facebook page');
    }

    public function testFacebookPagesAndTwitchChannelsAreShownToo(): void
    {
        $facebook = json_encode(['restream.ypt.me' => ['facebook' => ['broadcaster_id' => '1234567', 'subtitle' => 'TIC'], 'provider' => 'facebook',
            'channel' => ['provider' => 'facebook', 'kind' => 'page', 'id' => '1234567', 'title' => 'Tabernacle International Church',
                'url' => 'https://www.facebook.com/TIC', 'thumbnail' => 'https://scontent.xx.fbcdn.net/p.jpg']]]);
        $page = \LiveRestreamDestinationInfo::getChannelFromParameters($facebook);
        $this->assertSame('facebook', $page['provider']);
        $this->assertSame('Tabernacle International Church', \LiveRestreamDestinationInfo::getChannelLabel($page));
        $this->assertSame('Facebook page Tabernacle International Church', \LiveRestreamDestinationInfo::getChannelDescription($page));
        $html = \LiveRestreamDestinationInfo::getChannelHtml($page);
        $this->assertStringContainsString('fa-facebook', $html);
        $this->assertStringContainsString('https://www.facebook.com/TIC', $html);

        $profile = \LiveRestreamDestinationInfo::normalizeChannel(['id' => '1234567', 'kind' => 'profile', 'title' => 'Person', 'url' => 'https://www.facebook.com/x'], 'facebook');
        $this->assertSame('', $profile['url'], 'app-scoped profile ids have no public link');
        $this->assertSame('Person (profile)', \LiveRestreamDestinationInfo::getChannelLabel($profile));
        $this->assertStringContainsString('<span>', \LiveRestreamDestinationInfo::getChannelHtml($profile));
        $this->assertSame('https://www.facebook.com/1234567',
            \LiveRestreamDestinationInfo::normalizeChannel(['id' => '1234567', 'url' => 'https://evil.example/'], 'facebook')['url'], 'only facebook.com links');

        $twitch = \LiveRestreamDestinationInfo::normalizeChannel(['provider' => 'twitch', 'id' => '123', 'title' => 'TIC Live', 'handle' => 'TICLive',
            'thumbnail' => 'https://static-cdn.jtvnw.net/a.png'], 'twitch');
        $this->assertSame('TIC Live (ticlive)', \LiveRestreamDestinationInfo::getChannelLabel($twitch));
        $this->assertSame('https://www.twitch.tv/ticlive', $twitch['url']);
        $this->assertSame('ticlive', \LiveRestreamDestinationInfo::getChannelLabel(['provider' => 'twitch', 'id' => '123', 'handle' => 'ticlive']), 'login only once');
        $this->assertNull(\LiveRestreamDestinationInfo::normalizeChannel(['provider' => 'youtube', 'id' => '123'], 'twitch'), 'provider mismatch');
        $this->assertNull(\LiveRestreamDestinationInfo::normalizeChannel(['id' => 'abc'], 'twitch'));
    }

    public function testHintsUseTheRightWordForEachProvider(): void
    {
        $facebookRow = ['status' => 'a', 'stream_url' => 'rtmps://live-api-s.facebook.com:443/rtmp/',
            'parameters' => json_encode(['restream.ypt.me' => ['facebook' => ['broadcaster_id' => '1234567'], 'provider' => 'facebook']])];
        $this->assertStringContainsString('Checking which Facebook page', $this->texts(\LiveRestreamDestinationInfo::getHints($facebookRow, null, null, false)));
        $page = ['provider' => 'facebook', 'id' => '1234567', 'title' => 'TIC'];
        $this->assertStringContainsString('Same page as destination #44', $this->texts(\LiveRestreamDestinationInfo::getHints($facebookRow, $page, null, false, [44])));
        $twitchRow = ['status' => 'a', 'stream_url' => 'rtmp://jfk.contribute.live-video.net/app/',
            'parameters' => json_encode(['restream.ypt.me' => ['twitch' => ['broadcaster_id' => '123'], 'provider' => 'twitch']])];
        $this->assertStringContainsString('could not see which Twitch channel', $this->texts(\LiveRestreamDestinationInfo::getHints($twitchRow, null, null, false, [], true)));

        // same id on two providers is not the same destination
        $this->assertSame([], \LiveRestreamDestinationInfo::getSameChannelDestinations([
            1 => ['provider' => 'facebook', 'id' => '1234567'], 2 => ['provider' => 'twitch', 'id' => '1234567']]));
    }

    public function testBrowserPostedChannelIsValidatedAndEscaped(): void
    {
        $hostile = $this->channel([
            'title' => "<script>alert(1)</script>\n\"x\"",
            'handle' => '"><img src=x>',
            'thumbnail' => 'javascript:alert(1)',
            'live_streaming' => 'enabled',
        ]);
        $channel = \LiveRestreamDestinationInfo::normalizeChannel($hostile);
        $this->assertSame('', $channel['handle']);
        $this->assertSame('', $channel['thumbnail']);
        $this->assertSame('unknown', $channel['live_streaming'], 'nothing is ever shown as confirmed enabled');
        $this->assertStringNotContainsString("\n", $channel['title']);
        $html = \LiveRestreamDestinationInfo::getChannelHtml($hostile);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertNull(\LiveRestreamDestinationInfo::normalizeChannel(['id' => '../../etc']));
        $this->assertSame('', \LiveRestreamDestinationInfo::getChannelHtml(null));
    }

    public function testLiveStreamingNotEnabledBecomesAnActionableMessage(): void
    {
        $response = (object) ['error' => true, 'msg' => (object) ['error' => (object) ['code' => 403,
            'message' => 'The user is not enabled for live streaming.',
            'errors' => [(object) ['reason' => 'liveStreamingNotEnabled']]]]];
        $info = \LiveRestreamDestinationInfo::getLiveStreamingDisabledInfo($response, 'youtube : person@example.com');
        $this->assertSame('live_disabled', $info->reason);
        $this->assertStringContainsString('"youtube : person@example.com"', $info->msg);
        $this->assertStringContainsString('YouTube Studio', $info->msg);
        $this->assertNull(\LiveRestreamDestinationInfo::getLiveStreamingDisabledInfo((object) ['error' => true, 'msg' => 'quota'], 'x'));
        $this->assertNull(\LiveRestreamDestinationInfo::getLiveStreamingDisabledInfo((object) ['error' => false], 'x'));
        $this->assertNull(\LiveRestreamDestinationInfo::getLiveStreamingDisabledInfo(null, 'x'));
    }

    public function testLastResultKeepsOnlySafeFields(): void
    {
        $ok = \LiveRestreamDestinationInfo::getLastResult((object) ['error' => false, 'stream_key' => 'SECRET',
            'live_url' => 'https://www.youtube.com/watch?v=SgKX4uKjEAk'], '', 1000);
        $this->assertSame(['state' => 'ok', 'reason' => '', 'msg' => '', 'live_url' => 'https://www.youtube.com/watch?v=SgKX4uKjEAk', 'at' => 1000], $ok);
        $this->assertStringNotContainsString('SECRET', json_encode($ok));
        $this->assertSame('', \LiveRestreamDestinationInfo::getLastResult((object) ['error' => false, 'live_url' => 'javascript:alert(1)'])['live_url']);

        $error = \LiveRestreamDestinationInfo::getLastResult((object) ['error' => true,
            'msg' => (object) ['error' => (object) ['message' => 'Quota <b>exceeded</b>']]], 'whatever', 1000);
        $this->assertSame('error', $error['state']);
        $this->assertSame('other', $error['reason'], 'unknown reasons are normalized');
        $this->assertSame('Quota exceeded', $error['msg']);
        $this->assertSame('no_answer', \LiveRestreamDestinationInfo::getLastResult(null)['reason']);
        $this->assertSame('live_disabled', \LiveRestreamDestinationInfo::getLastResult((object) ['error' => true, 'msg' => 'x'], 'live_disabled')['reason']);
    }

    public function testHintsCoverTheProblemsSeenInProduction(): void
    {
        $row = ['status' => 'a', 'stream_url' => 'youtube', 'parameters' => $this->youtubeParameters($this->channel())];
        $channel = \LiveRestreamDestinationInfo::getChannelFromParameters($row['parameters']);

        $ready = \LiveRestreamDestinationInfo::getHints($row, $channel, null, false);
        $this->assertSame('success', $ready[0]['level']);
        $this->assertStringContainsString('Ready', $ready[0]['text']);

        $duplicate = \LiveRestreamDestinationInfo::getHints($row, $channel, null, false, [66, 69]);
        $this->assertStringContainsString('#66, #69', $this->texts($duplicate));

        $disabled = \LiveRestreamDestinationInfo::getHints($row, $channel, ['state' => 'error', 'reason' => 'live_disabled', 'at' => 1000], false);
        $this->assertSame('danger', $disabled[0]['level']);
        $this->assertStringContainsString('cannot go live', $this->texts($disabled));
        $this->assertStringContainsString('cannot go live', $this->texts(\LiveRestreamDestinationInfo::getHints($row, $this->channel(['live_streaming' => 'disabled']), null, false)));

        $expired = \LiveRestreamDestinationInfo::getHints($row, $channel, ['state' => 'error', 'reason' => 'expired', 'at' => 1000], false);
        $this->assertStringContainsString('Connect the account again with the YouTube button', $this->texts($expired));
        $this->assertStringContainsString('Revalidate', $this->texts(\LiveRestreamDestinationInfo::getHints($row, $channel, null, true)));

        $other = \LiveRestreamDestinationInfo::getHints($row, $channel, ['state' => 'error', 'reason' => 'other', 'msg' => 'Quota exceeded', 'at' => 1000], false);
        $this->assertSame('warning', $other[0]['level']);
        $this->assertStringContainsString('Quota exceeded', $this->texts($other));

        $ok = \LiveRestreamDestinationInfo::getHints($row, $channel, ['state' => 'ok', 'live_url' => 'https://www.youtube.com/watch?v=SgKX4uKjEAk', 'at' => 1000], false);
        $this->assertStringContainsString('The last live connected here', $this->texts($ok));
        $this->assertStringContainsString('watch?v=SgKX4uKjEAk', \LiveRestreamDestinationInfo::getHintsHtml($ok));

        $inactive = \LiveRestreamDestinationInfo::getHints(['status' => 'i'] + $row, $channel, null, false);
        $this->assertStringContainsString('Inactive', $this->texts($inactive));
    }

    public function testChannelLookupAndManualDestinationsAreExplained(): void
    {
        $row = ['status' => 'a', 'stream_url' => 'youtube', 'parameters' => $this->youtubeParameters()];
        $this->assertStringContainsString('Checking which YouTube channel', $this->texts(\LiveRestreamDestinationInfo::getHints($row, null, null, false)));
        $this->assertStringContainsString('could not see which YouTube channel', $this->texts(\LiveRestreamDestinationInfo::getHints($row, null, null, false, [], true)));

        $manual = ['status' => 'a', 'stream_url' => 'rtmps://a.rtmps.youtube.com/live2', 'parameters' => ''];
        $this->assertStringContainsString('the stream key decides', $this->texts(\LiveRestreamDestinationInfo::getHints($manual, null, null, false)));
        $facebook = ['status' => 'a', 'stream_url' => 'rtmps://live-api-s.facebook.com:443/rtmp/', 'parameters' => ''];
        $this->assertStringContainsString('Ready', $this->texts(\LiveRestreamDestinationInfo::getHints($facebook, null, null, false)));
    }

    public function testHintsHtmlEscapesTextAndKeepsOnlySafeClasses(): void
    {
        $html = \LiveRestreamDestinationInfo::getHintsHtml([
            ['level' => 'danger" onclick="x', 'icon' => 'fa-x" onmouseover="y', 'text' => '<b>bad</b>'],
            ['level' => 'success', 'icon' => 'fa-check', 'text' => 'ok', 'live_url' => 'javascript:alert(1)'],
        ], '<button>Revalidate</button>');
        $this->assertStringNotContainsString('<b>bad</b>', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('onmouseover', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('<button>Revalidate</button>', $html, 'server-built revalidate button is kept');
    }

    public function testSameChannelDestinationsAreGrouped(): void
    {
        $a = ['id' => self::CHANNEL_ID];
        $b = ['id' => 'UCzzzzzzzzzzzzzzzzzzzzzz'];
        $this->assertSame([66 => [69, 72], 69 => [66, 72], 72 => [66, 69]],
            \LiveRestreamDestinationInfo::getSameChannelDestinations([43 => null, 66 => $a, 69 => $a, 72 => $a, 80 => $b]));
        $this->assertSame([], \LiveRestreamDestinationInfo::getSameChannelDestinations([1 => $a, 2 => $b]));
    }

    public function testDestinationRowsGetChannelAndHealthWithoutBreakingOldFields(): void
    {
        $rows = [
            ['id' => 66, 'status' => 'a', 'name' => 'youtube : ticavteam@gmail.com', 'stream_url' => 'youtube', 'parameters' => $this->youtubeParameters($this->channel()), 'revalidateButton' => '<i></i>', 'token_expired' => (object) []],
            ['id' => 72, 'status' => 'a', 'name' => 'youtube : ticavteam@gmail.com', 'stream_url' => 'youtube', 'parameters' => $this->youtubeParameters(), 'revalidateButton' => '<i></i>', 'token_expired' => (object) []],
        ];
        // destination 72 was connected before the channel was saved; its channel was read later
        $statusDir = ObjectYPT::$dir . 'LiveRestreamDestinationStatus' . DIRECTORY_SEPARATOR;
        mkdir($statusDir, 0777, true);
        file_put_contents($statusDir . '72.json', json_encode(['channel' => $this->channel(), 'last' => ['state' => 'error', 'reason' => 'live_disabled', 'at' => 1000]]));
        $this->assertSame('live_disabled', Live_restreams::getDestinationStatus(72)['last']['reason']);
        $this->assertSame([], Live_restreams::getDestinationStatus(0));
        $result = Live_restreams::addDestinationStatus($rows);
        $this->assertSame('TIC AV (@TICAV)', $result[0]['channel_label']);
        $this->assertSame('TIC AV (@TICAV)', $result[1]['channel_label']);
        $this->assertSame([72], $result[0]['same_channel_as']);
        $this->assertSame([66], $result[1]['same_channel_as']);
        $this->assertStringContainsString('cannot go live', $result[1]['status_html']);
        $this->assertFalse($result[1]['needs_channel_lookup']);
        $this->assertSame('<i></i>', $result[0]['revalidateButton'], 'old field kept for older pages');
    }

    public function testDeleteStopsTheRelayFirstAndKeyRequestsRecordTheOutcome(): void
    {
        $root = dirname(__DIR__, 2) . '/plugin/Live/';
        $delete = file_get_contents($root . 'view/Live_restreams/delete.json.php');
        $this->assertLessThan(strpos($delete, '$row->delete()'), strpos($delete, 'stopRelaysOfDestination('), 'relay is stopped before the row is deleted');
        $this->assertStringContainsString("\$relays['failed'] > 0", $delete, 'a relay that could not be stopped blocks the delete');

        $getLiveKey = file_get_contents($root . 'view/Live_restreams/getLiveKey.json.php');
        $this->assertStringContainsString('getLiveStreamingDisabledInfo(', $getLiveKey);
        $this->assertMatchesRegularExpression('/if \(\$liveTransmitionHistory_id > 0\) \{\s+\$reason/', $getLiveKey, 'only real lives update the last result');

        $panel = file_get_contents($root . 'view/Live_restreams/livePanelForm.php');
        $this->assertStringContainsString('"data": "status_html"', $panel);
        $this->assertStringContainsString('channelInfo.json.php', $panel);
        $this->assertStringContainsString('data: {id: data.id}', $panel, 'delete posts only the id');
    }
}
