<?php

namespace Tests\Fixtures\CatalogVisibility;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/fixtures/catalog-visibility.php';

class CategoryListingVisibilityTest extends TestCase
{
    private $savedGlobals;

    protected function setUp(): void
    {
        foreach (['global', 'config', '_GET', '_POST', '_REQUEST'] as $name) {
            $this->savedGlobals[$name] = $GLOBALS[$name] ?? null;
        }
        Fixture::reset();
        $GLOBALS['global'] = [];
        $GLOBALS['config'] = new Config();
        $_GET = $_POST = [];
        $_REQUEST = ['rowCount' => 1000, 'current' => 1];
        foreach (range(1, 9) as $id) {
            Fixture::category($id, $id === 6 ? 5 : ($id === 7 ? 6 : 0));
        }
        Fixture::video(1, 9);
        Fixture::video(2);
        Fixture::video(3);
        Fixture::video(3, 9);
        Fixture::video(7, 9);
        Fixture::video(8);
        Fixture::video(9);
    }

    protected function tearDown(): void
    {
        foreach ($this->savedGlobals as $name => $value) {
            if ($value === null) {
                unset($GLOBALS[$name]);
            } else {
                $GLOBALS[$name] = $value;
            }
        }
    }

    public function testRestrictedOnlyCategoriesAndDeepAncestorsFollowSetting(): void
    {
        $this->assertSame([1, 2, 3, 5, 6, 7, 8, 9], array_column(Category::getAllCategories(false, true), 'id'));
        Fixture::$layouts['Gallery'] = true;
        $this->assertSame([2, 3, 8, 9], array_column(Category::getAllCategories(false, true), 'id'));
        $this->assertSame([2, 3, 4, 8, 9], array_column(Category::getAllCategories(), 'id'));
    }

    public function testMixedCategoryCountsAndChildCountsUseSamePolicy(): void
    {
        $rows = array_column(Category::getAllCategories(false, true), null, 'id');
        $this->assertSame(2, $rows[3]['total']);
        $this->assertSame(1, Category::getChildCategories(5)[0]['total']);
        $this->assertSame(1, Category::getTotalFromChildCategory(5, false, true)['total']);
        Fixture::$layouts['Gallery'] = true;
        $rows = array_column(Category::getAllCategories(false, true), null, 'id');
        $this->assertSame(1, $rows[3]['total']);
        $this->assertSame([], Category::getChildCategories(5));
        $this->assertSame(0, Category::getTotalFromChildCategory(5)['total']);
    }

    public function testPaginationAndTotalsAreAppliedAfterVisibility(): void
    {
        Fixture::$layouts['Gallery'] = true;
        $_REQUEST['rowCount'] = 2;
        $this->assertSame([2, 3], array_column(Category::getAllCategories(false, true), 'id'));
        $this->assertSame(4, Category::getTotalCategories(false, true));
        $_REQUEST['current'] = 2;
        $this->assertSame([8, 9], array_column(Category::getAllCategories(false, true), 'id'));
    }

    public function testCountingDescendantsDoesNotDependOnPagination(): void
    {
        foreach ([10, 11, 12] as $id) {
            Fixture::category($id, 5);
            Fixture::video($id);
        }
        $_REQUEST = ['rowCount' => 1, 'current' => 2];
        $this->assertSame(4, Category::getTotalFromCategory(5, false, true)['total']);
        $this->assertSame(4, Category::getTotalFromChildCategory(5, false, true)['total']);
    }

    public function testGroupChangesDoNotReuseGuestListing(): void
    {
        Fixture::$layouts['Gallery'] = true;
        $this->assertCount(4, Category::getAllCategories(false, true));
        Fixture::$user = 21;
        Fixture::$groups = [9];
        $this->assertCount(8, Category::getAllCategories(false, true));
    }

    public function testUploadSelectorsKeepRestrictedAndEmptyCategories(): void
    {
        Fixture::$layouts['Gallery'] = true;
        $this->assertCount(9, Category::getAllCategories(true));
        $this->assertSame(9, Category::getTotalCategories(true));
    }

