<?php
namespace Tests\Security\IdParameterFilter;

use PHPUnit\Framework\TestCase;

/**
 * objects/security.php forces every request parameter ending in "_id" to integers.
 * GHSA-4cq2-wh78-c3wc: a value sent as a text list ("0,<markup>") kept its raw text because
 * the inner loop reused $key/$value, so the integers were written to another key.
 */
class IdParameterFilterTest extends TestCase
{
    private function filter(array $input)
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/security.php');
        $start = strpos($source, '    // all variables with _id at the end');
        $end = strpos($source, '    foreach ($securityFilterInt', $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $scanThis = $input;
        eval(substr($source, $start, $end - $start));
        return $scanThis;
    }

    public function testTextListWithMarkupBecomesIntegersUnderTheSameKey()
    {
        $result = $this->filter(['campaign_has_videos_id' => '0,"]]><b>x</b><![CDATA["']);
        $this->assertSame(['campaign_has_videos_id' => '0,0'], $result);
    }

    public function testJSONListWithMarkupBecomesIntegers()
    {
        $result = $this->filter(['plans_id' => '[1,"<b>x</b>"]']);
        $this->assertSame(['plans_id' => '[1,0]'], $result);
    }

    public function testValidListsKeepTheirFormat()
    {
        $this->assertSame(['categories_id' => '1,2'], $this->filter(['categories_id' => '1,2']));
        $this->assertSame(['categories_id' => '[1,2]'], $this->filter(['categories_id' => '[1,2]']));
        $this->assertSame(['categories_id' => '[]'], $this->filter(['categories_id' => '[]']));
    }

    public function testPHPArraysAreKeptAndNoOtherKeyIsWritten()
    {
        // The Encoder posts usergroups_id[0]=...; callers such as aVideoEncoder.json.php expect an array.
        $input = ['usergroups_id' => ['5', '7'], 'other' => 'x'];
        $this->assertSame($input, $this->filter($input));
    }

    public function testScalarValuesAreStillIntegers()
    {
        $result = $this->filter(['videos_id' => '12', 'users_id' => 'abc', 'live_id' => '', 'name' => 'abc']);
        $this->assertSame(['videos_id' => 12, 'users_id' => 0, 'live_id' => 0, 'name' => 'abc'], $result);
    }
}
