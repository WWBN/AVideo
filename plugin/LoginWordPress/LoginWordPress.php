<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LoginWordPress extends PluginAbstract {

    const BINDING_KEY = 'loginWordPressUser';

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$LOGIN,
            PluginTags::$DEPRECATED,
        );
    }

    public function getDescription() {
        global $global;
        $obj = $this->getLogin();
        $name = !empty($obj[0]->type) ? $obj[0]->type : 'WordPress';
        $str = "<strong class='text-danger'>Deprecated: this plugin will be removed in a future version.</strong><br>";
        $str .= "Login with {$name} OAuth Integration";
        $str .= "<br><a href='{$obj[0]->linkToDevelopersPage}'>Get {$name} ID and Key</a>"
                . "<br>Valid OAuth redirect URIs: <strong>{$global['webSiteRootURL']}objects/login.json.php?type=$name</strong>"
                . "<br>For mobile a Valid OAuth redirect URIs: <strong>{$global['webSiteRootURL']}plugin/MobileManager/oauth2.php?type=$name</strong>";
        return $str;
    }

    public function getName() {
        return "LoginWordPress";
    }

    public function getUUID() {
        return "wp-8c31-4f15-a355-48715fac13f3";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();

        $obj->customWordPressSite = '';
        self::addDataObjectHelper('customWordPressSite', 'My Custom WP Site', "This will use your personal WP API to login with your users, this option does not require the ID and Key, this option must be your site URL, I.E. https://mywordpresssite.com/");
        $obj->customWordPressSiteSavePassword = true;
        self::addDataObjectHelper('customWordPressSiteSavePassword', 'Save the password on AVideo the database', "This will use your personal WP API to login with your users, this option does not require the ID and Key, this option must be your site URL, I.E. https://mywordpresssite.com/");
        $obj->customWordPressSiteIfLoginFailTryDatabase = false;
        self::addDataObjectHelper('customWordPressSiteIfLoginFailTryDatabase', 'Check the database if the wordpress login fail', "This will use your personal WP API to login with your users, this option does not require the ID and Key, this option must be your site URL, I.E. https://mywordpresssite.com/");

        $obj->customWordPressSiteForgotMyPasswordURL = '';
        $obj->customWordPressSiteSignUpURL = '';

        $o = new stdClass();
        $o->type = array(0 => '-- ' . __("None")) + UserGroups::getAllUsersGroupsArray();
        $o->value = 0;
        $obj->autoAddNewUsersOnUserGroup = $o;
        self::addDataObjectHelper('autoAddNewUsersOnUserGroup', 'Auto add new users on UserGroup', 'When a new user is created with the your WordPress site, this user will be auto added on the selected user group');

        $obj->globalWordpressLogin = true;
        self::addDataObjectHelper('globalWordpressLogin', 'Use the global Wordpress login', "This will use the global WordPress OAUTH2 to login, this option requires the ID and Key");
        $obj->id = "";
        $obj->key = "";
        $obj->buttonLabel = "";
        return $obj;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getLogin() {

        global $global;
        $objWP = AVideoPlugin::getDataObject('LoginWordPress');

        $logins = array();
        if ($objWP->globalWordpressLogin) {
            $obj = new stdClass();
            $obj->class = "btn btn-primary btn-block";
            $obj->icon = "fab fa-wordpress";
            $obj->type = "WordPress";
            $obj->linkToDevelopersPage = "https://developer.wordpress.com/apps/";

            $logins[] = $obj;
        }
        if (!empty($objWP->customWordPressSite)) {
            $logins[] = $global['systemRootPath'] . 'plugin/LoginWordPress/view/loginForm.php';
        }
        //var_dump($logins);exit;
        return $logins;
    }

    private static function auth($user, $pass) {
        if (empty($user) || empty($pass)) {
            _error_log("auth: WP EMPTY fail: Empty User and pass are not allowed");
            return User::USER_NOT_FOUND;
        }

        $obj = AVideoPlugin::getDataObject('LoginWordPress');

        $base64 = base64_encode($user . ':' . $pass);

        $wpSite = addLastSlash($obj->customWordPressSite);
        set_time_limit(0);
        $WPAPI = $wpSite . 'wp-json/wp/v2/users/me';

        $ch = curl_init($WPAPI);
        $CURLOPT_HTTPHEADER = array(
            "Authorization: Basic {$base64}",
            'Content-Type: application/json'
        );

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600); //timeout in seconds

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $CURLOPT_HTTPHEADER);
        // Off by default (self-signed WordPress sites); see shouldVerifySSLCertificates().
        $verifySSL = shouldVerifySSLCertificates();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : 0);

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        _error_log("LoginWordPresslogin: {$WPAPI} ");
        if(empty($data)){
            _error_log("LoginWordPresslogin: auth empty response ".json_encode($info));
        }else{
            _error_log("LoginWordPresslogin: auth response ".$data);
        }
        $dataJSON = json_decode($data);
        return $dataJSON;
    }

    static function login($user, $pass) {
        global $global;
        if (!User::checkLoginAttempts()) {
            return User::CAPTCHA_ERROR;
        }

        $obj = AVideoPlugin::getObjectData("LoginWordPress");
        $resp = self::getBindingValue(1) !== false ? self::auth($user, $pass) : null;
        if (!empty($resp) && !empty($resp->id)) {
            _error_log("LoginWordPresslogin: success {$user}");
            $users_id = self::getBoundUserId($user, $pass, $resp, $obj);
            if (empty($users_id)) {
                return User::USER_NOT_FOUND;
            }
            if (User::isLogged() && User::getId() != $users_id) {
                return User::USER_NOT_FOUND;
            }
            // login
            if (!empty($obj->autoAddNewUsersOnUserGroup)) {
                UserGroups::updateUserGroups($users_id, array($obj->autoAddNewUsersOnUserGroup->value), true, true);
            }
            if (User::isLogged() && User::getId() == $users_id) {
                return User::USER_LOGGED;
            }
            $userObject = new User($users_id);
            return $userObject->login(true, false, true);
        } else if ($obj->customWordPressSiteIfLoginFailTryDatabase) {
            _error_log("LoginWordPresslogin: fail try database {$user}");
            $user = new User(0, $user, $pass);
            $response = $user->login(false, false, true);
            _error_log("LoginWordPresslogin: fail try database response: " . json_encode($response));
            return $response;
        } else {
            _error_log("LoginWordPresslogin: not found " . json_encode($resp));
            return User::USER_NOT_FOUND;
        }
    }

    /**
     * The value stored in users.externalOptions that ties an AVideo account to one user of the configured WordPress site.
     */
    static function getBindingValue($wpUserId) {
        $obj = AVideoPlugin::getObjectData("LoginWordPress");
        $site = trim((string) $obj->customWordPressSite);
        $parts = parse_url($site);
        $id = filter_var($wpUserId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (empty($parts['host']) || empty($parts['scheme']) ||
            !in_array(strtolower($parts['scheme']), ['http', 'https'], true) || $id === false) {
            return false;
        }
        $scheme = strtolower($parts['scheme']);
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $port = $port === ($scheme === 'https' ? 443 : 80) ? '' : ':' . $port;
        return $scheme . '://' . strtolower($parts['host']) . $port . rtrim($parts['path'] ?? '', '/') . ':' . $id;
    }

    /**
     * Returns the AVideo account for a user the WordPress site authenticated, or false.
     * The WordPress username never selects an existing account by itself (GHSA-9wm7-g9h5-92xq): anyone who can
     * register on the WordPress site chooses that name. An account is used only when it is bound to this WordPress
     * user id, or, for an account not bound yet (created before the binding existed, or locally), when the same
     * password is also the account's AVideo password. Otherwise a new account is created only if the name is free.
     */
    private static function getBoundUserId($user, $pass, $resp, $obj) {
        $bindingKey = self::BINDING_KEY;
        $binding = self::getBindingValue($resp->id);
        if ($binding === false) {
            return false;
        }

        $res = sqlDAL::readSql('SELECT id FROM users WHERE LOCATE(?, FROM_BASE64(externalOptions)) > 0', 's', [serialize($bindingKey) . serialize($binding)], true);
        if ($res === false) {
            return false;
        }
        while ($row = sqlDAL::fetchAssoc($res)) {
            $userObj = new User($row['id']);
            if ((string) $userObj->getExternalOption($bindingKey) === $binding) {
                sqlDAL::close($res);
                return intval($row['id']);
            }
        }
        sqlDAL::close($res);

        $existing_id = User::userExists($user);
        if ($existing_id) {
            // An existing account not bound to this WordPress user: the person must also know its AVideo password.
            // User::login() returns early for a session that is already logged in, so only bind that session's own account.
            if (User::isLogged() && User::getId() != $existing_id) {
                _error_log("LoginWordPresslogin: refused, logged in as another account than {$user}", AVideoLog::$SECURITY);
                return false;
            }
            $accountMutationLock = UserAccountMutationLock::acquire($existing_id);
            if (!$accountMutationLock) {
                return false;
            }
            $userObj = new User($existing_id);
            if ($userObj->getExternalOption($bindingKey) !== null) {
                return false;
            }
            $userObject = new User(0, $user, $pass);
            // Verify without creating a session or running sign-in hooks, and require the selected username's ID.
            if ($userObject->getVerifiedCredentialsUserId(false, true) !== intval($existing_id)) {
                _error_log("LoginWordPresslogin: refused, the AVideo account {$user} is not bound to WordPress user {$binding}", AVideoLog::$SECURITY);
                return false;
            }
            // Reload after credential verification, which can repair a legacy plaintext password.
            $userObj = new User($existing_id);
            if (!$userObj->addExternalOptions($bindingKey, $binding)) {
                return false;
            }
            _error_log("LoginWordPresslogin: account {$existing_id} bound to WordPress user {$binding}");
            return intval($existing_id);
        }

        if (User::isLogged()) {
            return false;
        }
        $name = $user;
        $avatars = !empty($resp->avatar_urls) ? (array) $resp->avatar_urls : [];
        $photoURL = !empty($avatars) ? end($avatars) : '';
        $email = $user;
        if (!$obj->customWordPressSiteSavePassword) {
            $pass = bin2hex(random_bytes(32)); // SECURITY: cryptographically random, never typed by the user
        }
        // Persist the binding in the INSERT. If the name was taken meanwhile, the check below refuses that account.
        $users_id = User::createUserIfNotExists($user, $pass, $name, $email, $photoURL, false, true, [$bindingKey => $binding], true);
        if (empty($users_id)) {
            return false;
        }
        $userObj = new User($users_id);
        if ((string) $userObj->getExternalOption($bindingKey) !== $binding) {
            _error_log("LoginWordPresslogin: refused, the AVideo account {$user} was created by someone else", AVideoLog::$SECURITY);
            return false;
        }
        return $users_id;
    }

}
