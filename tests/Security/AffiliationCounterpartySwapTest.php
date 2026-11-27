<?php

namespace Tests\Security\AffiliationSwapFixtures;

class Forbidden extends \RuntimeException
{
}

// The endpoint source runs inside this namespace, where `new stdClass()` would not resolve.
class stdClass extends \stdClass
{
}

class State
{
    public static $admin = false;
    public static $usersId = 0;
    public static $isCompany = false;
    // row id => [users_id_company, users_id_affiliate, company_agree_date, affiliate_agree_date]
    public static $rows = [];
    public static $saved = [];
    public static $deleted = [];
    public static $tableInstalled = true;
}

class User
{
    public static function isLogged()
    {
        return !empty(State::$usersId);
    }

    public static function isAdmin()
    {
        return State::$admin;
    }

    public static function isACompany()
    {
        return State::$isCompany;
    }

    public static function getId()
    {
        return State::$usersId;
    }

    public static function getNameIdentificationById($id) { return 'User ' . $id; }
    public static function getPhoto($id, $a = false, $b = true) { return ''; }
}

class AVideoPlugin
{
    public static function loadPluginIfEnabled($name)
    {
        return new \stdClass();
    }

    public static function getObjectData($name) { return (object) ['enableAffiliation' => true]; }
}

class UserNotifications
{
    const type_success = 'success';
    const type_warning = 'warning';
    public static function createNotification(...$args) {}
}

class sqlDAL
{
    public static function readSql($sql, $types = '', $values = [], $refreshCache = false)
    {
        $rows = [];
        foreach (State::$rows as $id => $row) {
            if (!empty($values) && $id != $values[0]) { continue; }
            if (preg_match('/AND users_id_company = (\d+)/', $sql, $match) && $row[0] != $match[1]) { continue; }
            if (preg_match('/AND users_id_affiliate = (\d+)/', $sql, $match) && $row[1] != $match[1]) { continue; }
            $status = !empty($row[2]) && !empty($row[3]) ? 'a' : 'i';
            if (strpos($sql, "AND status = 'a'") !== false && $status !== 'a') { continue; }
            $rows[] = ['id' => $id, 'users_id_company' => $row[0], 'users_id_affiliate' => $row[1],
                'company_agree_date' => $row[2], 'affiliate_agree_date' => $row[3], 'status' => $status, 'modified' => '2026-11-27 00:00:00'];
        }
        return $rows;
    }

    public static function fetchAssoc($rows) { return $rows[0] ?? false; }
    public static function fetchAllAssoc($rows) { return $rows; }
    public static function close($rows) {}
    public static function num_rows($rows) { return count($rows); }
    public static function writeSql($sql, $types, $values)
    {
        State::$deleted[] = $values[0];
        unset(State::$rows[$values[0]]);
        return true;
    }
}