    public function testForKidsAndInactiveVideosDoNotMakeCategoryVisible(): void
    {
        Fixture::category(10);
        Fixture::video(10, 0, 'i');
        Fixture::category(11);
        Fixture::video(11, 0, 'a', false);
        $this->assertContains(11, array_column(Category::getAllCategories(false, true), 'id'));
        Fixture::$kids = true;
        $this->assertNotContains(10, array_column(Category::getAllCategories(false, true), 'id'));
        $this->assertNotContains(11, array_column(Category::getAllCategories(false, true), 'id'));
    }

    public function testActiveFlixSettingWinsWhenGalleryAlsoEnabled(): void
    {
        Fixture::$layouts = ['Gallery' => false, 'YouPHPFlix2' => true];
        $this->assertTrue(isToHidePrivateVideos());
        $hiddenKey = getVideoListVisibilityCacheKey();
        Fixture::$layouts['YouPHPFlix2'] = false;
        $this->assertFalse(isToHidePrivateVideos());
        $this->assertNotSame($hiddenKey, getVideoListVisibilityCacheKey());
    }

    public function testSearchDoesNotHideContentInDifferentlyNamedDescendants(): void
    {
        $_REQUEST['search'] = 'Category 5';
        $this->assertSame([5], array_column(Category::getAllCategories(false, true), 'id'));
        $this->assertSame(1, Category::getTotalCategories(false, true));
    }

    public function testCategoryImageUsesAllowedVideoEvenInDeepDescendant(): void
    {
        $this->assertSame(7, Category::getLatestVideoFromCategory(5, false, true)['categories_id']);
        Fixture::video(7);
        Fixture::video(7, 9);
        Fixture::$layouts['Gallery'] = true;
        $latest = Category::getLatestVideoFromCategory(5);
        $this->assertSame(0, $latest['group']);
    }

    public function testVisibleCountsQualifyGroupFilterWithVideoAlias(): void
    {
        Fixture::$layouts['Gallery'] = true;
        Category::getAllCategories(false, true);
        Category::getLatestVideoFromCategory(5);
        $visibilityQueries = array_filter(Fixture::$queries, function ($sql) {
            return strpos($sql, 'visible_for_') !== false;
        });
        $this->assertNotEmpty($visibilityQueries);
        foreach ($visibilityQueries as $sql) {
            $this->assertStringContainsString(' AND v.visible_for_', $sql);
        }
    }

    public function testAnonymousCategoriesEndpointStaysAnUploadSelector(): void
    {
        // The Encoder reloads this list cross-origin, so the request carries no Streamer session.
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/categories.json.php');
        $this->assertSame(1, preg_match('/\$filterCanAddVideoOnly = ([^;]+);/', $source, $match));
        $_GET = [];
        $this->assertTrue(eval('namespace ' . __NAMESPACE__ . '; return ' . $match[1] . ';'));
        $_GET = ['user' => 'mobile'];
        $this->assertFalse(eval('namespace ' . __NAMESPACE__ . '; return ' . $match[1] . ';'));
        Fixture::$layouts['Gallery'] = true;
        $this->assertCount(9, Category::getAllCategories(true));
    }

    public function testAPICacheSeparatesPolicyGroupsSlugAndPassword(): void
    {
        $cache = new VideosListCacheHandler();
        $method = new \ReflectionMethod($cache, 'getCacheSufix');
        $method->setAccessible(true);
        $keys = [$method->invoke($cache)];
        Fixture::$layouts['Gallery'] = true;
        $keys[] = $method->invoke($cache);
        Fixture::$groups = [9];
        $keys[] = $method->invoke($cache);
        $_REQUEST['clean_title'] = 'another-video';
        $keys[] = $method->invoke($cache);
        $_REQUEST['video_password'] = 'fixture-password';
        $keys[] = $method->invoke($cache);
        $this->assertCount(5, array_unique($keys));
    }
}
