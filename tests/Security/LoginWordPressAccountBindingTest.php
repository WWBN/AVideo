<?php

namespace Tests\Security;

require_once dirname(__DIR__) . '/fixtures/social-login-user.php';

use PHPUnit\Framework\TestCase;
use Tests\Security\SocialLoginFixtures\AVideoPlugin;
use Tests\Security\SocialLoginFixtures\User;
use Tests\Security\SocialLoginFixtures\UserStore;

/**
 * GHSA-9wm7-g9h5-92xq: the LoginWordPress custom-site login checked the password on the WordPress site and then logged
 * in to the AVideo account with the same username, without its password. Anyone able to create that username on the
 * WordPress site took over the AVideo account (admin included). The account must be bound to the WordPress user id.
 */
class LoginWordPressAccountBindingTest extends TestCase
{
    private $savedGlobals = [];

    public static function setUpBeforeClass(): void
    {
        if (class_exists('Tests\\Security\\SocialLoginFixtures\\LoginWordPress', false)) {
            return;
        }
        $methods = \Tests\Security\SocialLoginFixtures\extractProductionMethods('plugin/LoginWordPress/LoginWordPress.php', ['login', 'getBindingValue', 'getBoundUserId']);
        eval('namespace Tests\\Security\\SocialLoginFixtures;
            class LoginWordPress {
                const BINDING_KEY = "loginWordPressUser";
                public static $wpUsers = [];
                private static function auth($user, $pass) {
                    $wp = self::$wpUsers[$user] ?? null;
                    return ($wp && $wp["pass"] === $pass) ? (object) ["id" => $wp["id"], "avatar_urls" => (object) ["96" => "https://wp.example.test/a.png"]] : null;
                }
                ' . $methods . '
            }');
    }

    protected function setUp(): void
    {
        foreach (['global', 'config', 'advancedCustomUser', 'advancedCustom', '_SESSION', '_COOKIE', '_REQUEST', '_SERVER'] as $key) {
            $this->savedGlobals[$key] = [array_key_exists($key, $GLOBALS), $GLOBALS[$key] ?? null];
        }
        $GLOBALS['global'] = ['mysqli' => (object) ['insert_id' => 0, 'errno' => 1, 'error' => 'fixture failure']];
        $GLOBALS['config'] = null;
        $GLOBALS['advancedCustomUser'] = (object) ['forceLoginToBeTheEmail' => false, 'emailMustBeUnique' => false];
        $GLOBALS['advancedCustom'] = (object) ['enableOldPassHashCheck' => false];
        $_SESSION = $_COOKIE = $_REQUEST = [];
        $_SERVER['HTTP_USER_AGENT'] = 'login-wordpress-fixture';
        UserStore::$writes = UserStore::$groups = UserStore::$signIns = UserStore::$verifications = [];
        UserStore::$failWrite = UserStore::$regenerated = 0;
        UserStore::$cookie = null;
        UserStore::$beforeRead = null;
        AVideoPlugin::$onSignIn = null;
        UserStore::$allowLoginAttempt = true;
        UserStore::$reservedAttempts = UserStore::$refundedAttempts = 0;
        UserStore::$rows = [
            1 => ['user' => 'admin', 'email' => 'admin@example.net', 'isAdmin' => 1, 'emailVerified' => 0],
            2 => ['user' => 'victim', 'email' => 'victim@example.net', 'isAdmin' => 0, 'emailVerified' => 0],
            // created by this plugin before the binding existed: the WordPress password was saved locally
            3 => ['user' => 'legacywp', 'email' => 'legacywp', 'isAdmin' => 0, 'emailVerified' => 1, 'password' => md5('wp-legacy-pass')],
            4 => ['user' => 'attacker', 'email' => 'attacker@example.net', 'isAdmin' => 0, 'emailVerified' => 1, 'password' => md5('attacker-local')],
        ];
        foreach (UserStore::$rows as $id => &$row) {
            $row += ['id' => $id, 'password' => md5('local-secret-' . $id), 'status' => 'a', 'channelName' => $row['user'], 'photoURL' => '', 'externalOptions' => []];
        }
        unset($row);
        AVideoPlugin::$objectData['LoginWordPress'] = (object) [
            'customWordPressSite' => 'https://WP.example.test/blog/',
            'customWordPressSiteSavePassword' => true,
            'customWordPressSiteIfLoginFailTryDatabase' => false,
            'autoAddNewUsersOnUserGroup' => (object) ['value' => 0],
        ];
        // WordPress accounts the attacker registered with the victims' usernames, and two legitimate ones
        \Tests\Security\SocialLoginFixtures\LoginWordPress::$wpUsers = [
            'admin' => ['id' => 101, 'pass' => 'attacker-wp'],
            'victim' => ['id' => 102, 'pass' => 'attacker-wp'],
            'legacywp' => ['id' => 103, 'pass' => 'wp-legacy-pass'],
            'newguy' => ['id' => 104, 'pass' => 'newguy-wp'],
        ];
    }

    protected function tearDown(): void
    {
        UserStore::$beforeRead = null;
        AVideoPlugin::$onSignIn = null;
        UserStore::$allowLoginAttempt = true;
        foreach ($this->savedGlobals as $key => [$exists, $value]) {
            if ($exists) {
                $GLOBALS[$key] = $value;
            } else {
                unset($GLOBALS[$key]);
            }
        }
    }

    private function wpLogin($user, $pass)
    {
        return \Tests\Security\SocialLoginFixtures\LoginWordPress::login($user, $pass);
    }

    public function testWordPressUsernameNeverSelectsAnExistingAccount()
    {
        foreach (['admin' => 1, 'victim' => 2] as $user => $id) {
            $_SESSION = [];
            $this->assertNotSame(User::USER_LOGGED, $this->wpLogin($user, 'attacker-wp'), $user);
            $this->assertArrayNotHasKey('user', $_SESSION, $user);
            $this->assertSame([], UserStore::$rows[$id]['externalOptions'], $user);
            $this->assertSame(0, UserStore::$rows[$id]['emailVerified'], $user);
        }
        $this->assertCount(4, UserStore::$rows);
        $this->assertSame([], UserStore::$signIns);
    }

    public function testLoggedInSessionCannotBindAnotherAccount()
    {
        $this->assertSame(User::USER_LOGGED, (new User(0, 'attacker', 'attacker-local'))->login(false, false, true));
        $this->assertNotSame(User::USER_LOGGED, $this->wpLogin('admin', 'attacker-wp'));
        $this->assertSame(4, $_SESSION['user']['id']);
        $this->assertSame([], UserStore::$rows[1]['externalOptions']);
        $this->assertSame([], UserStore::$rows[4]['externalOptions']);
    }

    public function testNewWordPressUserGetsABoundAccountAndKeepsIt()
    {
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('newguy', 'newguy-wp'));
        $id = $_SESSION['user']['id'];
        $this->assertSame(5, $id);
        $this->assertSame('newguy', UserStore::$rows[$id]['user']);
        $this->assertSame(['loginWordPressUser' => 'https://wp.example.test/blog:104'], UserStore::$rows[$id]['externalOptions']);

        // the binding, not the name or the password, selects the account on the next logins
        $_SESSION = [];
        \Tests\Security\SocialLoginFixtures\LoginWordPress::$wpUsers['newguy']['pass'] = 'changed-on-wordpress';
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('newguy', 'changed-on-wordpress'));
        $this->assertSame($id, $_SESSION['user']['id']);
        $this->assertCount(5, UserStore::$rows);
    }

    public function testLegacyAccountIsBoundWhenItsAVideoPasswordMatches()
    {
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertSame(3, $_SESSION['user']['id']);
        $this->assertSame(['loginWordPressUser' => 'https://wp.example.test/blog:103'], UserStore::$rows[3]['externalOptions']);

        $_SESSION = [];
        \Tests\Security\SocialLoginFixtures\LoginWordPress::$wpUsers['legacywp']['pass'] = 'new-wp-pass';
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'new-wp-pass'));
        $this->assertSame(3, $_SESSION['user']['id']);
    }

    public function testAnotherWordPressUserWithTheSameNameCannotUseABoundAccount()
    {
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $_SESSION = [];
        // the WordPress user was deleted and someone registered the same name again (new WordPress id)
        \Tests\Security\SocialLoginFixtures\LoginWordPress::$wpUsers['legacywp'] = ['id' => 999, 'pass' => 'attacker-wp'];
        $this->assertNotSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'attacker-wp'));
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertSame(['loginWordPressUser' => 'https://wp.example.test/blog:103'], UserStore::$rows[3]['externalOptions']);
    }

    public function testWordPressRejectionStillFails()
    {
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('admin', 'wrong'));
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    public function testDifferentBindingIsRefusedEvenWithTheLocalPassword()
    {
        UserStore::$rows[3]['externalOptions'] = ['loginWordPressUser' => 'https://wp.example.test/blog:999'];
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertSame([], UserStore::$writes);
        $this->assertSame([], UserStore::$signIns);
    }

    public function testEmailAliasCannotBindAnotherAccountBeforeRefusing()
    {
        $GLOBALS['advancedCustomUser']->emailMustBeUnique = true;
        UserStore::$rows[3]['user'] = 'alice@example.net';
        UserStore::$rows[4]['email'] = 'alice@example.net';
        \Tests\Security\SocialLoginFixtures\LoginWordPress::$wpUsers['alice@example.net'] = ['id' => 999, 'pass' => 'attacker-local'];
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('alice@example.net', 'attacker-local'));
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertSame([], UserStore::$rows[4]['externalOptions']);
        $this->assertSame([], UserStore::$signIns);
    }

    public function testCurrentSessionStillNeedsTheLocalPasswordForANewBinding()
    {
        $_SESSION['user'] = UserStore::$rows[1];
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('admin', 'attacker-wp'));
        $this->assertSame(1, $_SESSION['user']['id']);
        $this->assertSame([], UserStore::$rows[1]['externalOptions']);
    }

    public function testFailedBindingSaveDoesNotLogInAndCanBeRetried()
    {
        UserStore::$failWrite = 1;
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertNull(UserStore::$cookie);
        $this->assertSame([], UserStore::$signIns);
        $this->assertSame([], UserStore::$rows[3]['externalOptions']);
        UserStore::$failWrite = 0;
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertSame([3], UserStore::$signIns);
    }

    public function testRegistrationRaceNeverVerifiesOrSavesTheOtherAccount()
    {
        $lookups = 0;
        UserStore::$beforeRead = static function ($sql, $values) use (&$lookups) {
            if (strpos($sql, 'WHERE user = ? LIMIT 1') !== false && $values[0] === 'newguy' && ++$lookups === 2) {
                UserStore::$rows[5] = array_replace(UserStore::$rows[2], ['id' => 5, 'user' => 'newguy']);
            }
        };
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('newguy', 'newguy-wp'));
        $this->assertSame(0, UserStore::$rows[5]['emailVerified']);
        $this->assertSame([], UserStore::$writes);
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    public function testDifferentSitesOnTheSameHostHaveDifferentBindings()
    {
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $_SESSION = [];
        AVideoPlugin::$objectData['LoginWordPress']->customWordPressSite = 'https://wp.example.test/other/';
        \Tests\Security\SocialLoginFixtures\LoginWordPress::$wpUsers['otheruser'] = ['id' => 103, 'pass' => 'other-pass'];
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('otheruser', 'other-pass'));
        $this->assertNotSame(3, $_SESSION['user']['id']);
    }

    public function testBindingIncludesThePortAndRejectsAnEmptySite()
    {
        $plugin = \Tests\Security\SocialLoginFixtures\LoginWordPress::class;
        $original = $plugin::getBindingValue(103);
        AVideoPlugin::$objectData['LoginWordPress']->customWordPressSite = 'https://wp.example.test:8443/blog/';
        $this->assertNotSame($original, $plugin::getBindingValue(103));
        AVideoPlugin::$objectData['LoginWordPress']->customWordPressSite = '';
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('newguy', 'newguy-wp'));
        $this->assertCount(4, UserStore::$rows);
    }

    public function testFailedLocalPasswordUsesThePenaltyWithoutLoginSideEffects()
    {
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('admin', 'attacker-wp'));
        $this->assertSame(1, UserStore::$reservedAttempts);
        $this->assertSame(0, UserStore::$refundedAttempts);
        $this->assertSame([], UserStore::$writes);

        UserStore::$allowLoginAttempt = false;
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertSame([], UserStore::$rows[3]['externalOptions']);
        $this->assertArrayNotHasKey('user', $_SESSION);
        UserStore::$allowLoginAttempt = true;
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertSame(1, UserStore::$refundedAttempts);
    }

    public function testDatabaseFallbackStillChecksTheAVideoPassword()
    {
        AVideoPlugin::$objectData['LoginWordPress']->customWordPressSiteIfLoginFailTryDatabase = true;
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('admin', 'local-secret-1'));
        $this->assertSame(1, $_SESSION['user']['id']);
        $this->assertSame([], UserStore::$rows[1]['externalOptions']);
    }

    public function testRandomLocalPasswordAndConfiguredGroupsStillWork()
    {
        AVideoPlugin::$objectData['LoginWordPress']->customWordPressSiteSavePassword = false;
        AVideoPlugin::$objectData['LoginWordPress']->autoAddNewUsersOnUserGroup->value = 7;
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('newguy', 'newguy-wp'));
        $id = $_SESSION['user']['id'];
        $this->assertNotSame(md5('newguy-wp'), UserStore::$rows[$id]['password']);
        $this->assertSame([7], UserStore::$groups[$id]);
        $this->assertSame(1, UserStore::$rows[$id]['emailVerified']);
        $_SESSION = [];
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('newguy', 'newguy-wp'));
        $this->assertSame($id, $_SESSION['user']['id']);
    }

    public function testNestedBindingCannotShadowARenamedAccount()
    {
        UserStore::$rows[1]['externalOptions'] = ['nested' => ['loginWordPressUser' => 'https://wp.example.test/blog:103']];
        UserStore::$rows[3]['user'] = 'renamed';
        UserStore::$rows[3]['externalOptions'] = ['loginWordPressUser' => 'https://wp.example.test/blog:103'];
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertSame(3, $_SESSION['user']['id']);
    }

    public function testOtherSessionCannotSelectABoundAccountOrChangeItsGroups()
    {
        $_SESSION['user'] = UserStore::$rows[4];
        UserStore::$rows[3]['externalOptions'] = ['loginWordPressUser' => 'https://wp.example.test/blog:103'];
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertSame(4, $_SESSION['user']['id']);
        $this->assertSame([], UserStore::$groups);
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('newguy', 'newguy-wp'));
        $this->assertCount(4, UserStore::$rows);
    }

    public function testCaseDifferencesNeverAuthorizeAnExistingAccount()
    {
        \Tests\Security\SocialLoginFixtures\LoginWordPress::$wpUsers['ADMIN'] = ['id' => 999, 'pass' => 'attacker-wp'];
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('ADMIN', 'attacker-wp'));
        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertSame([], UserStore::$rows[1]['externalOptions']);
    }

    public function testInactiveLegacyAccountIsNotBound()
    {
        UserStore::$rows[3]['status'] = 'i';
        $this->assertSame(User::USER_NOT_FOUND, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertSame([], UserStore::$writes);
        $this->assertSame([], UserStore::$rows[3]['externalOptions']);
    }

    public function testExplicitDefaultPortAndTrailingSlashKeepTheSameSiteIdentity()
    {
        $plugin = \Tests\Security\SocialLoginFixtures\LoginWordPress::class;
        $binding = $plugin::getBindingValue(103);
        AVideoPlugin::$objectData['LoginWordPress']->customWordPressSite = 'https://wp.example.test:443/blog';
        $this->assertSame($binding, $plugin::getBindingValue(103));
        $this->assertFalse($plugin::getBindingValue(-1));
        $this->assertFalse($plugin::getBindingValue('invalid'));
    }

    public function testLegacyBindingIsPersistedBeforeSignInHooksRun()
    {
        $GLOBALS['advancedCustomUser']->unverifiedEmailsCanNOTLogin = true;
        UserStore::$rows[3]['emailVerified'] = 0;
        AVideoPlugin::$onSignIn = static function ($id) {
            self::assertSame('https://wp.example.test/blog:103', UserStore::$rows[$id]['externalOptions']['loginWordPressUser'] ?? null);
        };
        $this->assertSame(User::USER_LOGGED, $this->wpLogin('legacywp', 'wp-legacy-pass'));
        $this->assertSame([3], UserStore::$signIns);
        $this->assertSame(0, UserStore::$rows[3]['emailVerified']);
        $this->assertSame([], UserStore::$verifications);
    }
}
