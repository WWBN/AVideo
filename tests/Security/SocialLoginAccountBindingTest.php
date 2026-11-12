<?php

namespace Tests\Security;

require_once dirname(__DIR__) . '/fixtures/social-login-user.php';

use PHPUnit\Framework\TestCase;
use Tests\Security\SocialLoginFixtures\User;
use Tests\Security\SocialLoginFixtures\UserStore;

/**
 * GHSA-9rvm-cfc4-q9g9: when the provider returns no email, the display name (chosen by the provider's user) selected
 * an existing local account and logged in to it without a password. It must never select an account; the account is
 * bound to the provider's user id.
 */
class SocialLoginAccountBindingTest extends TestCase
{
    private $savedGlobals = [];

    protected function setUp(): void
    {
        foreach (['global', 'config', 'advancedCustomUser', 'advancedCustom', '_SESSION', '_COOKIE', '_REQUEST', '_SERVER'] as $key) {
            $this->savedGlobals[$key] = [array_key_exists($key, $GLOBALS), $GLOBALS[$key] ?? null];
        }
        $GLOBALS['global'] = ['mysqli' => (object) ['insert_id' => 0, 'errno' => 1, 'error' => 'fixture failure']];
        $GLOBALS['config'] = null;
        $GLOBALS['advancedCustomUser'] = (object) ['forceLoginToBeTheEmail' => false, 'emailMustBeUnique' => false];
        $GLOBALS['advancedCustom'] = new \stdClass();
        $_SESSION = $_COOKIE = $_REQUEST = [];
        $_SERVER['HTTP_USER_AGENT'] = 'social-login-fixture';
        UserStore::$writes = UserStore::$groups = UserStore::$signIns = UserStore::$verifications = [];
        UserStore::$failWrite = UserStore::$regenerated = 0;
        UserStore::$cookie = null;
        UserStore::$rows = [
            1 => ['user' => 'admin', 'email' => '', 'isAdmin' => 1, 'photoURL' => 'https://graph.facebook.com/111/picture', 'externalOptions' => []],
            2 => ['user' => 'alice_streamer', 'email' => 'alice@example.net', 'isAdmin' => 0, 'photoURL' => '', 'externalOptions' => []],
            // created by a Facebook login without email before the binding existed
            3 => ['user' => 'John Smith', 'email' => '', 'isAdmin' => 0, 'photoURL' => 'https://graph.facebook.com/333/picture?width=150&height=150', 'externalOptions' => []],
            // created by an X login without email that used the default avatar
            4 => ['user' => 'bob', 'email' => '', 'isAdmin' => 0, 'photoURL' => 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png', 'externalOptions' => []],
        ];
        foreach (UserStore::$rows as $id => &$row) {
            $row += ['id' => $id, 'password' => str_repeat('a', 32), 'status' => 'a', 'channelName' => $row['user'], 'emailVerified' => 1];
        }
        unset($row);
    }

    protected function tearDown(): void
    {
        foreach ($this->savedGlobals as $key => [$exists, $value]) {
            if ($exists) {
                $GLOBALS[$key] = $value;
            } else {
                unset($GLOBALS[$key]);
            }
        }
    }

    private function profile($identifier, $displayName, $photoURL = '', $email = null)
    {
        return (object) ['identifier' => $identifier, 'displayName' => $displayName, 'photoURL' => $photoURL, 'email' => $email];
    }

    public function testDisplayNameNeverSelectsAnExistingAccount()
    {
        foreach ([['alice_streamer', 2], ['Alice_Streamer', 2], ['admin', 1], ['John Smith', 3], ['bob', 4]] as [$displayName, $victim]) {
            $id = User::getSocialLoginUserId('Twitter', $this->profile('999' . $victim, $displayName, 'https://pbs.twimg.com/profile_images/1/a_normal.jpg'));
            $this->assertNotSame($victim, $id, $displayName);
            $this->assertSame('Twitter_999' . $victim, UserStore::$rows[$id]['user'], $displayName);
        }
        // a default avatar is shared by many users and never proves identity
        $id = User::getSocialLoginUserId('Twitter', $this->profile('555', 'bob', UserStore::$rows[4]['photoURL']));
        $this->assertNotSame(4, $id);
        $this->assertSame([], UserStore::$rows[4]['externalOptions']);
        // an admin is never reattached, even with the same photo
        $this->assertNotSame(1, User::getSocialLoginUserId('Facebook', $this->profile('111', 'admin', UserStore::$rows[1]['photoURL'])));
    }

    public function testSameProviderUserGetsTheSameAccountBack()
    {
        $first = User::getSocialLoginUserId('Twitter', $this->profile('42', 'alice_streamer', 'https://pbs.twimg.com/profile_images/7/x.jpg'));
        $again = User::getSocialLoginUserId('Twitter', $this->profile('42', 'renamed_handle', 'https://pbs.twimg.com/profile_images/8/y.jpg'));
        $this->assertSame($first, $again);
        $this->assertSame('42', UserStore::$rows[$first]['externalOptions']['socialLoginTwitter']);
        // another provider user with the same display name gets its own account
        $this->assertNotSame($first, User::getSocialLoginUserId('Twitter', $this->profile('43', 'alice_streamer')));
    }

    public function testLegacyAccountIsReattachedOnlyWithItsOwnPhoto()
    {
        // another Facebook "John Smith" has a different id, so a different photo URL
        $other = User::getSocialLoginUserId('Facebook', $this->profile('444', 'John Smith', 'https://graph.facebook.com/444/picture?width=150&height=150'));
        $this->assertNotSame(3, $other);
        $this->assertSame([], UserStore::$rows[3]['externalOptions']);

        $owner = User::getSocialLoginUserId('Facebook', $this->profile('333', 'John Smith', UserStore::$rows[3]['photoURL']));
        $this->assertSame(3, $owner);
        $this->assertSame('333', UserStore::$rows[3]['externalOptions']['socialLoginFacebook']);

        // once bound, the photo no longer matters and nobody else can take it
        $this->assertSame(3, User::getSocialLoginUserId('Facebook', $this->profile('333', 'John Smith', 'https://graph.facebook.com/333/picture?width=200')));
        $this->assertNotSame(3, User::getSocialLoginUserId('Facebook', $this->profile('445', 'John Smith', UserStore::$rows[3]['photoURL'])));
    }

    public function testUsernameSquattingTheBoundNameIsRefused()
    {
        UserStore::$rows[5] = ['user' => 'Twitter_77', 'email' => 'squatter@example.org', 'isAdmin' => 0, 'photoURL' => '', 'externalOptions' => []];
        $this->assertFalse(User::getSocialLoginUserId('Twitter', $this->profile('77', 'someone')));
    }

    public function testReattachedLegacyAccountSurvivesDisplayNameAndPhotoChanges()
    {
        $this->assertSame(3, User::getSocialLoginUserId('Facebook', $this->profile('333', 'John Smith', UserStore::$rows[3]['photoURL'])));
        $this->assertSame(3, User::getSocialLoginUserId('Facebook', $this->profile('333', 'Renamed Owner', 'https://graph.facebook.com/333/picture?width=200')));
        $this->assertCount(4, UserStore::$rows);
    }

    public function testNestedUnrelatedOptionCannotShadowTheRealBinding()
    {
        UserStore::$rows[1]['externalOptions'] = ['nested' => ['socialLoginFacebook' => '333']];
        UserStore::$rows[3]['externalOptions'] = ['socialLoginFacebook' => '333'];
        $this->assertSame(3, User::getSocialLoginUserId('Facebook', $this->profile('333', 'Renamed')));
    }

    public function testBindingIsWrittenWithTheNewAccountAndPreservesDefaultGroups()
    {
        $GLOBALS['advancedCustomUser']->userDefaultUserGroup = (object) ['value' => 7];
        // A second write would fail, leaving an orphaned username in the original implementation.
        UserStore::$failWrite = 2;
        $id = User::getSocialLoginUserId('Twitter', $this->profile('42', 'Alice'), true);
        $this->assertNotFalse($id);
        $this->assertSame('42', UserStore::$rows[$id]['externalOptions']['socialLoginTwitter'] ?? null);
        $this->assertCount(1, UserStore::$writes);
        $this->assertSame([7], UserStore::$groups[$id]);
        $this->assertSame(1, UserStore::$rows[$id]['emailVerified']);
        $this->assertSame($id, User::getSocialLoginUserId('Twitter', $this->profile('42', 'Renamed')));
    }

    public function testFailedLegacyBindingIsRefusedAndCanBeRetried()
    {
        UserStore::$failWrite = 1;
        $profile = $this->profile('333', 'John Smith', UserStore::$rows[3]['photoURL']);
        $this->assertFalse(User::getSocialLoginUserId('Facebook', $profile));
        $this->assertSame([], UserStore::$rows[3]['externalOptions']);
        UserStore::$failWrite = 0;
        $this->assertSame(3, User::getSocialLoginUserId('Facebook', $profile));
    }

    public function testLongProviderIdentifierFitsTheUsernameColumnWithoutLosingIdentity()
    {
        $identifier = str_repeat('a', 64);
        $id = User::getSocialLoginUserId('Apple', $this->profile($identifier, 'Alice'));
        $this->assertNotFalse($id);
        $this->assertLessThanOrEqual(45, strlen(UserStore::$rows[$id]['user']));
        $this->assertSame($identifier, UserStore::$rows[$id]['externalOptions']['socialLoginApple']);
        $this->assertSame($id, User::getSocialLoginUserId('Apple', $this->profile($identifier, 'Renamed')));
        $other = User::getSocialLoginUserId('Apple', $this->profile(str_repeat('a', 63) . 'b', 'Alice'));
        $this->assertNotSame($id, $other);
    }

    public function testLegacyBindingPreservesOtherOptionsAndAccountFields()
    {
        UserStore::$rows[3]['externalOptions'] = ['verified' => true, 'otherPlugin' => ['enabled' => 1]];
        UserStore::$rows[3]['canUpload'] = 1;
        $before = UserStore::$rows[3];
        $this->assertSame(3, User::getSocialLoginUserId('Facebook', $this->profile('333', 'John Smith', $before['photoURL'])));
        foreach (['password', 'isAdmin', 'status', 'channelName', 'canUpload'] as $field) {
            $this->assertSame($before[$field], UserStore::$rows[3][$field], $field);
        }
        $this->assertSame($before['externalOptions'] + ['socialLoginFacebook' => '333'], UserStore::$rows[3]['externalOptions']);
    }

    public function testPasswordlessLoginKeepsTheSelectedIdDespiteEmailAliasCollision()
    {
        $GLOBALS['advancedCustomUser']->emailMustBeUnique = true;
        UserStore::$rows[3]['user'] = 'alice@example.net';
        UserStore::$rows[4]['email'] = 'alice@example.net';
        UserStore::$rows[4]['isAdmin'] = 1;
        $id = User::getSocialLoginUserId('Facebook', $this->profile('333', 'alice@example.net', UserStore::$rows[3]['photoURL']));
        $this->assertSame(3, $id);
        $user = new User($id);
        $this->assertSame(User::USER_LOGGED, $user->login(true));
        $this->assertSame(3, $_SESSION['user']['id']);
        $this->assertSame(3, UserStore::$cookie);
        $this->assertSame([3], UserStore::$signIns);
        $this->assertSame(1, UserStore::$regenerated);
    }

    public function testPasswordlessLoginWithoutAnIdStillUsesTheExistingUsernamePath()
    {
        $this->assertSame(User::USER_LOGGED, (new User(0, 'John Smith'))->login(true));
        $this->assertSame(3, $_SESSION['user']['id']);
    }

    public function testPasswordlessLoginByIdStillRejectsInactiveAndUnverifiedAccounts()
    {
        UserStore::$rows[3]['status'] = 'i';
        $this->assertSame(User::USER_NOT_FOUND, (new User(3))->login(true));
        $this->assertArrayNotHasKey('user', $_SESSION);
        UserStore::$rows[3]['status'] = 'a';
        UserStore::$rows[3]['emailVerified'] = 0;
        $GLOBALS['advancedCustomUser']->unverifiedEmailsCanNOTLogin = true;
        $this->assertSame(User::USER_NOT_VERIFIED, (new User(3))->login(true));
        $this->assertSame([3], UserStore::$verifications);
        $this->assertNull(UserStore::$cookie);
    }

    public function testWebLoginRetainsVerificationOfAccountsCreatedByMobile()
    {
        $GLOBALS['advancedCustomUser']->unverifiedEmailsCanNOTLogin = true;
        $profile = $this->profile('42', 'Alice');
        $id = User::getSocialLoginUserId('Twitter', $profile);
        $this->assertSame(0, UserStore::$rows[$id]['emailVerified']);
        $this->assertSame($id, User::getSocialLoginUserId('Twitter', $profile, true));
        $this->assertSame(1, UserStore::$rows[$id]['emailVerified']);
        $this->assertSame(User::USER_LOGGED, (new User($id))->login(true));
    }

    public function testWebLoginRetainsVerificationWhenReattachingLegacyAccounts()
    {
        UserStore::$rows[3]['emailVerified'] = 0;
        $this->assertSame(3, User::getSocialLoginUserId('Facebook', $this->profile('333', 'John Smith', UserStore::$rows[3]['photoURL']), true));
        $this->assertSame(1, UserStore::$rows[3]['emailVerified']);
    }

    public function testFailedWebVerificationSaveDoesNotAuthorizeAnUnpersistedChange()
    {
        $profile = $this->profile('42', 'Alice');
        $id = User::getSocialLoginUserId('Twitter', $profile);
        UserStore::$failWrite = count(UserStore::$writes) + 1;
        $this->assertFalse(User::getSocialLoginUserId('Twitter', $profile, true));
        $this->assertSame(0, UserStore::$rows[$id]['emailVerified']);
    }

    public function testProfileWithoutEmailOrIdIsRefused()
    {
        $this->assertFalse(User::getSocialLoginUserId('Twitter', $this->profile('', 'alice_streamer')));
        $this->assertFalse(User::getSocialLoginUserId('', $this->profile('1', 'alice_streamer')));
    }

    public function testLoginEndpointsUseTheBinding()
    {
        foreach (['objects/login.json.php', 'plugin/MobileManager/oauth2.php'] as $file) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/' . $file);
            $this->assertStringContainsString('User::getSocialLoginUserId($_GET[\'type\'], $userProfile', $source, $file);
            $this->assertStringNotContainsString('$user = $userProfile->displayName', $source, $file);
            $this->assertStringNotContainsString('User::createUserIfNotExists(', $source, $file);
        }
    }

