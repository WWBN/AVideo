<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';

class User {

    private $id;
    private $user;
    private $name;
    private $email;
    private $password;
    private $isAdmin;
    private $canStream;
    private $canUpload;
    private $canViewChart;
    private $status;
    private $photoURL;
    private $backgroundURL;
    private $recoverPass;
    private $about;
    private $channelName;
    private $emailVerified;
    private $analyticsCode;
    private $userGroups = array();

    function __construct($id, $user = "", $password = "") {
        if (empty($id)) {
            // get the user data from user and pass
            $this->user = $user;
            if ($password !== false) {
                $this->password = $password;
            } else {
                $this->loadFromUser($user);
            }
        } else {
            // get data from id
            $this->load($id);
        }
    }

    function getEmail() {
        return $this->email;
    }

    function getUser() {
        return $this->user;
    }

    function getAbout() {
        return $this->about;
    }

    function setAbout($about) {
        $this->about = $about;
    }

    function getPassword() {
        return $this->password;
    }

    function getCanStream() {
        return $this->canStream;
    }

    function setCanStream($canStream) {
        $this->canStream = $canStream;
    }

  function getCanViewChart() {
      return $this->canViewChart;
    }

    function setCanViewChart($canViewChart) {
      $this->canViewChart = $canViewChart;
    }

    function getCanUpload() {
        return $this->canUpload;
    }

    function setCanUpload($canUpload) {
        $this->canUpload = $canUpload;
    }

    function getAnalyticsCode() {
        return $this->analyticsCode;
    }

    function setAnalyticsCode($analyticsCode) {
        preg_match("/(ua-\d{4,9}-\d{1,4})/i", $analyticsCode, $matches);
        if(!empty($matches[1])){
            $this->analyticsCode = $matches[1];
        }else{
            $this->analyticsCode = "";
        }
    }

    function getAnalytics(){
        $id = $this->getId();
        $aCode = $this->getAnalyticsCode();
        if(!empty($id) && !empty($aCode)){
            $code = "<!-- Global site tag (gtag.js) - Google Analytics From user {$id} -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id={$aCode}\"></script>
<script>
if (typeof gtag !== \"function\") {
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
}

  gtag('config', '{$aCode}');
</script>
";
        }else{
            $code = "<!-- No Analytics for this user {$id} -->";
        }
        return $code;

    }

