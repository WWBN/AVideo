<?php

namespace Tests\Unit\SubscriptionCronStatus;

use PHPUnit\Framework\TestCase;

class CronProbe
{
    public static $rows = [];
    public static $failedUsers = [];
    public static $save = true;
    public static $history = true;
    public static $transfers = 0;
    public static $refunds = 0;
}
class AVideoPlugin
{
    public static function getObjectDataIfEnabled($name) { return (object) ['thanksForSubscribe' => 'Renewed {plan_name} until {renew_date}']; }
    public static function loadPluginIfEnabled($name) { return true; }
}
class User { public static function isAdmin() { return true; } }
class AVideoLog { public static $ERROR = 1; }
class Subscription
{
    public static function getExpiredSubscriptions() { return CronProbe::$rows; }
    public static function addToHistory($id, $days, $price) { return CronProbe::$history; }
}
class YPTWallet
{
    public static function transferBalanceToSiteOwner($user, $price, $description, $force)
    {
        ++CronProbe::$transfers;
        return !in_array($user, CronProbe::$failedUsers, true);
    }
    public static function transferBalanceFromSiteOwner($user, $price, $description, $force) { ++CronProbe::$refunds; }
    public static function getBillingInterval($days) { return (object) ['dateExpr' => '1 day']; }
    public static function formatCurrency($price) { return (string) $price; }
}
class SubscriptionTable
{
    public function __construct($id) {}
    public static function getSubscription($user, $plan, $cache) { return ['id' => 1, 'good_until' => '2030-01-01 00:00:00']; }
    public function setUsers_id($id) {}
    public function setSubscriptions_plans_id($id) {}
    public function setGood_until($date) {}
    public function setStatus($status) {}
    public function save() { return CronProbe::$save ? 101 : false; }
}
function isCommandLineInterface() { return true; }
function _error_log($message, $level = null) {}
function __($message) { return $message; }

class SubscriptionCronStatusTest extends TestCase
{
    protected function setUp(): void
    {
        if (!is_file(dirname(__DIR__, 2) . '/plugin/Subscription/crontab.php')) {
            $this->markTestSkipped('The separately distributed Subscription plugin is not installed.');
        }
        CronProbe::$rows = [];
        CronProbe::$failedUsers = [];
        CronProbe::$save = CronProbe::$history = true;
        CronProbe::$transfers = CronProbe::$refunds = 0;
    }

    private function runCron()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Subscription/crontab.php');
        // Execute the actual cron body with isolated wallet/database helpers, never site configuration.
        $start = strpos($source, '$objPlugin =');
        $this->assertNotFalse($start);
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; use \\stdClass;' . substr($source, $start));
            return json_decode(ob_get_contents(), true, 512, JSON_THROW_ON_ERROR);
        } finally {
            ob_end_clean();
        }
    }

    private function row($user = 1)
    {
        return ['users_id' => $user, 'subscriptions_plans_id' => 2, 'price' => 5, 'how_many_days' => 1, 'plan_name' => 'Test plan'];
    }

    public function testIdleCronSucceedsWithoutChargingAnyone()
    {
        $result = $this->runCron();
        $this->assertFalse($result['error']);
        $this->assertSame([], $result['msg']);
        $this->assertSame(0, CronProbe::$transfers);
    }

    public function testSuccessfulRenewalReportsSuccess()
    {
        CronProbe::$rows = [$this->row()];
        $result = $this->runCron();
        $this->assertFalse($result['error']);
        $this->assertCount(1, $result['msg']);
        $this->assertSame(1, CronProbe::$transfers);
        $this->assertSame(0, CronProbe::$refunds);
    }

    public function testFailedTransferIsNotHiddenByALaterSuccess()
    {
        CronProbe::$rows = [$this->row(1), $this->row(2)];
        CronProbe::$failedUsers = [1];
        $this->assertTrue($this->runCron()['error']);
        $this->assertSame(2, CronProbe::$transfers);
    }

    public function testFailedSaveKeepsTheExistingRefundAndReportsFailure()
    {
        CronProbe::$rows = [$this->row()];
        CronProbe::$save = false;
        $this->assertTrue($this->runCron()['error']);
        $this->assertSame(1, CronProbe::$refunds);
    }

    public function testFailedHistoryReportsFailure()
    {
        CronProbe::$rows = [$this->row()];
        CronProbe::$history = false;
        $this->assertTrue($this->runCron()['error']);
        $this->assertSame(0, CronProbe::$refunds);
    }
}