    public function testEmailProfilesKeepTheirAccount()
    {
        $id = User::getSocialLoginUserId('Google', $this->profile('g1', 'Alice', '', 'alice@example.net'));
        $this->assertSame('alice@example.net', UserStore::$rows[$id]['user']);
        $this->assertSame($id, User::getSocialLoginUserId('Google', $this->profile('g1', 'Alice', '', 'alice@example.net')));
    }

    /**
     * GHSA-286v-rxqx-v42r: an email the provider reports as unverified (Hybridauth leaves emailVerified empty) must not
     * select the account named after that email; a verified one, or a provider that does not report it, still does.
     */
    public function testUnverifiedProviderEmailNeverSelectsAnExistingAccount()
    {
        UserStore::$rows[2]['user'] = 'alice@example.net';
        $unverified = $this->profile('g9', 'Alice', '', 'alice@example.net');
        $unverified->emailVerified = '';
        $id = User::getSocialLoginUserId('Google', $unverified, true);
        $this->assertNotSame(2, $id);
        $this->assertSame('Google_g9', UserStore::$rows[$id]['user']);
        $this->assertSame('', UserStore::$rows[$id]['email']);
        $this->assertSame($id, User::getSocialLoginUserId('Google', $unverified, true));

        $verified = $this->profile('g2', 'Alice', '', 'alice@example.net');
        $verified->emailVerified = 'alice@example.net';
        $this->assertSame(2, User::getSocialLoginUserId('Google', $verified, true));
        // Apple and other adapters leave emailVerified null: unchanged behavior
        $this->assertSame(2, User::getSocialLoginUserId('Apple', $this->profile('a1', 'Alice', '', 'alice@example.net')));
    }
}
