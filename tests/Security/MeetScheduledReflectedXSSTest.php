<?php

namespace Tests\MeetScheduledReflectedXSS;

use PHPUnit\Framework\TestCase;

/**
 * GHSA-mcjg-w47c-498g: plugin/Meet/meet_scheduled.php is reachable without login and prints
 * meet_scheduled unescaped in element IDs, jQuery selectors and a JS variable name. Only the values
 * the Meet pages send (today/upcoming/past) are accepted; anything else becomes '' (no time filter,
 * the same result a missing parameter always had).
 */
class MeetScheduledReflectedXSSTest extends TestCase
{
    private static $block;
    private $savedRequest;

    public static function setUpBeforeClass(): void
    {
        $functions = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
        preg_match('/function cleanString\(.*?\n\}/s', $functions, $matches);
        eval('namespace ' . __NAMESPACE__ . '; ' . $matches[0]);

        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Meet/meet_scheduled.php');
        preg_match('/if \(empty\(\$meet_scheduled\)\) \{.*?\n\}/s', $source, $matches);
        self::$block = $matches[0];
    }

    protected function setUp(): void
    {
        $this->savedRequest = $_REQUEST;
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->savedRequest;
    }

    private function resolve($requestValue, $includerValue = null)
    {
        $_REQUEST = [];
        if ($requestValue !== null) {
            $_REQUEST['meet_scheduled'] = $requestValue;
        }
        $meet_scheduled = $includerValue;
        eval('namespace ' . __NAMESPACE__ . '; ' . self::$block);
        return $meet_scheduled;
    }

    public function testLegitimateValuesAreKept(): void
    {
        foreach (['today', 'upcoming', 'past'] as $value) {
            $this->assertSame($value, $this->resolve($value));
        }
    }

    public function testMissingParameterStaysEmpty(): void
    {
        $this->assertSame('', $this->resolve(null));
        $this->assertSame('', $this->resolve(''));
    }

    public function testMarkupAndScriptBreakoutsAreRefused(): void
    {
        foreach (['x"><b>', "x');//", 'today"', 'TODAY', 'today ', '<today>'] as $value) {
            $this->assertSame('', $this->resolve($value), $value);
        }
        $this->assertSame('', $this->resolve(['today']));
    }

    public function testValueSetByAnIncluderIsKept(): void
    {
        $this->assertSame('upcoming', $this->resolve('x"><b>', 'upcoming'));
    }

    public function testRequestValueIsReadOnlyInTheAllowListedBlock(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Meet/meet_scheduled.php');
        $this->assertSame(1, substr_count($source, "\$_REQUEST['meet_scheduled']"));
    }
}
