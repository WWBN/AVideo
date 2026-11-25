<?php

namespace Tests\Security\SSRFSpecialPurposeRangesFixtures;

// Keep DNS-path coverage deterministic and isolated from other tests' global stubs.
function gethostbyname($host)
{
    $addresses = [
        'cgnat.example.test' => '100.100.100.200',
        'azure.example.test' => '168.63.129.16',
        'public.example.test' => '8.8.8.8',
    ];
    return $addresses[$host] ?? \gethostbyname($host);
}

// Loads the production SSRF helpers into this namespace: other tests define global isSSRFSafeURL() stubs.
$ssrfSource = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php'));
foreach (['isPrivateOrLoopbackIP', 'isLoopbackIP', 'isSSRFBlockedSpecialPurposeIP', 'ssrfExtractEmbeddedIPv4', 'isSSRFSafeURL'] as $ssrfFunctionName) {
    if (!function_exists(__NAMESPACE__ . '\\' . $ssrfFunctionName)) {
        if (!preg_match('/\nfunction ' . $ssrfFunctionName . '\(.*?\n}\n/s', $ssrfSource, $ssrfMatch)) {
            // A missing helper is reported by the assertions below, not as a load error.
            continue;
        }
        eval('namespace ' . __NAMESPACE__ . ';' . $ssrfMatch[0]);
    }
}
unset($ssrfSource, $ssrfFunctionName, $ssrfMatch);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use function Tests\Security\SSRFSpecialPurposeRangesFixtures\isPrivateOrLoopbackIP;
use function Tests\Security\SSRFSpecialPurposeRangesFixtures\isSSRFSafeURL;

/**
 * GHSA-42x7-8gfw-6347: isSSRFSafeURL() allowed CGNAT (Alibaba Cloud metadata 100.100.100.200),
 * the Azure platform channel, TEST-NET/benchmarking ranges and deprecated ::a.b.c.d addresses,
 * reachable without login through plugin/LiveLinks/proxy.php, which echoes the response.
 */
class SSRFSpecialPurposeRangesTest extends TestCase
{
    private $previousGlobal;

    protected function setUp(): void
    {
        global $global;
        $this->previousGlobal = $global ?? null;
        $global['webSiteRootURL'] = 'https://avideo.example/';
    }

    protected function tearDown(): void
    {
        global $global;
        $global = $this->previousGlobal;
    }

    public static function blockedHostsProvider(): array
    {
        return [
            'Alibaba Cloud metadata' => ['100.100.100.200'],
            'CGNAT first' => ['100.64.0.1'],
            'CGNAT last' => ['100.127.255.254'],
            'Azure platform channel' => ['168.63.129.16'],
            'IETF protocol assignments' => ['192.0.0.192'],
            'TEST-NET-1' => ['192.0.2.1'],
            'TEST-NET-2' => ['198.51.100.7'],
            'TEST-NET-3' => ['203.0.113.9'],
            'benchmarking first' => ['198.18.0.1'],
            'benchmarking last' => ['198.19.255.255'],
            'IPv6 documentation' => ['[2001:db8::1]'],
            'IPv4-compatible loopback' => ['[::7f00:1]'],
            'IPv4-compatible private' => ['[::a00:1]'],
            'IPv4-compatible link-local metadata' => ['[::a9fe:a9fe]'],
            'IPv4-mapped CGNAT' => ['[::ffff:100.100.100.200]'],
            'NAT64 Azure platform channel' => ['[64:ff9b::a83f:8110]'],
            '6to4 compressed CGNAT' => ['[2002:6440::1]'],
            '6to4 expanded CGNAT' => ['[2002:6440:0:0:0:0:0:1]'],
            '6to4 compressed benchmarking' => ['[2002:c612::1]'],
            'Teredo CGNAT' => ['[2001:0:808:808:0:ffff:9b9b:9b37]'],
            'Teredo compressed CGNAT' => ['[2001:0:808:808::9b9b:9b37]'],
            'Teredo Azure platform channel' => ['[2001:0:808:808:0:ffff:57c0:7eef]'],
            'DNS to CGNAT' => ['cgnat.example.test'],
            'DNS to Azure platform channel' => ['azure.example.test'],
            'decimal IPv4 CGNAT' => ['1684301000'],
            'hexadecimal IPv4 CGNAT' => ['0x646464c8'],
            'octal IPv4 CGNAT' => ['0144.0144.0144.0310'],
            // Previously fixed classes must stay blocked.
            'loopback' => ['127.0.0.1'],
            'RFC 1918' => ['10.0.0.1'],
            'link-local metadata' => ['169.254.169.254'],
            'IPv4-mapped loopback' => ['[::ffff:127.0.0.1]'],
            'IPv6 loopback' => ['[::1]'],
            'IPv6 unspecified' => ['[::]'],
        ];
    }