// Only the persistence boundary is stubbed. Constructor/load/getFromDb and the entire
// affiliation model, including save(), permission checks and lookups, come from source.
class ModelLoader
{
    public static function load()
    {
        $objectSource = file_get_contents(dirname(__DIR__, 2) . '/objects/Object.php');
        $objectMethods = '';
        foreach (['__construct', 'load', 'getFromDb'] as $method) {
            preg_match('/    (?:public |static )*function ' . $method . '\([^\n]*\)\s*\{.*?\n    \}/s', $objectSource, $match);
            if (empty($match)) { throw new \RuntimeException('Missing ObjectYPT method: ' . $method); }
            $objectMethods .= str_replace("class_exists('sqlDAL')", "class_exists('" . __NAMESPACE__ . "\\\\sqlDAL')", $match[0]) . "\n";
        }
        eval('namespace ' . __NAMESPACE__ . '; #[\\AllowDynamicProperties] class ObjectYPT {' . $objectMethods . '
            public static function isTableInstalled() { return State::$tableInstalled; }
            public function save() {
                State::$saved[] = ["id" => $this->id, "company" => $this->getUsers_id_company(),
                    "affiliate" => $this->getUsers_id_affiliate(), "active" => $this->getStatus() === "a"];
                $id = empty($this->id) ? 99 : $this->id;
                State::$rows[$id] = [$this->getUsers_id_company(), $this->getUsers_id_affiliate(),
                    $this->getCompany_agree_date() === "NULL" ? null : $this->getCompany_agree_date(),
                    $this->getAffiliate_agree_date() === "NULL" ? null : $this->getAffiliate_agree_date()];
                $this->modified = "2026-11-27 00:00:00";
                return $id;
            }
        }');
        $modelSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/CustomizeUser/Objects/Users_affiliations.php');
        $modelSource = preg_replace('/^\s*require_once[^\n]*\n/m', '', $modelSource);
        eval('namespace ' . __NAMESPACE__ . '; ' . preg_replace('/^<\?php/', '', $modelSource));

        $pluginSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/CustomizeUser/CustomizeUser.php');
        preg_match('/    static function getAffiliationNotifications\(\).*?\n    \}/s', $pluginSource, $notificationMethod);
        if (empty($notificationMethod)) { throw new \RuntimeException('Missing notification method'); }
        // Include the existing role-specific helpers for mutation tests of the prior implementation.
        preg_match('/    static function getAffiliateCompanies\(.*?\n    \}/s', $pluginSource, $affiliateMethod);
        preg_match('/    static function getCompanyAffiliates\(.*?\n    \}/s', $pluginSource, $companyMethod);
        eval('namespace ' . __NAMESPACE__ . '; class CustomizeUser {' . $affiliateMethod[0] . $companyMethod[0] . $notificationMethod[0] . '}');
    }
}

ModelLoader::load();

function __($text) { return $text; }

function forbiddenPage($msg = '', $log = false)
{
    throw new Forbidden($msg);
}

function _error_log($msg, $level = 0)
{
}

function _json_encode($v)
{
    return json_encode($v);
}

/**
 * Runs the real affiliation endpoint bodies without bootstrap/header lines, using the real
 * model with stubbed database persistence and notification delivery. No site requests or file writes.
 */
class AffiliationAddRunner
{
    public static function run(array $post)
    {
        return self::runEndpoint('add', $post);
    }

    public static function runEndpoint($endpoint, array $post)
    {
        $affiliationAddSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/CustomizeUser/View/Users_affiliations/' . $endpoint . '.json.php');
        $affiliationAddSource = preg_replace('/^\s*require_once[^\n]*\n/m', '', $affiliationAddSource);
        $affiliationAddSource = preg_replace('/^\s*header\([^\n]*\n/m', '', $affiliationAddSource);
        $affiliationAddSource = preg_replace('/^<\?php/', '', $affiliationAddSource);
        $affiliationAddOldPost = $_POST;
        $affiliationAddOldRequest = $_REQUEST;
        $_POST = $post;
        $_REQUEST = $post;
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; ' . $affiliationAddSource);
        } finally {
            $affiliationAddOutput = ob_get_clean();
            $_POST = $affiliationAddOldPost;
            $_REQUEST = $affiliationAddOldRequest;
        }
        return $affiliationAddOutput;
    }
}

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\AffiliationSwapFixtures\AffiliationAddRunner;
use Tests\Security\AffiliationSwapFixtures\Forbidden;
use Tests\Security\AffiliationSwapFixtures\State;
use Tests\Security\AffiliationSwapFixtures\Users_affiliations;
use Tests\Security\AffiliationSwapFixtures\CustomizeUser;

/**
 * GHSA-695j-7p82-jvh2: a party to a confirmed affiliation could edit it, replace the other party with any user and keep
 * the old party's agree date, so the row became active for a user who never accepted it.
 */
class AffiliationCounterpartySwapTest extends TestCase
{
    private $oldGlobal;