    private function load($id) {
        $user = self::getUserDb($id);
        if (empty($user))
            return false;
        foreach ($user as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    private function loadFromUser($user) {
        $user = self::getUserDbFromUser($user);
        if (empty($user))
            return false;
        foreach ($user as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    function loadSelfUser() {
        $this->load($this->getId());
    }

    static function getId() {
        if (self::isLogged()) {
            return $_SESSION['user']['id'];
        } else {
            return false;
        }
    }

    static function getEmail_() {
        if (self::isLogged()) {
            return $_SESSION['user']['email'];
        } else {
            return false;
        }
    }

    function getBdId() {
        return $this->id;
    }

    static function updateSessionInfo() {
        if (self::isLogged()) {
            $user = self::getUserDb($_SESSION['user']['id']);
            $_SESSION['user'] = $user;
        }
    }

    static function getName() {
        if (self::isLogged()) {
            return $_SESSION['user']['name'];
        } else {
            return false;
        }
    }

    static function getUserName() {
        if (self::isLogged()) {
            return $_SESSION['user']['user'];
        } else {
            return false;
        }
    }

    static function getUserChannelName() {
        if (self::isLogged()) {

            if (empty($_SESSION['user']['channelName'])) {
                $_SESSION['user']['channelName'] = uniqid();
                $user = new User(User::getId());
                $user->setChannelName($_SESSION['user']['channelName']);
                $user->save();
            }

            return $_SESSION['user']['channelName'];
        } else {
            return false;
        }
    }

    /**
     * return an name to identify the user
     * @return String
     */
    static function getNameIdentification() {
        $advancedCustom = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
        if (self::isLogged()) {
            if (!empty(self::getName()) && empty($advancedCustom->doNotIndentifyByName)) {
                return self::getName();
            }
            if (!empty(self::getMail()) && empty($advancedCustom->doNotIndentifyByEmail)) {
                return self::getMail();
            }
            if (!empty(self::getUserName()) && empty($advancedCustom->doNotIndentifyByUserName)) {
                return self::getUserName();
            }
        }
        return __("Unknown User");
    }

    /**
     * return an name to identify the user from database
     * @return String
     */
    function getNameIdentificationBd() {
        $advancedCustom = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
        if (!empty($this->name) && empty($advancedCustom->doNotIndentifyByName)) {
            return $this->name;
        }
        if (!empty($this->email) && empty($advancedCustom->doNotIndentifyByEmail)) {
            return $this->email;
        }
        if (!empty($this->user) && empty($advancedCustom->doNotIndentifyByUserName)) {
            return $this->user;
        }
        return __("Unknown User");
    }

    static function getNameIdentificationById($id = "") {
        if (!empty($id)) {
            $user = new User($id);
            return $user->getNameIdentificationBd();
        }
        return __("Unknown User");
    }

    static function getUserPass() {
        if (self::isLogged()) {
            return $_SESSION['user']['password'];
        } else {
            return false;
        }
    }

    function _getName() {
        return $this->name;
    }

    static function getPhoto($id = "") {
        global $global;
        if (!empty($id)) {
            $user = self::findById($id);
            if (!empty($user)) {
                $photo = $user['photoURL'];
            }
        } elseif (self::isLogged()) {
            $photo = $_SESSION['user']['photoURL'];
        }
        if (!empty($photo) && preg_match("/videos\/userPhoto\/.*/", $photo)) {
            if (file_exists($global['systemRootPath'] . $photo)) {
                $photo = $global['webSiteRootURL'] . $photo;
            } else {
                $photo = "";
            }
        }
        if (empty($photo)) {
            $photo = $global['webSiteRootURL'] . "view/img/userSilhouette.jpg";
        }
        return $photo;
    }

    function getPhotoDB() {
        return self::getPhoto($this->id);
    }

    static function getBackground($id = "") {
        global $global;
        if (!empty($id)) {
            $user = self::findById($id);
            if (!empty($user)) {
                $photo = $user['backgroundURL'];
            }
        } elseif (self::isLogged()) {
            $photo = $_SESSION['user']['backgroundURL'];
        }
        if (!empty($photo) && preg_match("/videos\/userPhoto\/.*/", $photo)) {
            if (file_exists($global['systemRootPath'] . $photo)) {
                $photo = $global['webSiteRootURL'] . $photo;
            } else {
                $photo = "";
            }
        }
        if (empty($photo)) {
            $photo = $global['webSiteRootURL'] . "view/img/background.png";
        }
        return $photo;
    }

    static function getMail() {
        if (self::isLogged()) {
            return $_SESSION['user']['email'];
        } else {
            return false;
        }
    }

    function save($updateUserGroups = false) {
        global $global, $config;

        if($config->currentVersionLowerThen('5.6')){
            // they dont have analytics code
            return false;
        }

        if (empty($this->user) || empty($this->password)) {
            echo "u:" . $this->user . "|p:" . strlen($this->password);
            die('Error : ' . __("You need a user and passsword to register"));
        }
        if (empty($this->isAdmin)) {
            $this->isAdmin = "false";
        }
        if (empty($this->canStream)) {
            if (empty($this->id)) { // it is a new user
                $obj = YouPHPTubePlugin::getObjectDataIfEnabled('CustomizeAdvanced');
                if (empty($obj->newUsersCanStream)) {
                    $this->canStream = "false";
                } else {
                    $this->canStream = "true";
                }
            } else {
                $this->canStream = "false";
            }
        }
        if (empty($this->canUpload)) {
            $this->canUpload = "false";
        }
        if (empty($this->status)) {
            $this->status = 'a';
        }
        if(empty($this->emailVerified))
            $this->emailVerified = "false";

        if (empty($this->channelName)) {
            $this->channelName = uniqid();
        } else {
            $channelOwner = static::getChannelOwner($this->channelName);
            if (!empty($channelOwner)) { // if the channel name exists and it is not from this user, rename the channel name
                if (empty($this->id) || $channelOwner['id'] != $this->id) {
                    $this->channelName .= uniqid();
                }
            }
        }

        $this->user = $global['mysqli']->real_escape_string($this->user);
        $this->password = $global['mysqli']->real_escape_string($this->password);
        $this->name = $global['mysqli']->real_escape_string($this->name);
        $this->status = $global['mysqli']->real_escape_string($this->status);
        $this->about = $global['mysqli']->real_escape_string($this->about);
        $this->channelName = $global['mysqli']->real_escape_string($this->channelName);
        if (empty($this->channelName)) {
            $this->channelName = uniqid();
        }

        if(!isset($this->canViewChart)){
          //$this->canViewChart = 0;
        }

        if (!empty($this->id)) {
            $sql = "UPDATE users SET user = '{$this->user}', password = '{$this->password}', "
                    . "email = '{$this->email}', name = '{$this->name}', isAdmin = {$this->isAdmin},"
                    . "canStream = {$this->canStream},canUpload = {$this->canUpload},canViewChart = {$this->canViewChart}, status = '{$this->status}', "
                    . "photoURL = '{$this->photoURL}', backgroundURL = '{$this->backgroundURL}', "
                    . "recoverPass = '{$this->recoverPass}', about = '{$this->about}', "
                    . " channelName = '{$this->channelName}', emailVerified = {$this->emailVerified} , analyticsCode = '{$this->analyticsCode}' , modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO users (user, password, email, name, isAdmin, canStream, canUpload, canViewChart, status,photoURL,recoverPass, created, modified, channelName, analyticsCode) VALUES ('{$this->user}','{$this->password}','{$this->email}','{$this->name}',{$this->isAdmin}, {$this->canStream}, {$this->canUpload}, {$this->canViewChart}, '{$this->status}', '{$this->photoURL}', '{$this->recoverPass}', now(), now(), '{$this->channelName}', '{$this->analyticsCode}')";
        }
        //echo $sql;
        $insert_row = sqlDAL::writeSql($sql);

        if ($insert_row) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
                $obj = YouPHPTubePlugin::getObjectDataIfEnabled('CustomizeAdvanced');
                if (!empty($obj->unverifiedEmailsCanNOTLogin)) {
                    self::sendVerificationLink($id);
                }
            } else {
                $id = $this->id;
            }
            if ($updateUserGroups) {
                require_once $global['systemRootPath'] . 'objects/userGroups.php';
                // update the user groups
                UserGroups::updateUserGroups($id, $this->userGroups);
            }
            return $id;
        } else {
            if ($global['mysqli']->error == "Duplicate entry 'admin' for key 'user_UNIQUE'") {
                echo '{"error":"' . __("User name already exists") . '"}';
                exit;
            }

            die(' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    static function getChannelOwner($channelName) {
        global $global;
        $channelName = $global['mysqli']->real_escape_string($channelName);
        $sql = "SELECT * FROM users WHERE channelName = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($channelName));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $user = $result;
        } else {
            $user = false;
        }
        return $user;
    }

    function delete() {
        if (!self::isAdmin()) {
            return false;
        }
        // cannot delete yourself
        if (self::getId() === $this->id) {
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM users WHERE id = ?";
        } else {
            return false;
        }
        return sqlDAL::writeSql($sql, "i", array($this->id));
    }

    const USER_LOGGED = 0;
    const USER_NOT_VERIFIED = 1;
    const USER_NOT_FOUND = 2;

    function login($noPass = false, $encodedPass = false) {
        $obj = YouPHPTubePlugin::getObjectDataIfEnabled('CustomizeAdvanced');
        if ($noPass) {
            $user = $this->find($this->user, false, true);
        } else {
            $user = $this->find($this->user, $this->password, true, $encodedPass);
        }
        // if user is not verified
        if (!empty($user) && empty($user['isAmin']) && empty($user['emailVerified']) && !empty($obj->unverifiedEmailsCanNOTLogin)) {
            unset($_SESSION['user']);
            self::sendVerificationLink($user['id']);
            return self::USER_NOT_VERIFIED;
        } else if ($user) {
            $_SESSION['user'] = $user;
            $this->setLastLogin($_SESSION['user']['id']);
            return self::USER_LOGGED;
        } else {
            unset($_SESSION['user']);
            return self::USER_NOT_FOUND;
        }
    }

    private function setLastLogin($user_id) {
        global $global;
        if (empty($user_id)) {
            die('Error : setLastLogin ');
        }
        $sql = "UPDATE users SET lastLogin = now(), modified = now() WHERE id = ?";
        return sqlDAL::writeSql($sql, "i", array($user_id));
    }

    static function logoff() {
        unset($_SESSION['user']);
    }

    static function isLogged() {
        return !empty($_SESSION['user']['id']);
    }

    static function isVerified() {
        return !empty($_SESSION['user']['emailVerified']);
    }

    static function isAdmin() {
        return !empty($_SESSION['user']['isAdmin']);
    }

    static function canStream() {
        return !empty($_SESSION['user']['isAdmin']) || !empty($_SESSION['user']['canStream']);
    }

    function thisUserCanStream() {
        return !empty($this->isAdmin) || !empty($this->canStream);
    }

    private function find($user, $pass, $mustBeactive = false, $encodedPass = false) {
        global $global;
        $formats = "";
        $values = array();
        $user = $global['mysqli']->real_escape_string($user);
        $sql = "SELECT * FROM users WHERE user = '$user' ";

        if ($mustBeactive) {
            $sql .= " AND status = 'a' ";
        }
        if ($pass !== false) {
            if (!$encodedPass || $encodedPass === 'false') {
                $pass = md5($pass);
            }
            $sql .= " AND password = ? ";
            $formats = "s";
            $values = array($pass);
        }
        $sql .= " LIMIT 1";
        $res = sqlDAL::readSql($sql, $formats, $values);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $user = $result;
        } else {
            $user = false;
        }
        return $user;
    }

    static private function findById($id) {
        global $global;

        $sql = "SELECT * FROM users WHERE id = ?  LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", array($id));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $user = $result;
        } else {
            $user = false;
        }
        return $user;
    }