    /**
     * @dataProvider blockedHostsProvider
     */
    public function testSpecialPurposeAddressesAreBlocked(string $host): void
    {
        $resolvedIP = 'unchanged';
        $this->assertFalse(isSSRFSafeURL("http://{$host}/latest/meta-data/", $resolvedIP));
        $this->assertSame('unchanged', $resolvedIP);
    }

    public static function publicHostsProvider(): array
    {
        return [
            'public IPv4' => ['8.8.8.8'],
            'below CGNAT' => ['100.63.255.255'],
            'above CGNAT' => ['100.128.0.1'],
            'next to Azure platform channel' => ['168.63.129.17'],
            'below benchmarking' => ['198.17.255.255'],
            'above benchmarking' => ['198.20.0.1'],
            'next to IETF protocol assignments' => ['192.0.1.1'],
            'public IPv6' => ['[2606:4700:4700::1111]'],
            'next to IPv6 documentation' => ['[2001:db9::1]'],
        ];
    }

    /**
     * @dataProvider publicHostsProvider
     */
    public function testPublicAddressesNextToTheRangesStillWork(string $host): void
    {
        $resolvedIP = null;
        $this->assertTrue(isSSRFSafeURL("http://{$host}/live.m3u8", $resolvedIP));
        $this->assertSame(trim($host, '[]'), $resolvedIP);
    }

    public function testSameOriginShortcutIsUnchanged(): void
    {
        $this->assertTrue(isSSRFSafeURL('https://avideo.example/videos/file.mp4'));
    }

    public function testCallerTrustHelperIsUnchanged(): void
    {
        // Live callbacks trust callers by isPrivateOrLoopbackIP(); the new ranges must not become trusted.
        $this->assertFalse(isPrivateOrLoopbackIP('100.100.100.200'));
        $this->assertFalse(isPrivateOrLoopbackIP('203.0.113.9'));
        $this->assertTrue(isPrivateOrLoopbackIP('10.0.0.1'));
    }

    public static function transitionPublicHostsProvider(): array
    {
        return [
            'mapped' => ['::ffff:808:808', '8.8.8.8'],
            'NAT64' => ['64:ff9b::808:808', '8.8.8.8'],
            '6to4' => ['2002:808:808::1', '8.8.8.8'],
            'compressed 6to4' => ['2002:808::1', '2002:808::1'],
            'Teredo' => ['2001:0:808:808:0:ffff:f7f7:f7f7', '2001:0:808:808:0:ffff:f7f7:f7f7'],
            'compressed Teredo' => ['2001::f7f7:f7f7', '2001::f7f7:f7f7'],
        ];
    }

    /**
     * @dataProvider transitionPublicHostsProvider
     */
    public function testPublicTransitionAddressesKeepTheirResolvedIP(string $ip, string $expectedIP): void
    {
        $resolvedIP = null;
        $this->assertTrue(isSSRFSafeURL("http://[{$ip}]/live.m3u8", $resolvedIP));
        $this->assertSame($expectedIP, $resolvedIP);
    }

    public function testPublicDNSStillPopulatesResolvedIP(): void
    {
        $resolvedIP = null;
        $this->assertTrue(isSSRFSafeURL('https://public.example.test/live.m3u8', $resolvedIP));
        $this->assertSame('8.8.8.8', $resolvedIP);
    }

    public function testSameOriginOnBlockedIPKeepsTheShortcutAndOutParameter(): void
    {
        global $global;
        foreach (['100.100.100.200', '[2001:db8::1]', '[2002:6440::1]'] as $host) {
            $global['webSiteRootURL'] = "https://{$host}:8443/";
            $resolvedIP = 'unchanged';
            $this->assertTrue(isSSRFSafeURL("https://{$host}:8443/videos/file.mp4", $resolvedIP));
            $this->assertSame('unchanged', $resolvedIP);
            $this->assertFalse(isSSRFSafeURL("https://{$host}:8444/videos/file.mp4", $resolvedIP));
            $this->assertSame('unchanged', $resolvedIP);
        }
    }
}
