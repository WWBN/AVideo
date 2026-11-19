<?php

namespace Tests\Security\SortKeyExpressionInjection;

use PHPUnit\Framework\TestCase;

// GHSA-p26w-9mq2-6cf7: the ORDER BY key came from request data (sort[] or DataTables columns[]/order[])
// and only had characters stripped, so a key such as "v.id AND <condition>" became an SQL expression.
// The real BootGrid class and ObjectYPT::getSqlOrderByFromPost() are loaded into this namespace with
// local stubs, so no global class/function used by other fixtures is defined here.

class AVideoLog
{
    public static $SECURITY = 'security';
}

function _error_log($message, $level = '')
{
    return true;
}

function xss_esc($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function getRowCount()
{
    return 0;
}

function getCurrentPage()
{
    return 1;
}

$sortKeyRepoRoot = dirname(__DIR__, 2);
$sortKeyBootGridSource = file_get_contents($sortKeyRepoRoot . '/objects/bootGrid.php');
eval('namespace ' . __NAMESPACE__ . '; ' . preg_replace('/^<\?php/', '', $sortKeyBootGridSource));

$sortKeyObjectSource = file_get_contents($sortKeyRepoRoot . '/objects/Object.php');
$sortKeyStart = strpos($sortKeyObjectSource, 'public static function getSqlOrderByFromPost(');
$sortKeyEnd = strpos($sortKeyObjectSource, 'public static function getSqlLimit(', $sortKeyStart);
eval('namespace ' . __NAMESPACE__ . '; class ObjectSortHarness { ' . substr($sortKeyObjectSource, $sortKeyStart, $sortKeyEnd - $sortKeyStart) . ' }');
unset($sortKeyRepoRoot, $sortKeyBootGridSource, $sortKeyObjectSource, $sortKeyStart, $sortKeyEnd);

class SortKeyExpressionInjectionTest extends TestCase
{
    private $savedGet;
    private $savedPost;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedGet = $_GET;
        $this->savedPost = $_POST;
        $_GET = [];
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_GET = $this->savedGet;
        $_POST = $this->savedPost;
        parent::tearDown();
    }

    public function expressionKeysProvider()
    {
        return [
            'condition on joined users row' => ['v.id AND u.user LIKE BINARY 0x61646d696e'],
            'range comparison on reset token' => ['v.id AND BINARY u.recoverPass BETWEEN 0x4c30 AND 0x7f'],
            'unprefixed expression' => ['id AND u.isAdmin'],
            'three part name' => ['v.u.password'],
            'only spaces' => ['   '],
        ];
    }

    /**
     * @dataProvider expressionKeysProvider
     */
    public function testDataTablesSortExpressionNeverReachesOrderBy($key)
    {
        // view/videosList.php: unknown sortBy leaves $_POST['sort'] empty, DataTables params fill it
        $_POST['sort'] = [];
        $_GET['columns'][0]['data'] = $key;
        $_GET['order'][0]['column'] = '0';
        $_GET['order'][0]['dir'] = 'asc';

        $sql = BootGrid::getSqlFromPost([], 'v.', '', true);

        $this->assertStringNotContainsString('ORDER BY', $sql);
    }

    /**
     * @dataProvider expressionKeysProvider
     */
    public function testPostSortExpressionNeverReachesOrderByWithoutPrefix($key)
    {
        // Video::getSQLSort() passes an empty prefix when "likes" is sorted
        $_POST['sort'] = ['likes' => 'DESC', $key => 'ASC'];

        $sql = BootGrid::getSqlFromPost([], '', '', true);

        $this->assertSame(' ORDER BY  likes DESC ', $sql);
    }

    public function testLegitimateVideoSortKeysKeepTheirOrderBy()
    {
        // default grid sort built by Video::getSQLSort()
        $_POST['sort'] = [
            'v.`order`' => 'IS NOT NULL DESC',
            '`order`' => 'ASC',
            'v.created' => 'DESC',
            'v.id' => 'DESC',
            'title' => 'asc',
        ];

        $sql = BootGrid::getSqlFromPost([], 'v.', '', true);

        $this->assertSame(' ORDER BY  v.order IS NOT NULL DESC , v.`order` ASC , v.created DESC , v.id DESC , v.title ASC ', $sql);
    }

    public function testAlternativeOrderIsUsedWhenEveryKeyIsInvalid()
    {
        $_POST['sort'] = ['v.id AND u.isAdmin' => 'ASC'];

        $sql = BootGrid::getSqlFromPost([], 'v.', ' ORDER BY `order`, name ASC ', true);

        $this->assertSame(' ORDER BY `order`, name ASC ', $sql);
    }

    /**
     * @dataProvider expressionKeysProvider
     */
    public function testObjectYPTSortExpressionNeverReachesOrderBy($key)
    {
        $_GET['sort'] = [$key => 'ASC'];

        $sql = ObjectSortHarness::getSqlOrderByFromPost();

        $this->assertSame('', $sql);
    }

    public function testObjectYPTLegitimateSortKeysKeepTheirOrderBy()
    {
        $_POST['sort'] = ['created' => 'DESC', 'p.`order`' => 'ASC'];
        $_GET['sort'] = ['v.id AND u.isAdmin' => 'ASC'];

        $sql = ObjectSortHarness::getSqlOrderByFromPost();

        $this->assertSame(' ORDER BY  `created` DESC , p.order ASC ', $sql);
    }

    public function testObjectYPTDataTablesColumnSortStillWorks()
    {
        $_GET['columns'][2]['data'] = 'name';
        $_GET['order'][0]['column'] = '2';
        $_GET['order'][0]['dir'] = 'desc';

        $sql = ObjectSortHarness::getSqlOrderByFromPost();

        $this->assertSame(' ORDER BY  `name` DESC ', $sql);
    }
}
