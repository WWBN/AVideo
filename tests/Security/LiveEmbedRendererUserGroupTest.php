<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\LiveEmbedFixtures\State;
use function Tests\Security\LiveEmbedFixtures\renderPreamble;
use function Tests\Security\LiveEmbedFixtures\selectLatestLive;

require_once dirname(__DIR__) . '/fixtures/live-embed-user-groups.php';

/**
 * GHSA-xp3r-273q-6hmv: plugin/Live/index.php picks one of three renderers; each must refuse a viewer outside the
 * live's user groups (userCanSeeTransmition()) before it prints the HLS URL (Live::getM3U8File()).
 */
class LiveEmbedRendererUserGroupTest extends TestCase
{
    protected function setUp(): void
    {
        State::$row = ['id' => 42, 'key' => 'ordinary-key', 'users_id' => 5];
        State::$rows = [];
        State::$groups = [];
        State::$userGroups = [];
        State::$admin = State::$logged = State::$hideUserGroups = false;
        State::$playlists = State::$ppv = false;
        State::$ppvDenied = State::$events = [];
    }

    private static function source($relativePath)
    {
        return file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath);
    }

    public function rendererProvider()
    {
        return [
            'embedv2' => ['plugin/Live/view/videoEmbededV2.php'],
            'embed' => ['plugin/Live/view/videoEmbeded.php'],
            'live page' => ['plugin/Live/view/modeYoutubeLive.php'],
        ];
    }

    public function testIndexIncludesOnlyTheKnownRenderers()
    {
        $index = self::source('plugin/Live/index.php');
        preg_match_all("/include \\\$global\['systemRootPath'\] \. '(plugin\/Live\/view\/[^']+)'/", $index, $matches);
        $this->assertEqualsCanonicalizing(array_column($this->rendererProvider(), 0), $matches[1]);
    }

    /**
     * @dataProvider rendererProvider
     */
    public function testRendererChecksUserGroupsBeforeTheStreamUrl($relativePath)
    {
        $source = self::source($relativePath);
        $check = strpos($source, '$lt->userCanSeeTransmition()');
        $this->assertNotFalse($check, "{$relativePath} must call userCanSeeTransmition()");
        $this->assertStringContainsString('$lt = new Live_schedule($livet[\'id\']);', $source, 'scheduled lives use their own groups');

        $output = strpos($source, '?>');
        $this->assertNotFalse($output);
        $this->assertLessThan($output, $check, 'the check must run before any output');
        $this->assertStringNotContainsString('Live::getM3U8File', substr($source, 0, $check));
    }

    public function viewerProvider()
    {
        $cases = [
            'anonymous restricted' => [false, false, false, false, [7], [], true],
            'non-member owner' => [false, false, true, false, [7], [8], true],
            'member' => [false, false, true, false, [7], [7], false],
            'admin' => [false, true, true, false, [7], [], false],
            'unrestricted anonymous' => [false, false, false, false, [], [], false],
            'hidden groups' => [false, false, false, true, [7], [], false],
            'scheduled anonymous restricted' => [true, false, false, false, [7], [], true],
            'scheduled non-member' => [true, false, true, false, [7], [8], true],
            'scheduled member' => [true, false, true, false, [7], [7], false],
            'scheduled admin' => [true, true, true, false, [7], [], false],
            'scheduled unrestricted' => [true, false, false, false, [], [], false],
            'scheduled hidden groups still restricted' => [true, false, false, true, [7], [], true],
        ];
        $rows = [];
        foreach (['videoEmbededV2.php', 'videoEmbeded.php'] as $renderer) {
            foreach ($cases as $name => $case) {
                $rows[$renderer . ': ' . $name] = array_merge([$renderer], $case);
            }
        }
        return $rows;
    }

    /** @dataProvider viewerProvider */
    public function testEmbedExecutesTheGroupDecision($renderer, $scheduled, $admin, $logged, $hidden, $groups, $memberships, $denied)
    {
        State::$admin = $admin;
        State::$logged = $logged;
        State::$hideUserGroups = $hidden;
        State::$userGroups = array_map(static function ($id) { return ['id' => $id]; }, $memberships);
        State::$groups[42] = $groups;
        if ($scheduled) {
            State::$row['live_schedule'] = 42;
        }
        $response = renderPreamble('plugin/Live/view/' . $renderer, $scheduled ? ['live_schedule' => 42] : []);
        $this->assertSame($denied, $response['denied']);
        $this->assertContains([$scheduled ? 'schedule' : 'transmission', 42], State::$events);
        if ($denied) {
            $this->assertNotContains('embed-security', State::$events);
            $this->assertNotContains('ppv', State::$events);
        } else {
            $this->assertContains('embed-security', State::$events);
            $this->assertContains('ppv', State::$events);
        }
    }

    public function testV2StillAcceptsBothRootMaskFormatsAndCombinedEmbedFlags()
    {
        foreach (['https://mask.invalid/', base64_encode('https://mask.invalid/')] as $root) {
            State::$events = [];
            $response = renderPreamble('plugin/Live/view/videoEmbededV2.php', ['webSiteRootURL' => $root, 'embed' => 1, 'embedv2' => 1]);
            $this->assertFalse($response['denied']);
            $this->assertSame('https://mask.invalid/', $response['root']);
            $this->assertSame(['set-key', ['transmission', 42], 'embed-security', 'ppv'], State::$events);
        }
    }

    public function latestProvider()
    {
        return ['normal' => [false, false], 'playlist preference' => [true, false],
            'scheduled' => [false, true], 'scheduled playlist preference' => [true, true]];
    }

    /** @dataProvider latestProvider */
    public function testLatestLiveSkipsRestrictedStreamsAndKeepsSearching($playlists, $scheduled)
    {
        State::$playlists = $playlists;
        $restricted = ['id' => 42, 'key' => 'restricted-key', 'users_id' => 5, 'live_schedule' => $scheduled ? 42 : 0];
        $allowed = ['id' => 43, 'key' => 'allowed-key', 'users_id' => 6];
        State::$rows = ['restricted-key' => $restricted, 'allowed-key' => $allowed];
        State::$groups[42] = [7];
        $this->assertSame($allowed, selectLatestLive());
        State::$rows = ['restricted-key' => $restricted];
        $this->assertNull(selectLatestLive(), 'no permitted live must leave the existing fallback available');
        State::$logged = true;
        State::$userGroups = [['id' => 7]];
        $this->assertSame($restricted, selectLatestLive(), 'members retain access');
        State::$ppv = true;
        State::$ppvDenied = [5];
        $this->assertNull(selectLatestLive(), 'group membership must not bypass PPV');
    }
}
