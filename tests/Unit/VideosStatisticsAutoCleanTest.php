<?php

namespace Tests\Unit\VideosStatisticsAutoClean;

use PHPUnit\Framework\TestCase;

// Load the real method in isolation: the plugin file needs the application bootstrap and the
// live database. Only AVideoPlugin and sqlDAL are replaced below.
$source = file_get_contents(dirname(__DIR__, 2) . '/plugin/VideosStatistics/VideosStatistics.php');
$start = strpos($source, 'static function autoCleanStatisticsTable()');
$end = strpos($source, 'public static function profileTabName', $start);
eval('namespace ' . __NAMESPACE__ . '; class VideosStatistics { ' . substr($source, $start, $end - $start) . ' }');

class AVideoPlugin
{
    public static $dataObject;

    public static function getDataObject($name)
    {
        return self::$dataObject;
    }
}

class sqlDAL
{
    public static $queries = [];

    public static function writeSql($sql)
    {
        self::$queries[] = $sql;
        return true;
    }
}

class VideosStatisticsAutoCleanTest extends TestCase
{
    protected function setUp(): void
    {
        sqlDAL::$queries = [];
    }

    private function setOption($value)
    {
        $obj = new \stdClass();
        $obj->autoCleanStatisticsTable = new \stdClass();
        $obj->autoCleanStatisticsTable->type = [0 => 'Do not delete', 7 => 'Delete records older than 1 week'];
        $obj->autoCleanStatisticsTable->value = $value;
        AVideoPlugin::$dataObject = $obj;
    }

    public function doNotDeleteValues()
    {
        return [[0], ['0'], [''], [null], [-7]];
    }

    /**
     * @dataProvider doNotDeleteValues
     */
    public function testDoNotDeleteKeepsEveryRecord($value)
    {
        $this->setOption($value);

        $this->assertFalse(VideosStatistics::autoCleanStatisticsTable());
        $this->assertSame([], sqlDAL::$queries);
    }

    public function testMissingOptionDeletesNothing()
    {
        AVideoPlugin::$dataObject = new \stdClass();

        $this->assertFalse(VideosStatistics::autoCleanStatisticsTable());
        $this->assertSame([], sqlDAL::$queries);
    }

    public function testDeletesOnlyRecordsOlderThanTheChosenDays()
    {
        $this->setOption('60');

        $this->assertTrue(VideosStatistics::autoCleanStatisticsTable());
        $this->assertCount(1, sqlDAL::$queries);
        $this->assertMatchesRegularExpression('/DELETE FROM videos_statistics\s+WHERE created < NOW\(\) - INTERVAL 60 DAY;/', sqlDAL::$queries[0]);
    }
}
