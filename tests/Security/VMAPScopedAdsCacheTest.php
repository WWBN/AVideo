<?php
namespace Tests\Security\VMAPScopedAdsCache;

use PHPUnit\Framework\TestCase;

class VmapAdsFixture { public static $rental = false, $limited = '', $ads = true, $decisions = 0; }
class AVideoPlugin
{
    public static function isEnabledByName($name) { return $name === 'Cache'; }
    public static function getObjectDataIfEnabled($name) {
        return (object) ['allowRentalOfGroupRestrictedVideos' => VmapAdsFixture::$rental, 'plansThatRemoveAdsOnlyFromTheirVideos' => VmapAdsFixture::$limited];
    }
    public static function showAds($id) { VmapAdsFixture::$decisions++; return VmapAdsFixture::$ads; }
}
class Cache
{
    public static $key;
    public static function getCache($key, $ttl, $strict) { self::$key = $key; return null; }
}

class VMAPScopedAdsCacheTest extends TestCase
{
    protected function setUp(): void
    {
        if (!is_file(dirname(__DIR__, 2) . '/plugin/GoogleAds_IMA/VMAP.php')) {
            $this->markTestSkipped('GoogleAds_IMA is an independently deployed plugin');
        }
    }

    private function templateKey($ads): string
    {
        VmapAdsFixture::$ads = $ads;
        $savedRequest = $_REQUEST;
        $_REQUEST = ['videos_id' => 6106, 'optionalAdTagUrl' => 4];
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/GoogleAds_IMA/VMAP.php');
        $start = strpos($source, '$_vmapBuildingTemplate = false;');
        $end = strpos($source, '$jsonobj = new stdClass();', $start);
        try {
            eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));
            return Cache::$key;
        } finally {
            $_REQUEST = $savedRequest;
        }
    }

    public function testOptInViewerAdDecisionsCannotShareOneTemplate(): void
    {
        VmapAdsFixture::$rental = true;
        VmapAdsFixture::$limited = '';
        $this->assertNotSame($this->templateKey(true), $this->templateKey(false), 'A visitor and renter need different ad templates');
        VmapAdsFixture::$rental = false;
        VmapAdsFixture::$limited = '13';
        $this->assertNotSame($this->templateKey(true), $this->templateKey(false), 'Scoped members must not inherit visitor ads');
    }

    public function testOptionsOffPreserveTheExistingTemplateKey(): void
    {
        VmapAdsFixture::$rental = false;
        VmapAdsFixture::$limited = '';
        VmapAdsFixture::$decisions = 0;
        $this->assertSame('vmapTmpl_6106_4', $this->templateKey(true));
        $this->assertSame(0, VmapAdsFixture::$decisions);
    }
}
