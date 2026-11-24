<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * plugin/MonetizeUsers/View/report.php filters the rewards by User::getId(), and MonetizeUsers::getRewards()
 * drops the owner filter when that id is empty, so an anonymous visitor got the rewards of every creator.
 * Both pages that include report.php must refuse anonymous visitors before it runs (GHSA-923j-cj44-f9g8).
 */
class MonetizeUsersReportLoginTest extends TestCase
{
    private function readSource($relativePath)
    {
        return file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath);
    }

    /**
     * Offset of the first "if (<condition>) { forbiddenPage(" block, whatever the spacing and line endings.
     */
    private function guardOffset($source, $conditionPattern)
    {
        if (!preg_match('/if\s*\(\s*' . $conditionPattern . '\s*\)\s*\{\s*forbiddenPage\s*\(/', $source, $match, PREG_OFFSET_CAPTURE)) {
            return false;
        }
        return $match[0][1];
    }

    public function testReportPageRefusesAnonymousVisitorsBeforeLoadingTheReport()
    {
        $source = $this->readSource('plugin/MonetizeUsers/View/index.php');

        $guard = $this->guardOffset($source, '!\s*User::isLogged\(\)');
        $this->assertNotFalse($guard, 'View/index.php must call forbiddenPage() for anonymous visitors');
        $this->assertGreaterThan(strpos($source, 'videos/configuration.php'), $guard);
        $this->assertLessThan(strpos($source, 'new Page('), $guard);
        $this->assertLessThan(strpos($source, 'MonetizeUsers/View/report.php'), $guard);
    }

    public function testReportPageRefusesADisabledPluginAndLoadsTheWalletClassBeforeTheReport()
    {
        // report.php calls MonetizeUsers::getRewards() and YPTWallet::formatCurrency(); without these the page was a fatal error
        $source = $this->readSource('plugin/MonetizeUsers/View/index.php');
        $report = strpos($source, 'MonetizeUsers/View/report.php');

        $enabledGuard = $this->guardOffset($source, "!\s*AVideoPlugin::isEnabledByName\(\s*['\"]MonetizeUsers['\"]\s*\)");
        $this->assertNotFalse($enabledGuard, 'View/index.php must call forbiddenPage() when MonetizeUsers is disabled');
        $this->assertGreaterThan($this->guardOffset($source, '!\s*User::isLogged\(\)'), $enabledGuard);
        $this->assertLessThan($report, $enabledGuard);

        $this->assertSame(1, preg_match('/AVideoPlugin::loadPlugin\(\s*[\'"]YPTWallet[\'"]\s*\)/', $source, $match, PREG_OFFSET_CAPTURE));
        $this->assertLessThan($report, $match[0][1]);
    }

    public function testWalletHistoryStillRefusesAnonymousVisitorsBeforeIncludingTheReport()
    {
        $source = $this->readSource('plugin/YPTWallet/view/history.php');

        $guard = $this->guardOffset($source, '!\s*User::isLogged\(\)');
        $this->assertNotFalse($guard, 'YPTWallet/view/history.php must refuse anonymous visitors');
        $this->assertLessThan(strpos($source, 'MonetizeUsers/View/report.php'), $guard);
    }

    public function testReportUsesTheLoggedUserAndOnlyAdminsMayChooseAnotherUser()
    {
        $source = $this->readSource('plugin/MonetizeUsers/View/report.php');

        $this->assertSame(1, preg_match('/\$users_id\s*=\s*User::getId\(\)\s*;/', $source));
        $this->assertSame(1, preg_match('/if\s*\(\s*User::isAdmin\(\)\s*&&\s*!\s*empty\(\s*\$_REQUEST\[\s*[\'"]users_id[\'"]\s*\]\s*\)\s*\)/', $source));
    }
}