    static function findByEmail($email) {
        global $global;

        $sql = "SELECT * FROM users WHERE email = ?  LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($email));
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $user = $result;
        } else {
            $user = false;
        }
        return $user;
    }

    static private function getUserDb($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM users WHERE  id = ? LIMIT 1;";
        $res = sqlDAL::readSql($sql,"i",array($id));
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($user != false) {
            return $user;
        }
        return false;
    }

    static private function getUserDbFromUser($user) {
        global $global;
        $sql = "SELECT * FROM users WHERE user = ? LIMIT 1";
        $res = sqlDAL::readSql($sql,"s",array($user));
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($user != false) {
            return $user;
        }
        return false;
    }

    function setUser($user) {
        $this->user = strip_tags($user);
    }

    function setName($name) {
        $this->name = strip_tags($name);
    }

    function setEmail($email) {
        $this->email = strip_tags($email);
    }

    function setPassword($password) {
        if (!empty($password)) {
            $this->password = md5($password);
        }
    }

    function setIsAdmin($isAdmin) {
        if (empty($isAdmin) || $isAdmin === "false") {
            $isAdmin = "false";
        } else {
            $isAdmin = "true";
        }
        $this->isAdmin = $isAdmin;
    }

    function setStatus($status) {
        $this->status = strip_tags($status);
    }

    function getPhotoURL() {
        return $this->photoURL;
    }

    function setPhotoURL($photoURL) {
        $this->photoURL = strip_tags($photoURL);
    }

    static function getAllUsers($ignoreAdmin = false) {
        if (!self::isAdmin() && !$ignoreAdmin) {
            return false;
        }
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT * FROM users WHERE 1=1 ";

        $sql .= BootGrid::getSqlFromPost(array('name', 'email', 'user'));
        $user = array();
        require_once $global['systemRootPath'] . 'objects/userGroups.php';
        $res = sqlDAL::readSql($sql . ";");
        $downloadedArray = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            foreach ($downloadedArray as $row) {
                $row['groups'] = UserGroups::getUserGroups($row['id']);
                $row['identification'] = self::getNameIdentificationById($row['id']);
                $row['photo'] = self::getPhoto();
                $row['background'] = self::getBackground();
                $row['tags'] = self::getTags($row['id']);
                $row['name'] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $row['name']);
                $row['isEmailVerified']=$row['emailVerified'];
                unset($row['password']);
                unset($row['recoverPass']);
                $user[] = $row;
            }
        } else {
            $user = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        return $user;
    }

    static function getTotalUsers($ignoreAdmin = false) {
        if (!self::isAdmin() && !$ignoreAdmin) {
            return false;
        }
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT id FROM users WHERE 1=1  ";

        $sql .= BootGrid::getSqlSearchFromPost(array('name', 'email', 'user'));

        $res = sqlDAL::readSql($sql);
        $result = sqlDal::num_rows($res);
        sqlDAL::close($res);


        return $result;
    }

    static function userExists($user) {
        global $global;
        $user = $global['mysqli']->real_escape_string($user);
        $sql = "SELECT * FROM users WHERE user = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", array($user));
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);

        if ($user != false) {
            return $user['id'];
        } else {
            return false;
        }
    }

    static function idExists($users_id) {
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", array($users_id));
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($user != false) {
            return $user['id'];
        } else {
            return false;
        }
    }

    static function createUserIfNotExists($user, $pass, $name, $email, $photoURL, $isAdmin = false) {
        global $global;
        $user = $global['mysqli']->real_escape_string($user);
        if (!$userId = self::userExists($user)) {
            if (empty($pass)) {
                $pass = rand();
            }
            $pass = md5($pass);
            $userObject = new User(0, $user, $pass);
            $userObject->setEmail($email);
            $userObject->setName($name);
            $userObject->setIsAdmin($isAdmin);
            $userObject->setPhotoURL($photoURL);
            $userId = $userObject->save();
            return $userId;
        }
        return $userId;
    }

    function getRecoverPass() {
        return $this->recoverPass;
    }

    function setRecoverPass($recoverPass) {
        $this->recoverPass = $recoverPass;
    }

    static function canUpload() {
        global $global, $config;
        if ($config->getAuthCanUploadVideos()) {
            return self::isLogged();
        }
        if (self::isLogged() && !empty($_SESSION['user']['canUpload'])) {
            return true;
        }
        return self::isAdmin();
    }

    static function canViewChart() {
        global $global, $config;
        if (self::isLogged() && !empty($_SESSION['user']['canViewChart'])) {
            return true;
        }
        return self::isAdmin();
    }


    static function canComment() {
        global $global, $config;
        if ($config->getAuthCanComment()) {
            return self::isLogged();
        }
        return self::isAdmin();
    }

    static function canSeeCommentTextarea() {
        global $global, $config;
        if (!$config->getAuthCanComment()) {
            if (!self::isAdmin()) {
                return false;
            }
        }
        return true;
    }

    function getUserGroups() {
        return $this->userGroups;
    }

    function setUserGroups($userGroups) {
        if (is_array($userGroups)) {
            $this->userGroups = $userGroups;
        }
    }

    function getIsAdmin() {
        return $this->isAdmin;
    }

    function getStatus() {
        return $this->status;
    }

    /**
     *
     * @param type $user_id
     * text
     * label Default Primary Success Info Warning Danger
     */
    static function getTags($user_id) {
        $user = new User($user_id);
        $tags = array();
        if ($user->getIsAdmin()) {
            $obj = new stdClass();
            $obj->type = "info";
            $obj->text = __("Admin");
            $tags[] = $obj;
        } else {
            $obj = new stdClass();
            $obj->type = "default";
            $obj->text = __("Regular User");
            $tags[] = $obj;
        }

        if ($user->getStatus() == "a") {
            $obj = new stdClass();
            $obj->type = "success";
            $obj->text = __("Active");
            $tags[] = $obj;
        } else {
            $obj = new stdClass();
            $obj->type = "danger";
            $obj->text = __("Inactive");
            $tags[] = $obj;
        }
        if($user->getEmailVerified())
        {
            $obj = new stdClass();
            $obj->type="success";
            $obj->text = __("E-mail Verified");
            $tags[] = $obj;
        }else
        {
            $obj = new stdClass();
            $obj->type="warning";
            $obj->text = __("E-mail Not Verified");
            $tags[] = $obj;
        }
        global $global;
        if (!empty($global['systemRootPath'])) {
            require_once $global['systemRootPath'] . 'objects/userGroups.php';
        } else {
            require_once 'userGroups.php';
        }
        $groups = UserGroups::getUserGroups($user_id);
        foreach ($groups as $value) {
            $obj = new stdClass();
            $obj->type = "warning";
            $obj->text = $value['group_name'];
            $tags[] = $obj;
        }

        return $tags;
    }

    function getBackgroundURL() {
        if (empty($this->backgroundURL)) {
            $this->backgroundURL = "view/img/background.png";
        }
        return $this->backgroundURL;
    }

    function setBackgroundURL($backgroundURL) {
        $this->backgroundURL = strip_tags($backgroundURL);
    }

    function getChannelName() {
        if(empty($this->channelName)){
            $this->channelName = uniqid();
            $this->save();
        }
        return $this->channelName;
    }

    function getEmailVerified() {
        return $this->emailVerified;
    }

    /**
     *
     * @param type $channelName
     * @return boolean return true is is unique
     */
    function setChannelName($channelName) {
        $channelName = trim(preg_replace("/[^0-9A-Z_ -]/i", "", $channelName));
        $user = static::getChannelOwner($channelName);
        if (!empty($user)) { // if the channel name exists and it is not from this user, rename the channel name
            if (empty($this->id) || $user['id'] != $this->id) {
                return false;
            }
        }
        $this->channelName = $channelName;
        return true;
    }

    function setEmailVerified($emailVerified) {
        $this->emailVerified = $emailVerified;
    }

    static function getChannelLink($users_id = 0) {
        global $global, $config;
        if($config->currentVersionLowerThen('5.3')){
            return "{$global['webSiteRootURL']}channel/UpDateYourVersion";
        }
        if (empty($users_id)) {
            $users_id = self::getId();
        }
        $user = new User($users_id);
        if (empty($user)) {
            return false;
        }
        if (empty($user->getChannelName())) {
            $name = $user->getBdId();
        } else {
            $name = $user->getChannelName();
        }
        $link = "{$global['webSiteRootURL']}channel/" . urlencode($name);
        return $link;
    }

    static function sendVerificationLink($users_id) {
        global $global, $config;
        $user = new User($users_id);
        $code = urlencode(static::createVerificationCode($users_id));
        require_once $global['systemRootPath'] . 'objects/PHPMailer/PHPMailerAutoload.php';
        //Create a new PHPMailer instance
        $contactEmail = $config->getContactEmail();
        $webSiteTitle = $config->getWebSiteTitle();
        $email = $user->getEmail();
        try {
            $mail = new PHPMailer;
            setSiteSendMessage($mail);
            //$mail->SMTPDebug = 4;
            //Set who the message is to be sent from
            $mail->setFrom($contactEmail, $webSiteTitle);
            //Set who the message is to be sent to
            $mail->addAddress($email);
            //Set the subject line
            $mail->Subject = __('Please Verify Your E-mail ') . $webSiteTitle;

            $msg = sprintf(__("Hi %s"), $user->getNameIdentificationBd());
            $msg .= "<br><br>" . __("Just a quick note to say a big welcome and an even bigger thank you for registering.");

            $msg .= "<br><br>" . sprintf(__("Cheers, %s Team."), $webSiteTitle);

            $msg .= "<br><br>" . sprintf(__("You are just one click away from starting your journey with %s!"), $webSiteTitle);
            $msg .= "<br><br>" . sprintf(__("All you need to do is to verify your e-mail by clicking the link below"));
            $msg .= "<br><br>" . " <a href='{$global['webSiteRootURL']}objects/userVerifyEmail.php?code={$code}'>" . __("Verify") . "</a>";

            $mail->msgHTML($msg);
            $resp = $mail->send();
            if(!$resp){
                error_log("sendVerificationLink Error Info: {$mail->ErrorInfo}");
            }
            return $resp;
        } catch (phpmailerException $e) {
            error_log($e->errorMessage()); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            error_log($e->getMessage()); //Boring error messages from anything else!
        }
        return false;
    }

    static function verifyCode($code) {
        global $global;
        $obj = static::decodeVerificationCode($code);
        $salt = hash('sha256', $global['salt']);
        if ($salt !== $obj->salt) {
            return false;
        }
        $user = new User($obj->users_id);
        $recoverPass = $user->getRecoverPass();
        if ($recoverPass == $obj->recoverPass) {
            $user->setEmailVerified(1);
            return $user->save();
        }
        return false;
    }

    static function createVerificationCode($users_id) {
        global $global;
        $obj = new stdClass();
        $obj->users_id = $users_id;
        $obj->recoverPass = uniqid();
        $obj->salt = hash('sha256', $global['salt']);

        $user = new User($users_id);
        $user->setRecoverPass($obj->recoverPass);
        $user->save();

        return base64_encode(json_encode($obj));
    }

    static function decodeVerificationCode($code) {
        $obj = json_decode(base64_decode($code));
        return $obj;
    }

}
