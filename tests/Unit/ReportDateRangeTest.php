<?php
namespace Tests\Unit;

use Tests\TestCase;

class ReportDateRangeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once APP_ROOT . '/objects/reportDateRange.php';
    }

    public function testInclusiveDatesAndLegacyClients(): void
    {
        $expected = ['2024-02-29 00:00:00', '2024-03-01 23:59:59'];
        $this->assertSame($expected, reportDateRange(['dateFrom' => '2024-02-29', 'dateTo' => '2024-03-01']));
        $this->assertSame($expected, reportDateRange(['dateFrom' => '02/29/2024', 'dateTo' => '03/01/2024']));
        $this->assertSame(['2026-09-14 00:00:00', '2026-09-14 23:59:59'], reportDateRange(['dateFrom' => '2026-09-14', 'dateTo' => '2026-09-14']));
    }

    /** @dataProvider invalidDates */
    public function testInvalidDatesDoNotBecomeEpochOrNormalizedDates($from, $to): void
    {
        $this->expectException(\InvalidArgumentException::class);
        reportDateRange(['dateFrom' => $from, 'dateTo' => $to]);
    }

    public function invalidDates(): array
    {
        return [
            [null, null], ['', '2026-01-01'], [[], '2026-01-01'],
            ['2025-02-29', '2025-03-01'], ['2026-04-31', '2026-05-01'],
            ['2026-09-15', '2026-09-14'], ['tomorrow', '2026-09-14'],
            ["2026-09-14\0", '2026-09-14'], ['2026-09-14', '09/32/2026'],
            ['2026-09-14 12:00:00', '2026-09-14']
        ];
    }
}
