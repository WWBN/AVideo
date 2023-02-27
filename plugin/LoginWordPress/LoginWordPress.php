<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LoginWordPress extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$LOGIN,
        );
    }

    public function getDescription() {
        global $global;
        $obj = $this->getLogin();
        $name = $obj[0]->type;
        $str = "Login with {$name} OAuth Integration";
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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
        $resp = self::auth($user, $pass);
        if (!empty($resp) && !empty($resp->id)) {
            _error_log("LoginWordPresslogin: success {$user}");
            // create user if need     
            $name = $user;
            $photoURL = end($resp->avatar_urls);
            $email = $user;
            if (!$obj->customWordPressSiteSavePassword) {
                $pass = rand();
            }
            $users_id = User::createUserIfNotExists($user, $pass, $name, $email, $photoURL, false, true);
            // login
            if (!empty($obj->autoAddNewUsersOnUserGroup)) {
                UserGroups::updateUserGroups($users_id, array($obj->autoAddNewUsersOnUserGroup->value), true, true);
            }

            $userObject = new User(0, $user, $pass);
            $userObject->login(true, false, true);
            return User::USER_LOGGED;
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

}