    protected function setUp(): void
    {
        $this->oldGlobal = $GLOBALS['global'];
        $GLOBALS['global']['webSiteRootURL'] = '/';
        unset($GLOBALS['customUser_getAffiliationNotifications']);
        State::$admin = false;
        State::$usersId = 5;
        State::$isCompany = true;
        // row 3: company 5 <-> affiliate 6, both agreed
        State::$rows = [3 => [5, 6, '2026-11-26 02:48:17', '2026-11-26 02:48:17']];
        State::$saved = [];
        State::$deleted = [];
        State::$tableInstalled = true;
    }

    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->oldGlobal;
        unset($GLOBALS['customUser_getAffiliationNotifications']);
    }

    public function testCompanyCannotReplaceTheAffiliateOfAConfirmedRow()
    {
        AffiliationAddRunner::run(['id' => 3, 'users_id_affiliate' => 3]);
        $this->assertSame([['id' => 3, 'company' => 5, 'affiliate' => 6, 'active' => true]], State::$saved);
    }

    public function testAffiliateCannotReplaceTheCompanyOfAConfirmedRow()
    {
        State::$usersId = 6;
        State::$isCompany = false;
        AffiliationAddRunner::run(['id' => 3, 'users_id_company' => 9]);
        $this->assertSame([['id' => 3, 'company' => 5, 'affiliate' => 6, 'active' => true]], State::$saved);
    }

    public function testNonPartyCannotEditRow()
    {
        State::$usersId = 7;
        $this->expectException(Forbidden::class);
        AffiliationAddRunner::run(['id' => 3, 'users_id_affiliate' => 7]);
    }

    public function testCompanyInvitationWithoutIdStaysPending()
    {
        AffiliationAddRunner::run(['users_id_affiliate' => 3]);
        $this->assertSame([['id' => null, 'company' => 5, 'affiliate' => 3, 'active' => false]], State::$saved);
    }

    public function testAffiliateRequestWithoutIdStaysPending()
    {
        State::$usersId = 6;
        State::$isCompany = false;
        AffiliationAddRunner::run(['users_id_company' => 9]);
        $this->assertSame([['id' => null, 'company' => 9, 'affiliate' => 6, 'active' => false]], State::$saved);
    }

    public function testAdminCanStillChangeTheParties()
    {
        State::$admin = true;
        State::$usersId = 1;
        AffiliationAddRunner::run([
            'id' => 3,
            'users_id_company' => 5,
            'users_id_affiliate' => 8,
            'company_agree_date' => '2026-11-27 10:00:00',
            'affiliate_agree_date' => '2026-11-27 10:00:00',
        ]);
        $this->assertSame([['id' => 3, 'company' => 5, 'affiliate' => 8, 'active' => true]], State::$saved);
    }

    /** @dataProvider consentEndpoints */
    public function testFormerCompanyCannotAgreeForTheAffiliate($endpoint)
    {
        // Company 5 invited affiliate 6, who has not agreed. An admin later demotes company 5.
        State::$rows[3][3] = null;
        State::$isCompany = false;
        AffiliationAddRunner::runEndpoint($endpoint, ['id' => 3, 'confirm' => 1]);
        $this->assertSame([['id' => 3, 'company' => 5, 'affiliate' => 6, 'active' => false]], State::$saved);
    }

    /** @dataProvider consentEndpoints */
    public function testAffiliatePromotedToCompanyCannotAgreeForTheCompany($endpoint)
    {
        // Affiliate 6 requested company 5, which has not agreed. An admin later marks user 6 as a company.
        State::$rows[3][2] = null;
        State::$usersId = 6;
        State::$isCompany = true;
        AffiliationAddRunner::runEndpoint($endpoint, ['id' => 3, 'confirm' => 1]);
        $this->assertSame([['id' => 3, 'company' => 5, 'affiliate' => 6, 'active' => false]], State::$saved);
    }

    public static function consentEndpoints()
    {
        return [['add'], ['confirm']];
    }

    public function testLoadedAndMissingIdsFollowObjectYPTConstructor()
    {
        $this->assertSame(3, (new Users_affiliations(3))->getId());
        $this->assertSame(0, (new Users_affiliations())->getId());
        $this->assertSame(0, (new Users_affiliations(999))->getId());
        $this->expectException(Forbidden::class);
        AffiliationAddRunner::run(['id' => 999]);
    }

    /** @dataProvider invitationRoles */
    public function testInvitationReusingExistingPairCannotCarryOtherConsent($usersId, $isCompany)
    {
        State::$usersId = $usersId;
        State::$isCompany = $isCompany;
        AffiliationAddRunner::run(['users_id_company' => 5, 'users_id_affiliate' => 6]);
        $this->assertSame([['id' => 3, 'company' => 5, 'affiliate' => 6, 'active' => false]], State::$saved);
        $this->assertNull(State::$rows[3][$isCompany ? 3 : 2]);
    }

    public static function invitationRoles() { return [[5, true], [6, false]]; }

    /** @dataProvider storedRoles */
    public function testCancelClearsOnlyTheCallersStoredDate($usersId, $isCompany, $dateIndex)
    {
        State::$usersId = $usersId;
        State::$isCompany = $isCompany;
        $otherDate = State::$rows[3][$dateIndex === 2 ? 3 : 2];
        AffiliationAddRunner::runEndpoint('confirm', ['id' => 3, 'confirm' => 0]);
        $this->assertFalse(State::$saved[0]['active']);
        $this->assertNull(State::$rows[3][$dateIndex]);
        $this->assertSame($otherDate, State::$rows[3][$dateIndex === 2 ? 3 : 2]);
    }

    public static function storedRoles() { return [[5, true, 2], [5, false, 2], [6, false, 3], [6, true, 3]]; }

    /** @dataProvider invitationRoles */
    public function testGenuineCounterpartyConfirmationActivatesThePair($usersId, $isCompany)
    {
        State::$usersId = $usersId;
        State::$isCompany = $isCompany;
        State::$rows[3][$isCompany ? 2 : 3] = null;
        $this->assertFalse(Users_affiliations::isUserAffiliateOrCompanyToEachOther(5, 6));
        AffiliationAddRunner::runEndpoint('confirm', ['id' => 3, 'confirm' => 1]);
        $this->assertTrue(State::$saved[0]['active']);
        $this->assertSame(3, Users_affiliations::isUserAffiliateOrCompanyToEachOther(6, 5)['id']);
        $this->assertSame(1, count(CustomizeUser::getAffiliateCompanies(6)));
        $this->assertSame(1, count(CustomizeUser::getCompanyAffiliates(5)));
    }

    public function testSameAccountOnBothSidesUsesCompanySideWithoutInventingAffiliateConsent()
    {
        State::$rows[3] = [5, 5, null, null];
        State::$isCompany = false;
        AffiliationAddRunner::runEndpoint('confirm', ['id' => 3, 'confirm' => 1]);
        $this->assertNotEmpty(State::$rows[3][2]);
        $this->assertNull(State::$rows[3][3]);
        $this->assertFalse(State::$saved[0]['active']);
    }

    /** @dataProvider invitationRoles */
    public function testAdminConfirmationRetainsCompanyFlagSelection($unused, $isCompany)
    {
        State::$admin = true;
        State::$usersId = 1;
        State::$isCompany = $isCompany;
        AffiliationAddRunner::runEndpoint('confirm', ['id' => 3, 'confirm' => 0]);
        $this->assertNull(State::$rows[3][$isCompany ? 2 : 3]);
        $this->assertNotEmpty(State::$rows[3][$isCompany ? 3 : 2]);
    }

    /** @dataProvider invitationRoles */
    public function testListAndNotificationsIncludeBothStoredRolesWithoutDuplicates($unused, $isCompany)
    {
        State::$isCompany = $isCompany;
        State::$rows = [3 => [5, 6, null, '2026-11-26 02:48:17'],
            4 => [9, 5, '2026-11-26 02:48:17', null], 5 => [5, 5, null, null],
            6 => [8, 9, null, null]];
        $list = json_decode(AffiliationAddRunner::runEndpoint('list', []), true);
        $this->assertSame([3, 5, 4], array_column($list['data'], 'id'));
        $this->assertSame(3, $list['recordsTotal']);
        $this->assertSame(3, $list['recordsFiltered']);
        $notifications = CustomizeUser::getAffiliationNotifications();
        $this->assertSame([3, 5, 4], array_column($notifications, 'id'));
        $this->assertSame([6, 5, 9], array_column($notifications, 'users_id'));
        $this->assertSame([5], array_keys($GLOBALS['customUser_getAffiliationNotifications']));
        $this->assertSame($notifications, CustomizeUser::getAffiliationNotifications());
    }

    public function testListRequiresLoginAndZeroUserLookupIsEmpty()
    {
        State::$usersId = 0;
        $this->assertSame([], Users_affiliations::getAllForUser(0));
        $this->expectException(Forbidden::class);
        AffiliationAddRunner::runEndpoint('list', []);
    }

    public function testUserLookupIsEmptyWhenTheTableCheckFails()
    {
        State::$tableInstalled = false;
        $this->assertSame([], Users_affiliations::getAllForUser(5));
        $this->assertSame([], CustomizeUser::getAffiliationNotifications());
    }

    public function testAdminListStillIncludesAllRows()
    {
        State::$admin = true;
        State::$rows[4] = [8, 9, null, null];
        $list = json_decode(AffiliationAddRunner::runEndpoint('list', []), true);
        $this->assertSame([3, 4], array_column($list['data'], 'id'));
        $this->assertSame(2, $list['recordsTotal']);
    }

    /** @dataProvider buttonRoles */
    public function testRenderedButtonMatchesTheDateChangedByConfirm($usersId, $isCompany, $admin, $company, $affiliate, $companyDate, $affiliateDate, $buttonClass)
    {
        State::$usersId = $usersId;
        State::$isCompany = $isCompany;
        State::$admin = $admin;
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/CustomizeUser/View/tabAffiliation.php');
        preg_match('/render: (function \(data, type, row, meta\) \{.*?\r?\n {20}\})/s', $source, $match);
        $this->assertNotEmpty($match);
        ob_start();
        try {
            eval('namespace Tests\\Security\\AffiliationSwapFixtures; ?>' . $match[1]);
        } finally {
            $renderer = ob_get_clean();
        }
        // proc_open() warns (Windows) or the child exits 127 (Linux) when node is missing: check first so the test skips.
        exec('node --version 2>&1', $nodeVersion, $nodeExitCode);
        if ($nodeExitCode !== 0) { $this->markTestSkipped('Node.js is required to exercise the real JavaScript renderer.'); }
        $script = 'const fs = require("node:fs"); const input = JSON.parse(fs.readFileSync(0, "utf8"));'
            . 'const render = new Function("return (" + input.renderer + ")")(); process.stdout.write(render(null, null, input.row, null));';
        $process = proc_open(['node', '-e', $script], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        if (!is_resource($process)) { $this->markTestSkipped('Node.js is required to exercise the real JavaScript renderer.'); }
        fwrite($pipes[0], json_encode(['renderer' => $renderer, 'row' => ['users_id_company' => $company,
            'users_id_affiliate' => $affiliate, 'company_agree_date' => $companyDate, 'affiliate_agree_date' => $affiliateDate]]));
        fclose($pipes[0]);
        $button = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $error);
        $this->assertStringContainsString($buttonClass, $button);
    }

    public static function buttonRoles()
    {
        return [
            [6, true, false, 5, 6, 'agreed', null, 'btn-success'],
            [5, false, false, 5, 6, null, 'agreed', 'btn-success'],
            [6, true, false, 5, 6, null, 'agreed', 'btn-danger'],
            [5, false, false, 5, 6, 'agreed', null, 'btn-danger'],
            [6, true, false, 5, 6, 'agreed', 'agreed', 'btn-warning'],
            [5, false, false, 5, 5, null, 'agreed', 'btn-success'],
            [1, true, true, 5, 6, null, 'agreed', 'btn-success'],
            [1, false, true, 5, 6, 'agreed', null, 'btn-success'],
        ];
    }
}
