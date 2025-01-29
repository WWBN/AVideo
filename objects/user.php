<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
    require_once $global['systemRootPath'] . 'videos/configuration.php';
}


require_once $global['systemRootPath'] . 'objects/bootGrid.php';

require_once $global['systemRootPath'] . 'objects/userGroups.php';

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';



/**
 *
 * @var array $global
 * @var object $global['mysqli']
 */
class User
{

    protected $properties = [];
    private $id;
    private $user;
    private $name;
    private $email;
    private $password;
    private $isAdmin;
    private $canStream;
    private $canUpload;
    private $canCreateMeet;
    private $canViewChart;
    private $status;
    private $photoURL;
    private $backgroundURL;
    private $recoverPass;
    private $about;
    private $channelName;
    private $emailVerified;
    private $analyticsCode;
    private $externalOptions;
    private $userGroups = [];
    private $first_name;
    private $last_name;
    private $address;
    private $zip_code;
    private $country;
    private $region;
    private $city;
    private $donationLink;
    private $modified;
    private $extra_info;
    private $phone;
    private $is_company;
    private $birth_date;
    public static $DOCUMENT_IMAGE_TYPE = "Document Image";
    public static $channel_artTV = 'tv';
    public static $channel_artDesktopMax = 'desktop_max';
    public static $channel_artTablet = 'tablet';
    public static $channel_artDesktopMin = 'desktop_min';
    public static $channel_art = [
        'TV' => ['tv', 2550, 1440],
        'DesktopMax' => ['desktop_max', 2550, 423],
        'tablet' => ['tablet', 1855, 423],
        'DesktopMin' => ['desktop_min', 1546, 423]
    ];
    public static $is_company_status_NOTCOMPANY = 0;
    public static $is_company_status_ISACOMPANY = 1;
    public static $is_company_status_WAITINGAPPROVAL = 2;
    public static $is_company_status = [0 => 'Not a Company', 1 => 'Active Company', 2 => 'Company waiting for approval'];

    public function __construct($id, $user = "", $password = "")
    {
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

    function getIs_company(): int
    {
        return intval($this->is_company);
    }

    function setIs_company($is_company): void
    {
        if ($is_company === 'true') {
            $is_company = self::$is_company_status_ISACOMPANY;
        }
        if (empty($is_company) || $is_company === "false") {
            $is_company = self::$is_company_status_NOTCOMPANY;
        } else {
            if (Permissions::canAdminUsers()) {
                $is_company = intval($is_company);
            } else {
                // only admin can approve a company
                $is_company = self::$is_company_status_WAITINGAPPROVAL;
            }
        }
        $this->is_company = $is_company;
    }

    function getPhone()
    {
        return $this->phone;
    }

    function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    function getBirth_date()
    {
        return $this->birth_date;
    }

    function setBirth_date($birth_date): void
    {
        if (_empty($birth_date)) {
            $this->birth_date = 'NULL';
        } else {
            $time = strtotime($birth_date);
            $this->birth_date = date('Y/m/d', $time);
        }
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getAbout()
    {
        return str_replace(['\\\\\\\n'], ["\n"], $this->about);
    }

    public function setAbout($about)
    {
        $this->about = strip_specific_tags(xss_esc($about));
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getCanStream()
    {
        return $this->canStream;
    }

    public function setCanStream($canStream)
    {
        $this->canStream = (empty($canStream) || strtolower($canStream) === 'false') ? 0 : 1;
    }

    public function getCanViewChart()
    {
        return $this->canViewChart;
    }

    public function setCanViewChart($canViewChart)
    {
        $this->canViewChart = (empty($canViewChart) || strtolower($canViewChart) === 'false') ? 0 : 1;
    }

    public function getCanCreateMeet()
    {
        return $this->canCreateMeet;
    }

    public function setCanCreateMeet($canCreateMeet)
    {
        $this->canCreateMeet = _empty($canCreateMeet) ? 0 : 1;
    }

    public function getCanUpload()
    {
        return $this->canUpload;
    }

    public function setCanUpload($canUpload)
    {
        $this->canUpload = (empty($canUpload) || strtolower($canUpload) === 'false') ? 0 : 1;
    }

    public function getAnalyticsCode()
    {
        return $this->analyticsCode;
    }

    public function setAnalyticsCode($analyticsCode)
    {
        preg_match("/(ua-\d{4,9}-\d{1,4})/i", $analyticsCode, $matches);
        if (!empty($matches[1])) {
            $this->analyticsCode = $matches[1];
        } else {
            $this->analyticsCode = '';
        }
    }

    public function getAnalytics()
    {
        $id = $this->getId();
        $aCode = $this->getAnalyticsCode();
        if (!empty($id) && !empty($aCode)) {
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
        } else {
            $code = "<!-- No Analytics for this user {$id} -->";
        }
        return $code;
    }

    public function addExternalOptions($id, $value)
    {
        $eo = User::decodeExternalOption($this->externalOptions);
        if (!is_array($eo)) {
            $eo = [];
        }
        $eo[$id] = $value;
        $this->setExternalOptions($eo);
        return $this->save();
    }

    public function removeExternalOptions($id)
    {
        $eo = User::decodeExternalOption($this->externalOptions);
        unset($eo[$id]);
        $this->setExternalOptions($eo);
        return $this->save();
    }

    public function setExternalOptions($options)
    {
        //we convert it to base64 to sanitize the input since we do not validate input from externalOptions
        $this->externalOptions = base64_encode(serialize($options));
        //var_dump($this->externalOptions, $options);
    }

    public function getExternalOption($id)
    {
        $eo = User::decodeExternalOption($this->externalOptions);
        if (empty($eo[$id])) {
            return null;
        }
        return $eo[$id];
    }

    public function load($id, $refreshCache = false)
    {
        $id = intval($id);
        if (empty($id)) {
            return false;
        }
        $user = self::getUserDb($id);
        if (empty($user)) {
            return false;
        }
        foreach ($user as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    private function loadFromUser($user)
    {
        $userLoaded = self::getUserDbFromUser($user);
        if (empty($userLoaded)) {
            _error_log("User::loadFromUser($user) error");
            return false;
        } else {
            _error_log("User::loadFromUser($user) user found [{$userLoaded['id']}]{$userLoaded['user']}");
        }
        //_error_log("User::loadFromUser($user) ");
        //_error_log("User::loadFromUser json " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        foreach ($userLoaded as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    public function loadSelfUser()
    {
        $this->load($this->getId());
    }


    public static function getIdRequestOrSession()
    {
        if (!empty($_REQUEST['users_id'])) {
            return intval($_REQUEST['users_id']);
        } else {
            return self::getId();
        }
    }

    public static function getId()
    {
        if (self::isLogged()) {
            return $_SESSION['user']['id'];
        } else {
            return 0;
        }
    }

    static function getCookieUsersId()
    {
        $userCookie = User::getUserCookieCredentials();
        if (empty($userCookie)) {
            return 0;
        } else {
            return intval($userCookie->users_id);
        }
    }

    public static function getEmail_()
    {
        if (self::isLogged()) {
            return $_SESSION['user']['email'];
        } else {
            return '';
        }
    }

    public static function _getEmail()
    {
        return self::getEmail_();
    }

    static function getEmailDb($users_id)
    {
        $user = self::getUserDB($users_id);
        return @$user['email'];
    }

    public function getBdId()
    {
        return $this->id;
    }

    public static function updateSessionInfo()
    {
        if (self::isLogged()) {
            $user = self::getUserDb($_SESSION['user']['id']);
            _error_log('user updateSessionInfo login');
            $_SESSION['user'] = $user;
        }
    }

    public static function getName()
    {
        if (self::isLogged()) {
            return $_SESSION['user']['name'];
        } else {
            return '';
        }
    }

    public static function getUserName()
    {
        if (self::isLogged()) {
            return $_SESSION['user']['user'];
        } else {
            return '';
        }
    }

    public static function getUserChannelName()
    {
        if (self::isLogged()) {
            if (empty($_SESSION['user']['channelName'])) {
                $_SESSION['user']['channelName'] = self::_recommendChannelName('', 0, '', User::getId());
                $user = new User(User::getId());
                $user->setChannelName($_SESSION['user']['channelName']);
                $user->save();
            }

            return $_SESSION['user']['channelName'];
        } else {
            return false;
        }
    }

    public static function _recommendChannelName($name = "", $try = 0, $unknown = "", $users_id = 0)
    {

        $name = preg_replace('/\s+/', '', "{$name}");
        if (empty($users_id)) {
            if (!empty(User::getId())) {
                $users_id = User::getId();
            }
        }
        if (empty($name) && $try == 0) {
            if (!empty($unknown)) {
                $name = $unknown;
            } else if (!empty($users_id)) {
                $name = User::getUserDb($users_id);
            }
        }
        if ($try > 10) {
            _error_log("User:_recommendChannelName too many tries ({$name}) (" . User::getId() . ") ", AVideoLog::$ERROR);
            return uniqid();
        }
        if (empty($name)) {
            $name = self::getNameIdentification();
            if ($name == __("Unknown User") && !empty($unknown)) {
                $name = $unknown;
            }
            $name = preg_replace('/\s+/', '', $name);
            $name = cleanString($name);
        }
        if (!is_string($name)) {
            $name = json_encode($name);
        }
        // in case is a email get only the username
        $parts = explode("@", $name);
        $name = $parts[0];
        // do not exceed 36 chars to leave some room for the unique id;
        $name = substr($name, 0, 36);
        if (!Permissions::canAdminUsers()) {
            $user = self::getUserFromChannelName($name);
            if ($user && $user['id'] !== $users_id) {
                return self::_recommendChannelName($name . "_" . uniqid(), $try + 1, $unknown, $users_id);
            }
        }
        return $name;
    }

    public static function getUserFromChannelName($channelName)
    {
        $channelName = cleanString($channelName);
        global $global;
        $channelName = ($channelName);
        $sql = "SELECT * FROM users WHERE channelName = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$channelName]);
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);

        if ($user !== false) {
            $user = cleanUpRowFromDatabase($user);
            return $user;
        } else {
            return false;
        }
    }

    /**
     * return an name to identify the user
     * @return String
     */
    public static function getNameIdentification()
    {
        global $advancedCustomUser;
        if (self::isLogged()) {
            if (!empty(self::getName()) && empty($advancedCustomUser->doNotIdentifyByName)) {
                return self::getName();
            }
            if (!empty(self::getMail()) && empty($advancedCustomUser->doNotIdentifyByEmail)) {
                return self::getMail();
            }
            if (!empty(self::getUserName()) && empty($advancedCustomUser->doNotIdentifyByUserName)) {
                return self::getUserName();
            }
            if (!empty(self::getUserChannelName())) {
                return self::getUserChannelName();
            }
        }
        return __("Unknown User");
    }

    /**
     * return an name to identify the user from database
     * @return String
     */
    public function getNameIdentificationBd()
    {
        global $advancedCustomUser;
        if (!empty($this->name) && empty($advancedCustomUser->doNotIdentifyByName)) {
            return str_replace('"', '', $this->name);
        }
        if (!empty($this->email) && empty($advancedCustomUser->doNotIdentifyByEmail)) {
            return str_replace('"', '', $this->email);
        }
        if (!empty($this->user) && empty($advancedCustomUser->doNotIdentifyByUserName)) {
            return str_replace('"', '', $this->user);
        }
        if (!empty($this->channelName)) {
            return str_replace('"', '', $this->channelName);
        }
        return __("Unknown User");
    }

    public static function getNameIdentificationById($id = "")
    {
        if (!empty($id)) {
            $user = new User($id);
            return $user->getNameIdentificationBd();
        }
        return __("Unknown User");
    }

    public static function getDescriptionById($id, $removeHTML = false)
    {
        $about = self::getAboutFromId($id);
        if ($removeHTML) {
            $about = br2nl($about);
            $about = strip_tags($about);
        }
        return trim($about);
    }

    public static function getAboutFromId($id)
    {
        if (!empty($id)) {
            $user = new User($id);
            return $user->getAbout();
        }
        return '';
    }

    public static function getUserPass()
    {
        if (self::isLogged()) {
            //return $_SESSION['user']['password'];
            return @$_SESSION['user']['passhash'];
        } else {
            return '';
        }
    }

    public function _getName()
    {
        if (empty($this->name)) {
            return '';
        }
        return str_replace('"', '', $this->name);
    }

    public function getBdName()
    {
        return $this->_getName();
    }

    public static function _getPhoto($id = "")
    {
        global $global;
        if (!empty($id)) {
            $user = self::findById($id);
            if (!empty($user)) {
                $photo = $user['photoURL'];
            }
        } elseif (self::isLogged()) {
            $photo = $_SESSION['user']['photoURL'];
        }
        if (!empty($photo)) {
            if (preg_match("/videos\/userPhoto\/.*/", $photo) && file_exists($global['systemRootPath'] . $photo)) {
                return $photo;
            } else {
                $photoPath = "/videos/userPhoto/photo{$id}.png";
                $content = url_get_contents($photo);
                file_put_contents($global['systemRootPath'] . $photoPath, $content);
                $photo = $photoPath;
            }
        }
        if (empty($photo)) {
            $photo = ImagesPlaceHolders::getUserIcon();
        }
        return $photo;
    }

    public static function getPhoto($id = "", $ignoreCDN = false, $returnRelativePath = false)
    {
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
                if (!$returnRelativePath) {
                    $photo = getURL($photo, $ignoreCDN);
                }
            } else {
                $photo = '';
            }
        }
        if (empty($photo)) {
            $photo = ImagesPlaceHolders::getUserIcon();
            if (!$returnRelativePath) {
                $photo = getURL($photo);
            }
        }
        return $photo;
    }

    public static function _getOGImage($users_id)
    {
        return "/videos/userPhoto/photo{$users_id}_og_200X200.jpg";
    }

    public static function deleteOGImage($users_id)
    {
        global $global;
        $photo = $global['systemRootPath'] . self::_getOGImage($users_id);
        @unlink($photo);
    }

    public static function getOGImage($users_id = "")
    {
        global $global;
        $photo = self::_getPhoto($users_id);
        if ($photo == ImagesPlaceHolders::getUserIcon()) {
            return getURL($photo);
        }
        if (empty($photo)) {
            return false;
        }
        $source = $global['systemRootPath'] . $photo;
        $destination = $global['systemRootPath'] . self::_getOGImage($users_id);

        convertImageToOG($source, $destination);

        return getURL(self::_getOGImage($users_id));
    }

    public static function getOGImagePath($users_id = "")
    {
        global $global;
        $photo = self::_getPhoto($users_id);
        if ($photo == ImagesPlaceHolders::getUserIcon()) {
            return $global['systemRootPath'] . ($photo);
        }
        if (empty($photo)) {
            return false;
        }
        $source = $global['systemRootPath'] . $photo;
        $destination = $global['systemRootPath'] . self::_getOGImage($users_id);

        convertImageToOG($source, $destination);

        return $destination;
    }

    public static function getEmailVerifiedIcon($id = "")
    {
        global $advancedCustomUser;
        $mark = '';
        if (!empty($advancedCustomUser->showEmailVerifiedMark)) {
            if (!empty($id)) {
                $user = self::findById($id);
                if (!empty($user)) {
                    $verified = $user['emailVerified'];
                }
            } elseif (self::isLogged()) {
                $verified = $_SESSION['user']['emailVerified'];
            }
            if (!empty($verified)) {
                $mark .= ' <i class="fas fa-check-circle" data-toggle="tooltip" data-placement="bottom" title="' . __("E-mail Verified") . '"></i>';
            } else {
                //return '<i class="fas fa-times-circle text-muted"></i>';
                $mark .= '';
            }
        }
        if ($advancedCustomUser->Checkmark1Enabled) {
            if (User::externalOptionsFromUserID($id, "checkmark1")) {
                $mark .= " " . $advancedCustomUser->Checkmark1HTML;
            }
        }
        if ($advancedCustomUser->Checkmark2Enabled) {
            if (User::externalOptionsFromUserID($id, "checkmark2")) {
                $mark .= " " . $advancedCustomUser->Checkmark2HTML;
            }
        }
        if ($advancedCustomUser->Checkmark3Enabled) {
            if (User::externalOptionsFromUserID($id, "checkmark3")) {
                $mark .= " " . $advancedCustomUser->Checkmark3HTML;
            }
        }
        return $mark;
    }

    public function getPhotoDB()
    {
        global $global;
        $photo = self::getPhoto($this->id);
        return $photo;
    }

    public static function getBackground($id = "")
    {
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
                $photo = getCDN() . $photo;
            } else {
                $photo = '';
            }
        }
        if (empty($photo)) {
            $photo = getURL("view/img/background.png");
        }
        return $photo;
    }

    public static function getMail()
    {
        return self::getEmail_();
    }

    public function save($updateUserGroups = false)
    {
        global $global, $config, $advancedCustom, $advancedCustomUser;
        if (is_object($config) && $config->currentVersionLowerThen('5.6')) {
            // they don't have analytics code
            return false;
        }
        if (empty($this->user) || empty($this->password)) {
            //echo "u:" . $this->user . "|p:" . strlen($this->password);
            if (empty($this->user)) {
                //echo "u:" . $this->user . "|p:" . strlen($this->password);
                _error_log('User:save:Error : 1 You need a user to register ' . getRealIpAddr() . ' ' . json_encode(debug_backtrace()));
                return false;
            }
            if (empty($this->password)) {
                //echo "u:" . $this->user . "|p:" . strlen($this->password);
                _error_log('User:save:Error : 2 You need a password to register ' . getRealIpAddr());
                return false;
            }

            return false;
        }
        if (empty($this->isAdmin)) {
            $this->isAdmin = "false";
        }
        if (empty($this->canStream)) {
            if (empty($this->id)) { // it is a new user
                if (empty($advancedCustomUser->newUsersCanStream)) {
                    $this->canStream = "0";
                } else {
                    $this->canStream = "1";
                }
            } else {
                $this->canStream = "0";
            }
        }
        if (empty($this->canUpload)) {
            $this->canUpload = "0";
        }
        if (empty($this->status)) {
            $this->status = 'a';
        }
        if (empty($this->emailVerified)) {
            $this->emailVerified = 0;
        }
        if (isset($global['emailVerified'])) {
            $this->emailVerified = $global['emailVerified'];
        }
        if (isset($global['canCreateMeet'])) {
            $this->canCreateMeet = $global['canCreateMeet'];
        }
        if (isset($global['canStream'])) {
            $this->canStream = $global['canStream'];
        }
        if (isset($global['canUpload'])) {
            $this->canUpload = $global['canUpload'];
        }

        $this->emailVerified = intval($this->emailVerified);

        $this->is_company = $this->getIs_company();

        $user = ($this->user);
        $password = ($this->password);
        $name = ($this->name);
        $status = ($this->status);
        $this->about = preg_replace("/(\\\)+n/", "\n", "{$this->about}");
        $this->channelName = self::_recommendChannelName($this->channelName, 0, $this->user, $this->id);
        if (filter_var($this->donationLink, FILTER_VALIDATE_URL) === false) {
            $this->donationLink = '';
        }
        if (!empty($this->id)) {
            $formats = "ssssiiii";
            $values = [$user, $password, $this->email, $name, $this->isAdmin, $this->canStream, $this->canUpload, $this->canCreateMeet];
            $sql = "UPDATE users SET user = ?, password = ?, "
                . "email = ?, name = ?, isAdmin = ?,"
                . "canStream = ?,canUpload = ?,canCreateMeet = ?,";
            if (isset($this->canViewChart)) {
                $formats .= "i";
                $values[] = $this->canViewChart;
                $sql .= "canViewChart = ?, ";
            }
            if (!_empty($this->birth_date)) {
                $values[] = $this->birth_date;
            } else {
                $values[] = null;
            }
            $formats .= "s";
            $sql .= " birth_date = ?, ";

            $formats .= "ssssssisssssssssssi";
            $values[] = $this->status;
            $values[] = $this->photoURL;
            $values[] = $this->backgroundURL;
            $values[] = $this->recoverPass;
            $values[] = $this->about;
            $values[] = $this->channelName;
            $values[] = $this->emailVerified;
            $values[] = $this->analyticsCode;
            $values[] = $this->externalOptions;
            $values[] = $this->first_name;
            $values[] = $this->last_name;
            $values[] = $this->address;
            $values[] = $this->zip_code;
            $values[] = $this->country;
            $values[] = $this->region;
            $values[] = $this->city;
            $values[] = $this->donationLink;
            $values[] = $this->phone;
            $values[] = $this->id;

            $sql .= "status = ?, "
                . "photoURL = ?, backgroundURL = ?, "
                . "recoverPass = ?, about = ?, "
                . " channelName = ?, emailVerified = ? , analyticsCode = ?, externalOptions = ? , "
                . " first_name = ? , last_name = ? , address = ? , zip_code = ? , country = ? , region = ? , city = ? , donationLink = ? , phone = ? , is_company = " . (empty($this->is_company) ? 'NULL' : intval($this->is_company)) . ", "
                . " modified = now() WHERE id = ?";
            //var_dump(__LINE__, debug_backtrace());exit;
            _error_log("Update user user=$user, email={$this->email}, name=$name " . ' IP=' . getRealIpAddr() . ' ' . $_SERVER['HTTP_USER_AGENT'] . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        } else {
            $formats = "ssssiiiisssssssi";
            $values = [
                $user,
                $password,
                $this->email,
                $name,
                $this->isAdmin,
                $this->canStream,
                $this->canUpload,
                $this->canCreateMeet,
                $status,
                $this->photoURL,
                $this->recoverPass,
                $this->channelName,
                $this->analyticsCode,
                $this->externalOptions,
                $this->phone,
                $this->emailVerified
            ];
            $sql = "INSERT INTO users (user, password, email, name, isAdmin, canStream, canUpload, canCreateMeet, canViewChart, "
                . " status,photoURL,recoverPass, created, modified, channelName, analyticsCode, externalOptions, phone, is_company,emailVerified) "
                . " VALUES (?,?,?,?,?,?,?,?, false, "
                . "?,?,?, now(), now(),?,?,?,?," . (empty($this->is_company) ? 'NULL' : intval($this->is_company)) . ",?)";

            _error_log("Insert new user user=$user, email={$this->email}, name=$name " . ' IP=' . getRealIpAddr() . ' ' . $_SERVER['HTTP_USER_AGENT'] . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        }
        $insert_row = sqlDAL::writeSql($sql, $formats, $values);

        /**
         *
         * @var array $global
         * @var object $global['mysqli']
         */
        if ($insert_row) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
                if (empty($global['emailVerified']) && (!empty($advancedCustomUser->unverifiedEmailsCanNOTLogin) || !empty($advancedCustomUser->sendVerificationMailAutomatic))) {
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
            $this->id = $id;
            return $id;
        } else {
            _error_log('User:save:Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error . " $sql");
            return false;
        }
    }

    public static function getChannelOwner($channelName)
    {
        global $global;
        $channelName = ($channelName);
        $sql = "SELECT * FROM users WHERE channelName = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$channelName]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $result = cleanUpRowFromDatabase($result);
            $user = $result;
        } else {
            $user = false;
        }
        return $user;
    }

    public static function getFromUsername($user)
    {
        global $global;
        $user = ($user);
        $sql = "SELECT * FROM users WHERE user = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$user]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $result = cleanUpRowFromDatabase($result);
            $user = $result;
        } else {
            $user = false;
        }
        return $user;
    }

    private static function setCacheWatchVideo($cacheName, $value)
    {
        if (!User::isLogged()) {
            ObjectYPT::setCache($cacheName, $value);
        } else {
            ObjectYPT::setSessionCache($cacheName, $value);
        }
    }

    public static function canWatchVideo($videos_id, $users_id = null, $ignoreCache = false)
    {
        global $global;
        $global['canWatchVideoReason'] = ''; // Initialize global variable for the reason
        $cacheName = "canWatchVideo$videos_id";

        if (empty($users_id)) {
            $users_id = User::getId();
        }

        if (empty($users_id)) {
            $cacheName = "canWatchVideoNOTLOGGED$videos_id";
            if (!$ignoreCache) {
                $cache = ObjectYPT::getCache($cacheName, 60);
            }
        }
        if (empty($cache) && !$ignoreCache) {
            $cache = ObjectYPT::getSessionCache($cacheName, 600);
        }
        if (!$ignoreCache && isset($cache)) {
            if ($cache === 'false') {
                $cache = false;
            }
            return $cache;
        }
        if (empty($videos_id)) {
            $global['canWatchVideoReason'] = "Video ID is empty";
            return false;
        }

        if (User::isAdmin($users_id)) {
            $global['canWatchVideoReason'] = "User {$users_id} is an admin";
            return true;
        }

        $video = new Video("", "", $videos_id);
        if ($video->getStatus() === Video::$statusInactive) {
            $global['canWatchVideoReason'] = "Video is inactive";
            _error_log("User::canWatchVideo Video is inactive ({$videos_id})");
            self::setCacheWatchVideo($cacheName, false);
            return false;
        }
        $user = new User($video->getUsers_id());
        if ($user->getStatus() === 'i') {
            $global['canWatchVideoReason'] = "Video owner is inactive";
            _error_log("User::canWatchVideo User is inactive ({$videos_id})");
            self::setCacheWatchVideo($cacheName, false);
            return false;
        }

        if (AVideoPlugin::userCanWatchVideo($users_id, $videos_id)) {
            global $userCanWatchVideoReason;
            $global['canWatchVideoReason'] = "User is allowed by plugin to watch the video: {$userCanWatchVideoReason}";
            self::setCacheWatchVideo($cacheName, true);
            return true;
        }

        $rows = UserGroups::getVideosAndCategoriesUserGroups($videos_id);
        if (empty($rows)) {
            $pluginCanWatch = AVideoPlugin::userCanWatchVideo($users_id, $videos_id);
            if (!$pluginCanWatch) {
                global $userCanWatchVideoReason;
                $global['canWatchVideoReason'] = "No: $userCanWatchVideoReason";
                if ($users_id) {
                    _error_log("User::canWatchVideo Plugin restricts access to user [{$users_id}] ({$videos_id})");
                }
                self::setCacheWatchVideo($cacheName, false);
                return false;
            } else {
                $global['canWatchVideoReason'] = "No user group, but video is public";
                self::setCacheWatchVideo($cacheName, true);
                return true;
            }
        }

        if (!$users_id) {
            $global['canWatchVideoReason'] = "User is not logged in";
            self::setCacheWatchVideo($cacheName, false);
            return false;
        }

        $rowsUser = UserGroups::getUserGroups($users_id);
        foreach ($rows as $value) {
            foreach ($rowsUser as $value2) {
                if ($value['id'] === $value2['id']) {
                    $global['canWatchVideoReason'] = "User is in the required user group id {$value['id']}";
                    self::setCacheWatchVideo($cacheName, true);
                    return true;
                }
            }
        }

        if (isVideo()) {
            $global['canWatchVideoReason'] = "User is not in any required user groups";
            _error_log("User::canWatchVideo The user {$users_id} is not in any user groups ({$videos_id}) " . json_encode($rows));
        }
        self::setCacheWatchVideo($cacheName, false);
        return false;
    }

    public static function canWatchVideoWithAds($videos_id, $users_id = null, $ignoreCache = false)
    {
        global $global;
        $global['canWatchVideoReason'] = ''; // Initialize global variable for the reason

        if (empty($users_id)) {
            $users_id = User::getId();
        }

        if (empty($videos_id)) {
            $global['canWatchVideoReason'] = "canWatchVideoWithAds: Video ID is empty";
            _error_log("User::canWatchVideoWithAds Video ID is empty ({$videos_id})");
            return false;
        }

        if (User::isAdmin($users_id)) {
            $global['canWatchVideoReason'] = "canWatchVideoWithAds: User is an admin";
            return true;
        }

        $video = new Video("", "", $videos_id);
        if ($video->getStatus() === Video::$statusInactive) {
            $global['canWatchVideoReason'] = "canWatchVideoWithAds: Video is inactive";
            _error_log("User::canWatchVideoWithAds Video is inactive ({$videos_id})");
            return false;
        }

        $user = new User($video->getUsers_id());
        if ($user->getStatus() === 'i') {
            $global['canWatchVideoReason'] = "canWatchVideoWithAds: Video owner is inactive";
            _error_log("User::canWatchVideoWithAds User is inactive ({$videos_id})");
            return false;
        }

        if (AVideoPlugin::userCanWatchVideoWithAds($users_id, $videos_id)) {
            global $userCanWatchVideoWithAdsReason;
            $global['canWatchVideoReason'] = "canWatchVideoWithAds: User is allowed to watch with ads by a plugin: {$userCanWatchVideoWithAdsReason}";
            return true;
        }

        if (!empty($_REQUEST['debug']) && isVideo()) {
            _error_log("User::canWatchVideoWithAds User cannot watch with ads ({$videos_id})");
        }

        if (self::canWatchVideo($videos_id, $users_id, $ignoreCache)) {
            $global['canWatchVideoReason'] = "canWatchVideoWithAds: User is allowed to watch the video";
            return true;
        }

        if (!empty($_REQUEST['debug']) && isVideo()) {
            _error_log("User::canWatchVideoWithAds User cannot watch video ({$videos_id})");
        }

        if(empty($global['canWatchVideoReason'])){
            $global['canWatchVideoReason'] = "canWatchVideoWithAds: User cannot watch the video or ads";
        }
        return false;
    }


    public function delete()
    {
        $this->id = intval($this->id);
        if (!Permissions::canAdminUsers() && !isCommandLineInterface()) {
            if (self::getId() !== $this->id) {
                _error_log('Delete user error, users_id does not match: [' . self::getId() . '] !== [' . $this->id . ']');
                return false;
            }
        }

        global $global;
        if (!empty($this->id)) {

            $sql = "DELETE FROM live_transmition_history_log
            WHERE live_transmitions_history_id IN (
                SELECT id
                FROM live_transmitions_history
                WHERE users_id = ?
            )";
            sqlDAL::writeSql($sql, "i", [$this->id]);

            $arrayTables = [
                //'live_transmition_history_log',
                'live_transmitions',
                'users_login_history',
                'audit',
                'ppvlive_purchases',
                'bans',
                'LiveChat',
                'live_transmition_history_log',
                'live_transmitions_history',
                'email_to_user',
                'wallet',
            ];

            foreach ($arrayTables as $value) {
                // Check if table exists
                $checkTableSQL = "SHOW TABLES LIKE '{$value}'";
                try {
                    $result = sqlDAL::readSql($checkTableSQL);
                    $tableExists = (sqlDAL::num_rows($result) > 0);
                    sqlDAL::close($result); // Make sure to close the result after checking
                } catch (Exception $exc) {
                    _error_log("Delete usertable not found {$value}");
                    $tableExists = false;
                }

                if ($tableExists) {
                    $sql = "DELETE FROM {$value} WHERE users_id = ?";
                    try {
                        _error_log("Delete user $sql users_id = {$this->id}");
                        sqlDAL::writeSql($sql, "i", [$this->id]);
                    } catch (Exception $exc) {
                        // Handle exception if needed
                    }
                }
            }

            $sql = "DELETE FROM users WHERE id = ?";
        } else {
            _error_log('Delete user error, this->id is empty');
            return false;
        }
        _error_log('Delete user execute: ' . $this->id);
        return sqlDAL::writeSql($sql, "i", [$this->id]);
    }

    public const USER_LOGGED = 0;
    public const USER_NOT_VERIFIED = 1;
    public const USER_NOT_FOUND = 2;
    public const CAPTCHA_ERROR = 3;
    public const REQUIRE2FA = 4;
    public const SYSTEM_ERROR = 5;

    public function login($noPass = false, $encodedPass = false, $ignoreEmailVerification = false)
    {
        global $global, $advancedCustom, $advancedCustomUser, $config;
        require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

        if (!class_exists('AVideoPlugin')) {
            _error_log("ERROR login($noPass, $encodedPass, $ignoreEmailVerification) " . json_encode(debug_backtrace()));
            return self::SYSTEM_ERROR;
        }

        if (User::isLogged()) {
            return self::USER_LOGGED;
        }

        if (class_exists('AVideoPlugin')) {
            if (empty($advancedCustomUser)) {
                $advancedCustomUser = AVideoPlugin::getObjectData("CustomizeUser");
            }
            if (empty($advancedCustom)) {
                $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
            }
        }

        if (strtolower($encodedPass) === 'false') {
            $encodedPass = false;
        }

        if ($noPass) {
            $user = $this->find($this->user, false, true);
        } else {
            $user = $this->find($this->user, $this->password, true, $encodedPass);
        }

        if (!isAVideoMobileApp() && !isAVideoEncoder() && !self::checkLoginAttempts()) {
            _error_log('login Captcha error ' . $_SERVER['HTTP_USER_AGENT']);
            return self::CAPTCHA_ERROR;
        }

        ObjectYPT::clearSessionCache();
        _session_start();

        if (empty($ignoreEmailVerification) && !empty($user) && empty($user['isAdmin']) && empty($user['emailVerified']) && !empty($advancedCustomUser->unverifiedEmailsCanNOTLogin)) {
            unset($_SESSION['user']);
            self::sendVerificationLink($user['id']);
            return self::USER_NOT_VERIFIED;
        } elseif ($user) {
            $_SESSION['user'] = $user;
            $this->setLastLogin($_SESSION['user']['id']);
            $rememberme = 0;

            if ((!empty($_REQUEST['rememberme']) && $_REQUEST['rememberme'] == "true") || !empty($_COOKIE['rememberme'])) {
                $valid = '+ 1 year';
                $expires = strtotime($valid);
                $rememberme = 1;
                $passhash = self::getUserHash($user['id'], $valid);
            } else {
                $valid = '+ 1 day';
                $expires = 0;
                $passhash = self::getUserHash($user['id'], $valid);
            }

            self::setUserCookie($rememberme, $user['id'], $user['user'], $passhash, $expires);

            AVideoPlugin::onUserSignIn($_SESSION['user']['id']);
            $_SESSION['loginAttempts'] = 0;

            // Call custom session regenerate logic
            // this was regenerating the session all the time, making harder to save info in the session
            //_session_regenerate_id();

            _session_write_close();

            //_error_log("User:login finish with success users_id= {$_SESSION['user']['id']} {$_SERVER['HTTP_USER_AGENT']} IP=" . getRealIpAddr() . json_encode(debug_backtrace()));
            return self::USER_LOGGED;
        } else {
            unset($_SESSION['user']);
            return self::USER_NOT_FOUND;
        }
    }

    static function setUserCookie($rememberme, $users_id, $user, $pass, $expires)
    {
        //_error_log("setUserCookie rememberme=$rememberme users_id={$users_id}");
        if (!empty($rememberme)) {
            self::setUserCookieCredentials($users_id, $user, $pass, $expires);
        }
    }

    static function setUserCookieCredentials($users_id, $user, $pass, $expires)
    {
        $array = self::getUserCookieCredentials();
        if (!empty($array->users_id)) {
            _error_log("setUserCookieCredentials there is already a cookie");
            if ($array->users_id == $users_id) {
                _error_log("setUserCookieCredentials there is already a cookie and is the same user");
                return true;
            }
        }
        $array = array('users_id' => $users_id, 'user' => $user, 'pass' => $pass, 'ip' => getRealIpAddr());
        $cookieValue = encryptString(json_encode($array));
        _setcookie("credentials", $cookieValue, $expires);
        _error_log("setUserCookieCredentials credentials set");
    }

    static function getUserCookieCredentials()
    {
        $array = false;
        if (!empty($_COOKIE['credentials'])) {
            $string = decryptString($_COOKIE['credentials']);
            $array = json_decode($string);
            $ipNow = getRealIpAddr();
            if ($array->ip !== $ipNow) {
                _error_log("getUserCookieCredentials ip does not match {$array->ip} != $ipNow");
                return false;
            }
        }
        return $array;
    }

    static function unsetUserCookie()
    {
        _error_log("unsetUserCookie _unsetcookie('credentials') " . json_encode(debug_backtrace()));
        _unsetcookie('credentials');
    }

    public static function isCaptchaNeed()
    {
        global $advancedCustomUser;
        // check for multiple logins attempts to prevent hacking
        if (!empty($_SESSION['loginAttempts']) && !empty($advancedCustomUser->requestCaptchaAfterLoginsAttempts)) {
            if (isMobile()) {
                $advancedCustomUser->requestCaptchaAfterLoginsAttempts += 10;
            }
            if ($_SESSION['loginAttempts'] > $advancedCustomUser->requestCaptchaAfterLoginsAttempts) {
                return true;
            }
        }
        return false;
    }

    public static function checkLoginAttempts()
    {
        global $advancedCustomUser, $global, $_checkLoginAttempts;
        if (isset($_checkLoginAttempts)) {
            return $_checkLoginAttempts;
        }
        // check for multiple logins attempts to prevent hacking
        if (empty($_SESSION['loginAttempts'])) {
            _session_start();
            $_SESSION['loginAttempts'] = 0;
        }
        $_checkLoginAttempts = true;
        if (!empty($advancedCustomUser->requestCaptchaAfterLoginsAttempts)) {
            _session_start();
            $_SESSION['loginAttempts']++;
            if ($_SESSION['loginAttempts'] > $advancedCustomUser->requestCaptchaAfterLoginsAttempts) {
                if (empty($_POST['captcha'])) {
                    $_checkLoginAttempts = false;
                }
                require_once $global['systemRootPath'] . 'objects/captcha.php';
                if (!Captcha::validation($_POST['captcha'])) {
                    $_checkLoginAttempts = false;
                }
            }
        }
        return $_checkLoginAttempts;
    }

    public static function getCaptchaFormIfNeed()
    {
        // check for multiple logins attempts to prevent hacking
        if (self::isCaptchaNeed()) {
            return self::getCaptchaForm();
        }
        return "";
    }

    public static function getCaptchaForm($uid = "", $forceCaptcha = false)
    {
        global $global;
        $capcha = getCaptcha($uid, $forceCaptcha);
        return $capcha;
    }

    private function setLastLogin($user_id)
    {
        global $global;
        if (empty($user_id)) {
            die('Error : setLastLogin ');
        }
        $sql = "UPDATE users SET lastLogin = now(), modified = now() WHERE id = ?";
        return sqlDAL::writeSql($sql, "i", [$user_id]);
    }

    public static function logoff()
    {
        global $global, $justLogoff, $isLogged;
        $justLogoff = true;
        $isLogged = false;
        _session_start();
        self::unsetUserCookie();
        //session_regenerate_id(true);
        ObjectYPT::deleteAllSessionCache();
        unset($_SESSION['user']);
        unset($_SESSION['swapUser']);
        _error_log('user:logoff');
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        _session_write_close();
    }

    private static function recreateLoginFromCookie()
    {
        //return false;
        //var_dump($_COOKIE['credentials']);exit;
        //var_dump($_COOKIE);exit;
        global $justLogoff, $justTryToRecreateLoginFromCookie;
        if (empty($justTryToRecreateLoginFromCookie) && empty($justLogoff) && empty($_SESSION['user']['id'])) {
            _session_start();
            //var_dump($_COOKIE);exit;
            $justTryToRecreateLoginFromCookie = 1;

            // first check if the LoginControl::singleDeviceLogin is enabled, if it is only recreate login if the device is the last device
            if (class_exists('AVideoPlugin') && $obj = AVideoPlugin::getDataObjectIfEnabled("LoginControl")) {
                if (!empty($obj->singleDeviceLogin)) {
                    if (!LoginControl::isLoggedFromSameDevice()) {
                        self::logoff();
                        return false;
                    }
                }
            }
            $userCookie = User::getUserCookieCredentials();
            if ((!empty($userCookie))) {
                $_REQUEST['rememberme'] = 1;
                //_error_log("user::recreateLoginFromCookie: SCRIPT_NAME ".json_encode($_SERVER["SCRIPT_NAME"]));
                //_error_log("user::recreateLoginFromCookie: user cookie found: {$userCookie->user} result: " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
                $user = new User(0, $userCookie->user, false);
                $user->setPassword($userCookie->pass, true);
                $resp = $user->login(false, true);
                //_error_log("user::recreateLoginFromCookie: resp=$resp");

                $userCookie = User::getUserCookieCredentials();
                if (!empty($userCookie) && $user->id != $userCookie->users_id) {
                    _error_log("user::recreateLoginFromCookie: do logoff because the cookie users_id does not match: {$userCookie->user} result: " . $resp);
                    self::logoff();
                } else {
                    if (User::USER_LOGGED == $resp) {
                        _error_log("user::recreateLoginFromCookie: do cookie-login: {$userCookie->user} [{$userCookie->id}]  id: " . $_SESSION['user']['id']);
                    } else {
                        //_error_log("user::recreateLoginFromCookie: do cookie-login: user={$userCookie->user} [{$userCookie->id}]  pass={$userCookie->pass} login does not match resp=$resp");
                        if ($resp != User::SYSTEM_ERROR) {
                            self::logoff();
                        }
                    }
                }
            }
        }
    }

    public static function isLogged($checkForRequestLogin = false)
    {
        self::recreateLoginFromCookie();
        $isLogged = !empty($_SESSION['user']['id']);
        if (empty($isLogged) && $checkForRequestLogin) {
            self::loginFromRequest();
            return !empty($_SESSION['user']['id']);
        } else {
            return $isLogged;
        }
    }

    public static function isVerified()
    {
        self::recreateLoginFromCookie();
        return !empty($_SESSION['user']['emailVerified']);
    }

    public static function isAdmin($users_id = 0): bool
    {
        if (!empty($users_id)) {
            $user = new User($users_id);
            return !empty($user->getIsAdmin());
        }

        self::recreateLoginFromCookie();
        return !empty($_SESSION['user']['isAdmin']);
    }

    public static function getBirthIfIsSet($users_id = 0)
    {
        $birth_date = '';
        if (!empty($users_id)) {
            $user = new User($users_id);
            $birth_date = $user->getBirth_date();
        } else {
            if (empty($_SESSION['user']['birth_date'])) {
                self::recreateLoginFromCookie();
            }
            if (!empty($_SESSION['user']['birth_date'])) {
                $birth_date = $_SESSION['user']['birth_date'];
            } else {
                $user = new User(User::getId());
                $birth_date = $_SESSION['user']['birth_date'] = $user->getBirth_date();
            }
        }
        return $birth_date;
    }

    public static function getAge($users_id = 0)
    {
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        if (empty($users_id)) {
            return 0;
        }
        $birth_date = self::getBirthIfIsSet($users_id);
        if (empty($birth_date)) {
            return 0;
        }
        $birth_date = new DateTime($birth_date);
        $current_date = new DateTime('now');
        $age = $current_date->diff($birth_date)->y;
        if ($age < 0) {
            return 0;
        }
        return $age;
    }

    public static function isOver18($users_id = 0): bool
    {
        return self::getAge($users_id) > 18;
    }

    public static function isACompany($users_id = 0)
    {
        global $_is_a_company;

        if (!empty($users_id)) {
            if (!isset($_is_a_company)) {
                $_is_a_company = [];
            }
            if (!isset($_is_a_company[$users_id])) {
                $user = new User($users_id);
                $_is_a_company[$users_id] = !empty($user->getIs_company());
            }
            return $_is_a_company[$users_id];
        }

        self::recreateLoginFromCookie();
        return !empty($_SESSION['user']['is_company']);
    }

    public static function canStream()
    {
        self::recreateLoginFromCookie();

        return !empty($_SESSION['user']['isAdmin']) || !empty($_SESSION['user']['canStream']) || AVideoPlugin::userCanLivestream($_SESSION['user']['id']);
    }

    public static function externalOptions($id)
    {
        if (!empty($_SESSION['user']['externalOptions'])) {
            $externalOptions = User::decodeExternalOption($_SESSION['user']['externalOptions']);
            if (isset($externalOptions[$id])) {
                if ($externalOptions[$id] == "true") {
                    $externalOptions[$id] = true;
                } elseif ($externalOptions[$id] == "false") {
                    $externalOptions[$id] = false;
                }

                return $externalOptions[$id];
            }
        }
        return false;
    }

    public function getExternalOptions($id)
    {
        if (empty($this->id)) {
            return null;
        }
        return self::externalOptionsFromUserID($this->id, $id);
    }

    public function _getExternalOptions()
    {
        return $this->externalOptions;
    }

    public static function externalOptionsFromUserID($users_id, $id)
    {
        $user = self::findById($users_id);
        if ($user) {
            if (!is_null($user['externalOptions'])) {
                $externalOptions = User::decodeExternalOption($user['externalOptions']);
                if (is_array($externalOptions) && sizeof($externalOptions) > 0) {
                    //var_dump($externalOptions);
                    foreach ($externalOptions as $k => $v) {
                        if ($id != $k) {
                            continue;
                        }
                        if ($v == "true") {
                            $v = 1;
                        } elseif ($v == "false") {
                            $v = 0;
                        }
                        return $v;
                    }
                }
            }
        }
        return false;
    }

    public function thisUserCanStream()
    {
        global $advancedCustomUser, $_thisUserCanStreamReasonMessage;
        $_thisUserCanStreamReasonMessage = '';
        if (!empty($advancedCustomUser->unverifiedEmailsCanNOTLiveStream)) {
            if (empty($this->emailVerified)) {
                $_thisUserCanStreamReasonMessage = 'User Not verified';
                return false;
            }
        }
        if ($this->status === 'i') {
            $_thisUserCanStreamReasonMessage = 'User status is inactive';
            return false;
        }
        $can = !empty($this->isAdmin) || !empty($this->canStream) || AVideoPlugin::userCanLivestream($this->id);
        if (empty($can)) {
            $reasons = [];
            if (empty($this->isAdmin)) {
                $reasons[] = 'User is not admin';
            }
            if (empty($this->canStream)) {
                $reasons[] = 'User cannot stream';
            }
            $_thisUserCanStreamReasonMessage = implode(', ', $reasons);
        }
        return $can;
    }

    static public function getLastUserCanStreamReason()
    {
        global $_thisUserCanStreamReasonMessage;

        return $_thisUserCanStreamReasonMessage;
    }

    private function find($user, $pass, $mustBeactive = false, $encodedPass = false)
    {
        global $global, $advancedCustom, $advancedCustomUser;
        $formats = '';
        $values = [];
        $sql = "SELECT * FROM users WHERE user = ? ";

        $formats .= "s";
        $values[] = $user;

        if (trim($user) !== $user) {
            $formats .= "s";
            $values[] = trim($user);
            $sql .= " OR user = ? ";
        }

        if (empty($advancedCustomUser)) {
            $advancedCustomUser = AVideoPlugin::getObjectData("CustomizeUser");
        }
        if (
            !empty($advancedCustomUser) &&
            ($advancedCustomUser->forceLoginToBeTheEmail || $advancedCustomUser->emailMustBeUnique) &&
            filter_var($user, FILTER_VALIDATE_EMAIL)
        ) {
            $formats .= "s";
            $values[] = trim($user);
            $sql .= " OR email = ? ";
        }

        if ($mustBeactive) {
            $sql .= " AND status = 'a' ";
        }

        $sql .= " ORDER BY id DESC LIMIT 1";

        //_error_log("User::find {$sql} ".json_encode($values));
        //_error_log("User::find values ".json_encode($values));
        $res = sqlDAL::readSql($sql, $formats, $values, true);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($result)) {
            if ($pass !== false) {
                if (!encryptPasswordVerify($pass, $result['password'], $encodedPass)) {
                    if (!empty($advancedCustom) && $advancedCustom->enableOldPassHashCheck) {
                        //_error_log("Password check new hash pass does not match, trying MD5");
                        return $this->find_Old($user, $pass, $mustBeactive, $encodedPass);
                    } else {
                        return false;
                    }
                }
            }
            $user = $result;
            $user['passhash'] = self::getUserHash($user['id']);
        } else {
            _error_log("User::find line= " . __LINE__);
            //_error_log("Password check new hash user not found");
            //check if is the old password style
            $user = false;
            //$user = false;
        }
        //_error_log("User::find line= " . __LINE__);
        return $user;
    }

    /**
     * this is the deprecated function, with week password
     * @global array $global
     * @param string $user
     * @param string $pass
     * @param string $mustBeactive
     * @param string $encodedPass
     * @return boolean
     */
    private function find_Old($user, $pass, $mustBeactive = false, $encodedPass = false)
    {
        global $global;
        $formats = '';
        $values = [];
        $sql = "SELECT * FROM users WHERE user = ? ";

        $formats .= "s";
        $values[] = $user;

        if ($mustBeactive) {
            $sql .= " AND status = 'a' ";
        }
        if ($pass !== false) {
            if (!$encodedPass || $encodedPass === 'false') {
                $passEncoded = md5($pass);
                _error_log("Password check Old not encoded pass");
                //_error_log("Password check Old not encoded pass passEncoded={$passEncoded}");
            } else {
                $passEncoded = $pass;
                _error_log("Password check Old encoded pass");
                //_error_log("Password check Old encoded pass passEncoded={$passEncoded}");
            }
            $sql .= " AND password = ? ";
            $formats .= "s";
            $values[] = $passEncoded;
        }
        $sql .= " LIMIT 1";
        $res = sqlDAL::readSql($sql, $formats, $values, true);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($result)) {
            if (!$encodedPass || $encodedPass === 'false') {
                //update the password
                $u = new User($result['id']);
                $u->setPassword($pass);
                $u->save();
                $result['password'] = $u->getPassword();
            }
            $user = $result;
            $user['passhash'] = self::getUserHash($user['id']);
        } else {
            $user = false;
        }
        if (empty($user)) {
            _error_log("Password check Old not found");
        } else {
            _error_log("Password check Old found");
        }
        return $user;
    }

    private static function findById($id)
    {
        global $global;
        $id = intval($id);
        if (empty($id)) {
            return false;
        }
        $sql = "SELECT * FROM users WHERE id = ?  LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$id]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $user = $result;
            $user['passhash'] = self::getUserHash($user['id']);
        } else {
            $user = false;
        }
        return $user;
    }

    public static function findByEmail($email)
    {
        global $global;
        $email = trim($email);
        if (empty($email)) {
            return false;
        }
        $sql = "SELECT * FROM users WHERE email = ?  LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$email]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res !== false) {
            $user = $result;
            $user['passhash'] = self::getUserHash($user['id']);
        } else {
            $user = false;
        }
        return $user;
    }

    private static function getUserDb($id)
    {
        global $global;
        $id = intval($id);
        if (empty($id)) {
            return false;
        }
        $sql = "SELECT * FROM users WHERE  id = ? LIMIT 1;";
        $res = sqlDAL::readSql($sql, "i", [$id], true);
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($user !== false) {
            return $user;
        }
        return false;
    }

    static function getUserHash($users_id, $valid = '+7 days')
    {
        $obj = new stdClass();
        $obj->u = $users_id;
        $obj->v = strtotime($valid);
        $user = self::getUserDb($users_id);
        $obj->p = $user['password'];
        return '_user_hash_' . encryptString($obj);
    }

    static function getPasswordFromUserHashIfTheItIsValid($hash)
    {
        if (!preg_match('/^_user_hash_/', $hash)) {
            return false;
        }
        $string = str_replace('_user_hash_', '', $hash);

        $json = decryptString($string);
        if (empty($json)) {
            _error_log('getPasswordFromUserHashIfTheItIsValid: string not decrypted ' . $hash);
            return false;
        }

        $obj = json_decode($json);

        if (empty($obj)) {
            _error_log('getPasswordFromUserHashIfTheItIsValid: json not decoded ');
            return false;
        }

        if ($obj->v < time()) {
            _error_log('getPasswordFromUserHashIfTheItIsValid: hash expired ');
            return false;
        }

        if (empty($obj->u)) {
            _error_log('getPasswordFromUserHashIfTheItIsValid: user is empty ');
            return false;
        }

        $user = self::getUserDb($obj->u);

        if ($user['password'] === $obj->p) {
            return $user['password'];
        }
        _error_log("getPasswordFromUserHashIfTheItIsValid: password does not match [{$user['password']}] === [{$obj->p}]");
        return false;
    }

    private static function getUserDbFromUser($user)
    {
        global $global, $advancedCustomUser;
        if (empty($user)) {
            _error_log("getUserDbFromUser empty user ");
            return false;
        }
        $formats = "";
        $values = [];
        $sql = "SELECT * FROM users WHERE user = ? ";
        $formats .= 's';
        $values[] = $user;

        if (class_exists('AVideoPlugin') && empty($advancedCustomUser)) {
            $advancedCustomUser = AVideoPlugin::getObjectData("CustomizeUser");
        }
        if (
            !empty($advancedCustomUser) &&
            ($advancedCustomUser->forceLoginToBeTheEmail || $advancedCustomUser->emailMustBeUnique) &&
            filter_var($user, FILTER_VALIDATE_EMAIL)
        ) {
            _error_log("getUserDbFromUser will also check the email {$user} ");
            $sql .= " OR email = ? ";
            $formats .= 's';
            $values[] = $user;
        } else {
            if (empty($advancedCustomUser)) {
                _error_log("getUserDbFromUser advancedCustomUser is empty ");
            } else {
                _error_log("getUserDbFromUser [{$user}] " . json_encode(
                    array(
                        $advancedCustomUser->forceLoginToBeTheEmail,
                        $advancedCustomUser->emailMustBeUnique,
                        filter_var($user, FILTER_VALIDATE_EMAIL)
                    )
                ));
            }
        }

        $sql .= " LIMIT 1";

        _error_log("getUserDbFromUser {$sql} " . json_encode(array($formats, $values)));
        $res = sqlDAL::readSql($sql, $formats, $values);
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($user !== false) {
            return $user;
        }
        return false;
    }

    public static function getUserFromID($users_id)
    {
        global $global;
        $users_id = intval($users_id);
        if (empty($users_id)) {
            return false;
        }
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$users_id]);
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($user !== false) {
            $user['groups'] = UserGroups::getUserGroups($user['id']);
            $user['identification'] = self::getNameIdentificationById($user['id']);
            $user['photo'] = self::getPhoto($user['id']);
            $user['background'] = self::getBackground($user['id']);
            $user['tags'] = self::getTags($user['id']);
            $user['name'] = @preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $user['name']);
            $user['isEmailVerified'] = $user['emailVerified'];
            if (!is_null($user['externalOptions'])) {
                $externalOptions = User::decodeExternalOption($user['externalOptions']);
                if (is_array($externalOptions) && sizeof($externalOptions) > 0) {
                    foreach ($externalOptions as $k => $v) {
                        if ($v == "true") {
                            $v = 1;
                        } elseif ($v == "false") {
                            $v = 0;
                        }
                        $user[$k] = $v;
                    }
                }
            }
            unset($user['password'], $user['recoverPass']);
            if (!Permissions::canAdminUsers() && $user['id'] !== User::getId()) {
                unset(
                    $user['first_name'],
                    $user['last_name'],
                    $user['address'],
                    $user['zip_code'],
                    $user['country'],
                    $user['region'],
                    $user['city']
                );
            }
            return $user;
        }
        return false;
    }

    public static function getUserFromEmail($email)
    {
        $email = trim($email);
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$email]);
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($user !== false) {
            return $user;
        }
        return false;
    }

    public function setUser($user)
    {
        global $advancedCustomUser;
        if (empty($advancedCustomUser->userCanChangeUsername)) {
            if (!empty($this->user)) {
                return false;
            }
        }
        $this->user = strip_tags($user);
    }

    public function setName($name)
    {
        $this->name = strip_tags($name);
    }

    static function isEmailUniqeOrFromUser($email, $users_id)
    {
        global $isEmailUniqeOrFromUserReason;

        if (empty($email)) {
            $isEmailUniqeOrFromUserReason = "FALSE: empty email address.";
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isEmailUniqeOrFromUserReason = "FALSE: Invalid email address. {$email}";
            return false;
        }

        $userFromEmail = User::getUserFromEmail($email);

        if (empty($userFromEmail)) {
            $isEmailUniqeOrFromUserReason = "TRUE: Email is unique and not associated with any user.";
            return true;
        }

        if ($userFromEmail['id'] == $users_id) {
            $isEmailUniqeOrFromUserReason = "TRUE: Email belongs to the same user (ID: {$users_id}).";
            return true;
        }

        $isEmailUniqeOrFromUserReason = "FALSE: Email is already associated with a different user (ID: {$userFromEmail['id']}).";
        return false;
    }


    public function setEmail($email)
    {
        global $advancedCustomUser;
        $email = strip_tags($email);
        if (!empty($advancedCustomUser->emailMustBeUnique)) {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            $userFromEmail = User::getUserFromEmail($email);
            if (!empty($userFromEmail)) {
                return false;
            }
        }
        if ($this->email !== $email) {
            $this->setEmailVerified(0);
        }
        $this->email = $email;
        return true;
    }

    public function setPassword($password, $doNotEncrypt = false)
    {
        if (strpos($password, "_user_hash_") === 0) {
            $passwordFromHash = User::getPasswordFromUserHashIfTheItIsValid($password);
            if (!empty($passwordFromHash)) {
                $password = $passwordFromHash;
            }
        }
        if (!empty($password)) {
            if ($doNotEncrypt) {
                $this->password = ($password);
            } else {
                $this->password = encryptPassword($password);
            }
        }
    }

    public function setIsAdmin($isAdmin)
    {
        if (empty($isAdmin) || $isAdmin === "false" || !User::isAdmin()) {
            $isAdmin = "0";
        } else {
            $isAdmin = "1";
        }
        $this->isAdmin = $isAdmin;
    }

    public function setStatus($status)
    {
        $this->status = strip_tags($status);
    }

    public function getPhotoURL()
    {
        return $this->photoURL;
    }

    public function setPhotoURL($photoURL)
    {
        $this->photoURL = strip_tags($photoURL);
    }

    public static function getAllUsersFromUsergroup($users_groups_id, $ignoreAdmin = false, $searchFields = ['name', 'email', 'user', 'channelName', 'about'], $status = "")
    {
        if (!Permissions::canAdminUsers() && !$ignoreAdmin) {
            return false;
        }
        $users_groups_id = intval($users_groups_id);
        if (empty($users_groups_id)) {
            return false;
        }
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT * FROM users u WHERE 1=1 ";

        $queryIds = [];
        if (empty($_REQUEST['userGroupShowOnly']) || $_REQUEST['userGroupShowOnly'] == 'permanent') {
            $queryIds[] = " id IN (SELECT users_id FROM users_has_users_groups ug WHERE ug.users_groups_id = {$users_groups_id}) ";
        }
        if (empty($_REQUEST['userGroupShowOnly']) || $_REQUEST['userGroupShowOnly'] == 'dynamic') {
            $ids = AVideoPlugin::getDynamicUsersId($users_groups_id);
            if (!empty($ids) && is_array($ids)) {
                $ids = array_unique($ids);
                $queryIds[] = " id IN ('" . implode("','", $ids) . "') ";
            }
        }
        if (!empty($queryIds)) {
            $sql .= " AND ( ";
            $sql .= implode(' OR ', $queryIds);
            $sql .= " ) ";
        } else {
            // do not return nothing
            $sql .= " AND u.id < 0 ";
        }

        if (!empty($status)) {
            if (strtolower($status) === 'i') {
                $sql .= " AND u.status = 'i' ";
            } else {
                $sql .= " AND u.status = 'a' ";
            }
        }

        $sql .= BootGrid::getSqlFromPost($searchFields);

        $user = [];
        require_once $global['systemRootPath'] . 'objects/userGroups.php';
        //echo $sql;exit;
        $res = sqlDAL::readSql($sql . ";");
        $downloadedArray = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        if ($res !== false) {
            foreach ($downloadedArray as $row) {
                $row['creator'] = Video::getCreatorHTML($row['id'], '', true, true);
                $row = cleanUpRowFromDatabase($row);
                $user[] = self::getUserInfoFromRow($row);
            }
        } else {
            $user = false;
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        return $user;
    }

    public static function getTotalUsersFromUsergroup($users_groups_id, $ignoreAdmin = false, $status = "")
    {
        if (!Permissions::canAdminUsers() && !$ignoreAdmin) {
            return false;
        }
        $users_groups_id = intval($users_groups_id);
        if (empty($users_groups_id)) {
            return false;
        }
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT id FROM users u WHERE 1=1  ";

        $queryIds = [];
        if (empty($_REQUEST['userGroupShowOnly']) || $_REQUEST['userGroupShowOnly'] == 'permanent') {
            $queryIds[] = " id IN (SELECT users_id FROM users_has_users_groups ug WHERE ug.users_groups_id = {$users_groups_id}) ";
        }
        if (empty($_REQUEST['userGroupShowOnly']) || $_REQUEST['userGroupShowOnly'] == 'dynamic') {
            $ids = AVideoPlugin::getDynamicUsersId($users_groups_id);
            if (!empty($ids) && is_array($ids)) {
                $ids = array_unique($ids);
                $queryIds[] = " id IN ('" . implode("','", $ids) . "') ";
            }
        }
        if (!empty($queryIds)) {
            $sql .= " AND ( ";
            $sql .= implode(' OR ', $queryIds);
            $sql .= " ) ";
        } else {
            // do not return nothing
            $sql .= " AND u.id < 0 ";
        }

        if (!empty($status)) {
            if (strtolower($status) === 'i') {
                $sql .= " AND status = 'i' ";
            } else {
                $sql .= " AND status = 'a' ";
            }
        }
        $sql .= BootGrid::getSqlSearchFromPost(['name', 'email', 'user']);

        $res = sqlDAL::readSql($sql);
        $result = sqlDal::num_rows($res);
        sqlDAL::close($res);

        return $result;
    }

    public static function getAllUsers($ignoreAdmin = false, $searchFields = ['name', 'email', 'user', 'channelName', 'about'], $status = "", $isAdmin = null, $isCompany = null, $canUpload = null)
    {
        if (!Permissions::canAdminUsers() && !$ignoreAdmin) {
            _error_log('You are not admin and cannot list all users');
            //echo __LINE__;
            return false;
        }
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT * ";

        if (!empty($_REQUEST['getUsage'])) {
            $sql .= ", (SELECT sum(filesize) as total FROM videos WHERE filesize > 0 AND (users_id = u.id)) as usageInBytes";
        }

        $sql .= " FROM users u WHERE 1=1 ";
        if (!empty($status)) {
            if (strtolower($status) === 'i') {
                $sql .= " AND status = 'i' ";
            } else {
                $sql .= " AND status = 'a' ";
            }
        }
        if (isset($isAdmin)) {
            if (empty($isAdmin)) {
                $sql .= " AND isAdmin = 0 ";
            } else {
                $sql .= " AND isAdmin = 1 ";
            }
        }
        if (isset($canUpload)) {
            if (empty($canUpload)) {
                $sql .= " AND canUpload = 0 ";
            } else {
                $sql .= " AND canUpload = 1 ";
            }
        }
        if (isset($isCompany)) {
            if (!empty($isCompany) && $isCompany == self::$is_company_status_ISACOMPANY || $isCompany == self::$is_company_status_WAITINGAPPROVAL) {
                $sql .= " AND is_company = $isCompany ";
            } else {
                $sql .= " AND (is_company = 0 OR is_company IS NULL) ";
            }
        }
        $sql .= BootGrid::getSqlFromPost($searchFields);
        //var_dump($isCompany, $sql);exit;
        $user = [];
        require_once $global['systemRootPath'] . 'objects/userGroups.php';
        $res = sqlDAL::readSql($sql . ";");
        $downloadedArray = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        if ($res !== false) {
            foreach ($downloadedArray as $row) {
                $row['creator'] = Video::getCreatorHTML($row['id'], '', true, true);
                $row = self::getUserInfoFromRow($row);
                $row = cleanUpRowFromDatabase($row);
                $user[] = $row;
            }
        } else {
            $user = false;
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        return $user;
    }

    public static function getAllActiveUsersThatCanUpload()
    {
        if (!Permissions::canAdminUsers()) {
            return false;
        }
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT * FROM users WHERE 1=1 AND status = 'a' AND (canUpload = 1 OR isAdmin = 1) ";

        $user = [];
        $res = sqlDAL::readSql($sql . ";");
        $downloadedArray = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        if ($res !== false) {
            foreach ($downloadedArray as $row) {
                $row = cleanUpRowFromDatabase($row);
                $user[] = $row;
            }
        } else {
            $user = false;
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        return $user;
    }

    public static function decodeExternalOption($externalOptions)
    {
        if (is_string($externalOptions)) {
            $externalOptions = unserialize(base64_decode($externalOptions));
        }
        return $externalOptions;
    }

    private static function getUserInfoFromRow($row)
    {
        $row['groups'] = UserGroups::getUserGroups($row['id']);
        $row['identification'] = self::getNameIdentificationById($row['id']);
        $row['photo'] = self::getPhoto($row['id']);
        $row['background'] = self::getBackground($row['id']);
        $row['tags'] = self::getTags($row['id']);
        $row['name'] = @preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $row['name']);
        $row['isEmailVerified'] = $row['emailVerified'];
        if (!is_null($row['externalOptions'])) {
            $externalOptions = self::decodeExternalOption($row['externalOptions']);
            if (is_array($externalOptions) && sizeof($externalOptions) > 0) {
                foreach ($externalOptions as $k => $v) {
                    if ($v == "true") {
                        $v = 1;
                    } elseif ($v == "false") {
                        $v = 0;
                    }
                    $row[$k] = $v;
                }
            }
        }
        unset($row['password'], $row['recoverPass']);
        if (!Permissions::canAdminUsers() && $row['id'] !== User::getId()) {
            unset(
                $row['first_name'],
                $row['last_name'],
                $row['address'],
                $row['zip_code'],
                $row['country'],
                $row['region'],
                $row['city']
            );
        }
        return $row;
    }

    public static function getAllUsersThatHasVideos($ignoreAdmin = false)
    {
        if (!self::isAdmin() && !$ignoreAdmin) {
            return false;
        }
        global $global;
        $sql = "SELECT * FROM users u WHERE status = 'a' AND (canUpload = 1 || isAdmin = 1) AND "
            . " (SELECT count(id) FROM videos where users_id = u.id )>0 ";

        $user = [];
        $res = sqlDAL::readSql($sql . ";");
        $downloadedArray = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        if ($res !== false) {
            foreach ($downloadedArray as $row) {
                $row = cleanUpRowFromDatabase($row);
                $user[] = $row;
            }
        } else {
            $user = false;
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        return $user;
    }

    public static function getTotalUsers($ignoreAdmin = false, $status = "", $isAdmin = null, $isCompany = null, $canUpload = null)
    {
        if (!Permissions::canAdminUsers() && !$ignoreAdmin) {
            return false;
        }
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        $sql = "SELECT id FROM users WHERE 1=1  ";

        if (!empty($status)) {
            if (strtolower($status) === 'i') {
                $sql .= " AND status = 'i' ";
            } else {
                $sql .= " AND status = 'a' ";
            }
        }
        if (isset($isAdmin)) {
            if (empty($isAdmin)) {
                $sql .= " AND isAdmin = 0 ";
            } else {
                $sql .= " AND isAdmin = 1 ";
            }
        }
        if (isset($canUpload)) {
            if (empty($canUpload)) {
                $sql .= " AND canUpload = 0 ";
            } else {
                $sql .= " AND canUpload = 1 ";
            }
        }
        if (isset($isCompany)) {
            if (!empty($isCompany) && $isCompany == self::$is_company_status_ISACOMPANY || $isCompany == self::$is_company_status_WAITINGAPPROVAL) {
                $sql .= " AND is_company = $isCompany ";
            } else {
                $sql .= " AND (is_company = 0 OR is_company IS NULL) ";
            }
        }
        $sql .= BootGrid::getSqlSearchFromPost(['name', 'email', 'user']);

        $res = sqlDAL::readSql($sql);
        $result = sqlDal::num_rows($res);
        sqlDAL::close($res);

        return $result;
    }

    public static function userExists($user)
    {
        global $global;
        $user = ($user);
        $sql = "SELECT * FROM users WHERE user = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$user]);
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);

        if ($user !== false) {
            return $user['id'];
        } else {
            return false;
        }
    }

    public static function idExists($users_id)
    {
        global $global;
        $users_id = intval($users_id);
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$users_id]);
        $user = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($user !== false) {
            return $user['id'];
        } else {
            return false;
        }
    }

    public static function createUserIfNotExists($user, $pass, $name, $email, $photoURL, $isAdmin = false, $emailVerified = false)
    {
        global $global, $advancedCustomUser;
        $userId = 0;
        if (!$userId = self::userExists($user)) {
            if (empty($pass)) {
                $pass = uniqid();
            }
            $pass = encryptPassword($pass);
            $userObject = new User(0, $user, $pass);
            $userObject->setEmail($email);
            $userObject->setName($name);
            $userObject->setIsAdmin($isAdmin);
            $userObject->setPhotoURL($photoURL);
            $userObject->setEmailVerified($emailVerified);
            $userId = $userObject->save();
            if (!empty($userId)) {
                if (!empty($advancedCustomUser->userDefaultUserGroup->value)) { // for new users use the default usergroup
                    UserGroups::updateUserGroups($userId, [$advancedCustomUser->userDefaultUserGroup->value], true);
                }
            }
            return $userId;
        } else {
            $userObj = new User($userId);
            //$userObj->setEmail($email);
            //$userObj->setName($name);
            //$userObj->setPhotoURL($photoURL);
            if ($emailVerified) {
                $userObj->setEmailVerified(1);
            }
            $userObj->save();
        }
        return $userId;
    }

    /**
     * @return string
     */
    public function getRecoverPass()
    {
        return $this->recoverPass;
    }

    public function setRecoverPass($forceChange = false)
    {
        // let the same recover pass if it was 10 minutes ago
        if ($this->isRecoverPassValid($this->recoverPass) && empty($forceChange) && !empty($this->recoverPass) && !empty($recoverPass) && !empty($this->modified) && strtotime($this->modified) > strtotime("-10 minutes")) {

            _error_log("setRecoverPass:isRecoverPassValid {$this->modified}");
            return $this->recoverPass;
        }
        $this->recoverPass = $this->createRecoverPass($this->id);

        _error_log("setRecoverPass:created " . json_encode(!empty($this->recoverPass)));
        return $this->recoverPass;
    }

    private function createRecoverPass($id, $secondsValid = 600)
    {
        $json = new stdClass();
        $json->id = $id;
        $json->uniqid = uniqid();
        $json->valid = strtotime("+{$secondsValid} seconds");
        _error_log("createRecoverPass " . getRealIpAddr() . ' ' . json_encode($_SERVER));
        return encryptString(json_encode($json));
    }

    public function checkRecoverPass($recoverPass)
    {
        if ($this->recoverPass === $recoverPass) {
            if ($this->isRecoverPassValid($recoverPass)) {
                _error_log('checkRecoverPass success: ' . $this->user . ' ' . getRealIpAddr());
                return true;
            }
        }
        return false;
    }

    public function isRecoverPassValid($recoverPass)
    {
        $string = decryptString($recoverPass);
        if ($string) {
            $json = _json_decode($string);
            if (is_object($json)) {
                if (time() < $json->valid) {
                    if ($this->id < $json->id) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function canNotUploadReason($doNotCheckPlugins = false)
    {
        global $global, $config, $advancedCustomUser;
        $reason = [];
        if (empty($doNotCheckPlugins) && !AVideoPlugin::userCanUpload(User::getId())) {
            $reason[] = 'A plugin said users_id=[' . User::getId() . '] cannot upload';
        }

        if ((isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && $advancedCustomUser->onlyVerifiedEmailCanUpload && !User::isVerified())) {
            $reason[] = 'The email is not verified';
        }

        if ($config->getAuthCanUploadVideos() && !self::isLogged()) {
            $reason[] = 'The user is not logged';
        }
        if (self::isLogged() && !empty($_SESSION['user']['canUpload'])) {
            $reason[] = 'You do not have upload rights';
        }
        return $reason;
    }


    public static function canUpload($doNotCheckPlugins = false)
    {
        global $global, $config, $advancedCustomUser;
        if (Permissions::canModerateVideos()) {
            return true;
        }
        if (User::isAdmin()) {
            return true;
        }
        if (empty($doNotCheckPlugins) && !AVideoPlugin::userCanUpload(User::getId(), true)) {
            return false;
        }

        if ((isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && $advancedCustomUser->onlyVerifiedEmailCanUpload && !User::isVerified())) {
            return false;
        }

        if ($config->getAuthCanUploadVideos()) {
            return self::isLogged();
        }
        if (self::isLogged() && !empty($_SESSION['user']['canUpload'])) {
            return true;
        }
        return self::isAdmin() || AVideoPlugin::userCanUpload(User::getId(), false);
    }

    public static function canViewChart()
    {
        global $global, $config;
        if (self::isLogged() && !empty($_SESSION['user']['canViewChart'])) {
            return true;
        }
        return self::isAdmin();
    }

    public static function canCreateMeet()
    {
        $p = AVideoPlugin::isEnabledByName('Meet');
        if (empty($p)) {
            return false;
        }

        return Meet::canCreateMeet();
    }

    public static function canComment()
    {
        global $global, $config, $advancedCustomUser;
        if (self::isAdmin()) {
            return true;
        }

        if (Permissions::canAdminComment()) {
            return true;
        }

        if ($config->getAuthCanComment()) {
            if (empty($advancedCustomUser->unverifiedEmailsCanNOTComment)) {
                return self::isLogged();
            } else {
                return self::isVerified();
            }
        }
        return false;
    }

    public static function canSeeCommentTextarea()
    {
        global $global, $config;
        if (!$config->getAuthCanComment()) {
            if (!self::isAdmin()) {
                return false;
            }
        }
        return true;
    }

    public function getUserGroups()
    {
        return $this->userGroups;
    }

    public function setUserGroups($userGroups)
    {
        if (is_array($userGroups)) {
            $this->userGroups = $userGroups;
        }
    }

    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param string $user_id
     * text
     * label Default Primary Success Info Warning Danger
     */
    public static function getTags($user_id)
    {
        $user = new User($user_id);
        $tags = [];
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

        if ($status = $user->getIs_company()) {
            $obj = new stdClass();
            if ($status !== self::$is_company_status_ISACOMPANY) {
                $obj->type = "warning";
            } else {
                $obj->type = "success";
            }
            $obj->text = '<i class="fas fa-building"></i> ' . __(self::$is_company_status[$status]);
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
        if ($user->getEmailVerified()) {
            $obj = new stdClass();
            $obj->type = "success";
            $obj->text = __("E-mail Verified");
            $tags[] = $obj;
        } else {
            $obj = new stdClass();
            $obj->type = "warning";
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
            $obj->text = (!empty($value['isDynamic']) ? '<i class="fas fa-link"></i>' : '<i class="fas fa-lock"></i>') . ' ' . $value['group_name'];
            $tags[] = $obj;
        }

        return $tags;
    }

    public function getBackgroundURL($type = '', $ignoreGeneric = false)
    {
        global $global;
        $this->backgroundURL = self::getBackgroundURLFromUserID($this->id, $type, $ignoreGeneric);
        return $this->backgroundURL;
    }

    public static function getBackgroundURLFromUserID($users_id = 0, $type = '', $ignoreGeneric = false)
    {
        global $global;
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        $dir = "videos/userPhoto/{$users_id}/";
        make_path("{$global['systemRootPath']}{$dir}");
        $suffix = '';
        if (!empty($type)) {
            $suffix = "_{$type}";
        }
        global $global;
        $backgroundURL = "{$dir}background{$suffix}.jpg";
        if (empty($ignoreGeneric) && !file_exists($global['systemRootPath'] . $backgroundURL)) {
            $backgroundURL = "{$dir}background{$suffix}.png";
        }
        if (!file_exists($global['systemRootPath'] . $backgroundURL)) {
            $backgroundURL = "videos/userPhoto/background{$users_id}.jpg";
        }
        if (!file_exists($global['systemRootPath'] . $backgroundURL)) {
            $backgroundURL = "videos/userPhoto/background{$users_id}.png";
        }
        if (empty($ignoreGeneric) && !file_exists($global['systemRootPath'] . $backgroundURL)) {
            $backgroundURL = "view/img/background{$suffix}.jpg";
        }
        return $backgroundURL;
    }

    public function setBackgroundURL($backgroundURL)
    {
        $this->backgroundURL = strip_tags($backgroundURL);
    }

    public function getChannelName()
    {
        if (empty($this->channelName)) {
            if (empty($this->user)) {
                return 'Unknow Channel Name';
            }
            $this->channelName = self::_recommendChannelName($this->channelName, 0, $this->user, $this->id);
            $this->save();
        }
        return $this->channelName;
    }

    public static function _getUserChannelName($users_id = 0)
    {
        global $global, $config;
        if (empty($users_id)) {
            $users_id = self::getId();
        }
        $user = new User($users_id);
        if (empty($user)) {
            return false;
        }

        return $user->getChannelName();
    }

    public function getEmailVerified()
    {
        return intval($this->emailVerified);
    }


    public static function _getEmailVerified($users_id = 0)
    {
        global $global, $config;
        if (empty($users_id)) {
            $users_id = self::getId();
        }
        $user = new User($users_id);
        if (empty($user)) {
            return false;
        }

        return $user->getEmailVerified();
    }

    public static function validateChannelName($channelName)
    {
        return trim(preg_replace("/[^0-9A-Z_-]/i", "", ucwords($channelName)));
    }

    /**
     *
     * @param string $channelName
     * @return boolean return true is is unique
     */
    public function setChannelName($channelName)
    {
        $channelName = self::validateChannelName($channelName);
        $user = static::getChannelOwner($channelName);
        if (!empty($user)) { // if the channel name exists and it is not from this user, rename the channel name
            if (empty($this->id) || $user['id'] != $this->id) {
                _error_log("setChannelName: name NOT UNIQUE [{$channelName}] found on user=[{$user['user']}] id=[{$user['id']}]");
                return false;
            }
        }
        $this->channelName = xss_esc($channelName);
        return true;
    }

    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = (empty($emailVerified) || strtolower($emailVerified) === 'false') ? 0 : 1;
    }

    public static function getChannelLink($users_id = 0)
    {
        global $global;
        $name = self::_getChannelName($users_id);
        if (empty($name)) {
            return false;
        }
        $link = "{$global['webSiteRootURL']}channel/" . urlencode($name);
        return $link;
    }

    public static function getChannelLinkFromChannelName($channelName)
    {
        global $global;
        $link = "{$global['webSiteRootURL']}channel/" . urlencode($channelName);
        return $link;
    }

    public static function _getChannelName($users_id = 0)
    {
        global $global, $config;
        if (empty($users_id)) {
            $users_id = self::getId();
        }
        $user = new User($users_id);
        if (empty($user)) {
            return '';
        }
        if (empty($user->getChannelName())) {
            $name = $user->getBdId();
        } else {
            $name = $user->getChannelName();
        }
        return $name;
    }

    public static function sendVerificationLink($users_id)
    {
        global $global, $advancedCustomUser, $_sendVerificationLink_sent;

        if (empty($users_id)) {
            _error_log("sendVerificationLink: empty user");
            return false;
        }

        $user = new User($users_id);
        if (empty($user->getUser())) {
            _error_log("sendVerificationLink: user not found {$users_id}");
            return false;
        }
        if (!isset($_sendVerificationLink_sent)) {
            $_sendVerificationLink_sent = [];
        }
        //Only send the verification email each 30 minutes
        if (!empty($_sendVerificationLink_sent[$users_id])) {
            _error_log("sendVerificationLink: Email already sent, we will wait 30 min  {$users_id}");
            return true;
        }
        $_sendVerificationLink_sent[$users_id] = 1;
        //Only send the verification email each 30 minutes
        if (!empty($_SESSION["sendVerificationLink"][$users_id]) && (time() - $_SESSION["sendVerificationLink"][$users_id]) < 1800) {
            _error_log("sendVerificationLink: Email already sent, we will wait 30 min  {$users_id}");
            return true;
        }
        try {
            require_once $global['systemRootPath'] . 'objects/configuration.php';
            if (class_exists('AVideoConf')) {
                $config = new AVideoConf();
            }
            $code = urlencode(static::createVerificationCode($users_id));
            //Create a new PHPMailer instance
            if (!is_object($config)) {
                _error_log("sendVerificationLink: config is not a object " . json_encode($config));
                return false;
            }
            $webSiteTitle = $config->getWebSiteTitle();
            /**
             * @var string $email
             */
            $email = '';
            $email = $user->getEmail();

            $msg = $user->getName();
            $msg .= "<br><br>" . __($advancedCustomUser->verificationMailTextLine1);
            $msg .= "<br><br>" . sprintf(__($advancedCustomUser->verificationMailTextLine2), $webSiteTitle);
            $msg .= "<br><br>" . sprintf(__($advancedCustomUser->verificationMailTextLine3), $webSiteTitle);
            $msg .= "<br><br>" . __($advancedCustomUser->verificationMailTextLine4);
            $msg .= "<br><br>";
            $msg .= "<a href='{$global['webSiteRootURL']}objects/userVerifyEmail.php?code={$code}' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer; border-radius: 5px;'>" . __($advancedCustomUser->verificationMailButtonLabel);
            $msg .= "</a>";


            $resp = sendSiteEmail($user->getEmail(), __('Please Verify Your E-mail ') . ' ' . $webSiteTitle, $msg);

            if (!$resp) {
                _error_log("sendVerificationLink Error");
            } else {
                _error_log("sendVerificationLink: SUCCESS {$users_id} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
                _session_start();
                $_SESSION["sendVerificationLink"][$users_id] = time();
            }
            return $resp;
        } catch (Exception $e) {
            _error_log($e->getMessage()); //Boring error messages from anything else!
        }
        return false;
    }

    public static function verifyCode($code)
    {
        global $global;
        $obj = static::decodeVerificationCode($code);
        $salt = hash('sha256', $global['salt']);
        if ($salt !== $obj->salt) {
            _error_log('verifyCode salt is wrong');
            return false;
        }
        $user = new User($obj->users_id);
        $recoverPass = $user->getRecoverPass();
        if ($recoverPass == $obj->recoverPass) {
            $user->setEmailVerified(1);
            $users_id = $user->save();
            if (empty($users_id)) {
                _error_log('verifyCode could not save user');
            } else {
                _error_log('verifyCode user save');
                return true;
            }
        } else {
            _error_log("verifyCode wrong recover pass for user {$obj->users_id} [{$recoverPass}] != [{$obj->recoverPass}]");
        }
        return false;
    }

    public static function createVerificationCode($users_id)
    {
        global $global, $_createVerificationCode;

        if (empty($users_id)) {
            return false;
        }

        if (!isset($_createVerificationCode)) {
            $_createVerificationCode = [];
        }

        if (empty($_createVerificationCode[$users_id])) {
            $obj = new stdClass();
            $obj->users_id = $users_id;
            $obj->salt = hash('sha256', $global['salt']);

            $user = new User($users_id);
            $obj->recoverPass = $user->setRecoverPass();
            $saved = $user->save();
            _error_log("createVerificationCode: save " . json_encode($saved));
            $_createVerificationCode[$users_id] = base64_encode(json_encode($obj));
        }

        return $_createVerificationCode[$users_id];
    }

    public static function decodeVerificationCode($code)
    {
        $obj = _json_decode(base64_decode($code));
        return $obj;
    }

    public function getFirst_name()
    {
        return $this->first_name;
    }

    public function getLast_name()
    {
        return $this->last_name;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getZip_code()
    {
        return $this->zip_code;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setFirst_name($first_name)
    {
        $this->first_name = $first_name;
    }

    public function setLast_name($last_name)
    {
        $this->last_name = $last_name;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function setZip_code($zip_code)
    {
        $this->zip_code = $zip_code;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function setRegion($region)
    {
        $this->region = $region;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public static function getDocumentImage($users_id)
    {
        $row = static::getBlob($users_id, User::$DOCUMENT_IMAGE_TYPE);
        if (!empty($row['blob'])) {
            return $row['blob'];
        }
        return false;
    }

    public static function saveDocumentImage($image, $users_id)
    {
        $row = static::saveBlob($image, $users_id, User::$DOCUMENT_IMAGE_TYPE);
        if (!empty($row['blob'])) {
            return $row['blob'];
        }
        return false;
    }

    public static function getBlob($users_id, $type)
    {
        global $global;
        $sql = "SELECT * FROM users_blob WHERE users_id = ? AND `type` = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "is", [$users_id, $type]);
        $result = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $result;
    }

    public static function saveBlob($blob, $users_id, $type)
    {
        global $global;
        $row = self::getBlob($users_id, $type);
        $null = null;
        /**
         *
         * @var array $global
         * @var object $global['mysqli']
         */
        if (!empty($row['id'])) {
            $sql = "UPDATE users_blob SET `blob` = ? , modified = now() WHERE id = ?";
            $stmt = $global['mysqli']->prepare($sql);
            $stmt->bind_param('bi', $null, $row['id']);
        } else {
            $sql = "INSERT INTO users_blob (`blob`, users_id, `type`, modified, created) VALUES (?,?,?, now(), now())";
            $stmt = $global['mysqli']->prepare($sql);
            $stmt->bind_param('bis', $null, $users_id, $type);
        }

        $stmt->send_long_data(0, $blob);

        return $stmt->execute();
    }

    public static function deleteBlob($users_id, $type)
    {
        global $global;
        $row = self::getBlob($users_id, $type);
        if (!empty($row['id'])) {
            $sql = "DELETE FROM users_blob ";
            $sql .= " WHERE id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", [$row['id']]);
        }
        _error_log("Id for table users_blob not defined for deletion");
        return false;
    }

    public function getDonationLink()
    {
        return $this->donationLink;
    }

    public function getDonationLinkIfEnabled()
    {
        global $advancedCustomUser;
        if ($advancedCustomUser->allowDonationLink) {
            return $this->donationLink;
        }
        return false;
    }

    public function setDonationLink($donationLink)
    {
        $this->donationLink = $donationLink;
    }

    public static function donationLink()
    {
        if (self::isLogged()) {
            return $_SESSION['user']['donationLink'];
        } else {
            return '';
        }
    }

    public static function loginFromRequestIfNotLogged()
    {
        if (User::isLogged()) {
            return self::USER_LOGGED;
        }
        return self::loginFromRequest();
    }

    public static function loginFromRequest()
    {
        inputToRequest();
        if (!empty($_REQUEST['do_not_login'])) {
            return false;
        }
        if (empty($_REQUEST['pass']) && !empty($_REQUEST['password'])) {
            $_REQUEST['pass'] = $_REQUEST['password'];
        }

        $response = false;
        if (!empty($_REQUEST['user']) && !empty($_REQUEST['pass'])) {
            unset($_SESSION['user']);
            $user = new User(0, $_REQUEST['user'], $_REQUEST['pass']);
            $response = $user->login(false, !empty($_REQUEST['encodedPass']));
            if ($response !== self::USER_LOGGED) {
                //_error_log("loginFromRequest trying again");
                $response = $user->login(false, empty($_REQUEST['encodedPass']));
            }
            if ($response) {
                switch ($response) {
                    case self::USER_LOGGED:
                        $global['bypassSameDomainCheck'] = 1;
                        _error_log("loginFromRequest SUCCESS {$_REQUEST['user']}");
                        break;
                    case self::USER_NOT_FOUND:
                        _error_log("loginFromRequest NOT FOUND {$_REQUEST['user']}");
                        break;
                    case self::USER_NOT_VERIFIED:
                        _error_log("loginFromRequest NOT VERIFIED {$_REQUEST['user']}");
                        break;
                    case self::CAPTCHA_ERROR:
                        _error_log("loginFromRequest CAPTCHA_ERROR {$_REQUEST['user']}");
                        break;
                    case self::REQUIRE2FA:
                        _error_log("loginFromRequest REQUIRE2FA {$_REQUEST['user']}");
                        break;
                    case self::SYSTEM_ERROR:
                        _error_log("loginFromRequest SYSTEM_ERROR {$_REQUEST['user']}");
                        break;
                    default:
                        _error_log("loginFromRequest UNDEFINED {$_REQUEST['user']}");
                        break;
                }
            } else {
                //_error_log("loginFromRequest ERROR {$_REQUEST['user']}");
            }
            $_REQUEST['do_not_login'] = 1;
        }
        return $response;
    }

    public static function loginFromRequestToGet()
    {
        if (!empty($_REQUEST['user']) && !empty($_REQUEST['pass'])) {
            $return = "user={$_REQUEST['user']}&pass={$_REQUEST['pass']}";
            if (!empty($_REQUEST['encodedPass'])) {
                $return .= "&encodedPass=" . intval($_REQUEST['encodedPass']);
            }
            return $return;
        }
        return "";
    }

    public static function getAddChannelToGalleryButton($users_id)
    {
        $gallery = AVideoPlugin::isEnabledByName('Gallery');
        if (!empty($gallery)) {
            return Gallery::getAddChannelToGalleryButton($users_id);
        } else {
            $gallery = AVideoPlugin::isEnabledByName('YouPHPFlix2');
            if (!empty($gallery)) {
                return YouPHPFlix2::getAddChannelToYouPHPFlix2Button($users_id);
            }
        }
        return '';
    }

    public static function getBlockUserButton($users_id)
    {
        $canBlock = self::userCanBlockUserWithReason($users_id);
        if (!$canBlock->result) {
            return "<!-- {$canBlock->msg} -->";
        }
        return ReportVideo::buttonBlockUser($users_id);
    }

    public static function getActionBlockUserButton($users_id)
    {
        $canBlock = self::userCanBlockUserWithReason($users_id);
        if (!$canBlock->result) {
            return "<!-- {$canBlock->msg} -->";
        }
        return "<!-- getActionBlockUserButton($users_id) -->" . ReportVideo::actionButtonBlockUser($users_id);
    }

    public static function userCanBlockUser($users_id, $ignoreIfIsAlreadyBLocked = false)
    {
        if (empty($users_id)) {
            return false;
        }
        if (!User::isLogged()) {
            return false;
        }
        if ($users_id == User::getId()) {
            return false;
        }
        if (empty($ignoreIfIsAlreadyBLocked)) {
            $report = AVideoPlugin::getDataObjectIfEnabled("ReportVideo");
            if (empty($report)) {
                return false;
            }
        }
        return true;
    }

    public static function userCanBlockUserWithReason($users_id, $ignoreIfIsAlreadyBLocked = false)
    {
        $obj = new stdClass();
        $obj->result = false;
        $obj->msg = "Unkonw";

        if (empty($users_id)) {
            $obj->msg = "Empty User ID";
            return $obj;
        }
        if (!User::isLogged()) {
            $obj->msg = "You are not logged";
            return $obj;
        }
        if ($users_id == User::getId()) {
            $obj->msg = "You cannot block your own video";
            return $obj;
        }
        if (empty($ignoreIfIsAlreadyBLocked)) {
            $report = AVideoPlugin::getDataObjectIfEnabled("ReportVideo");
            if (empty($report)) {
                $obj->msg = "this user is already blocked";
                return $obj;
            }
        }

        $obj->result = true;
        $obj->msg = "You can block";
        return $obj;
    }

    public static function hasBlockedUser($reported_users_id, $users_id = 0)
    {
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        if (!self::userCanBlockUser($reported_users_id, true)) {
            return false;
        }
        $report = AVideoPlugin::getDataObjectIfEnabled("ReportVideo");
        if (!empty($report)) {
            return ReportVideo::isBlocked($reported_users_id, $users_id);
        } else {
            return false;
        }
    }

    public function updateUserImages($params = [])
    {
        $id = $this->id;
        $obj = new stdClass();

        // Update Background Image
        if (isset($params['backgroundImg']) && $params['backgroundImg'] !== '') {
            $background = url_get_contents($params['backgroundImg']);
            $ext = pathinfo(parse_url($params['backgroundImg'], PHP_URL_PATH), PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'gif', 'png'];
            if (!in_array(strtolower($ext), $allowed)) {
                return "File extension error background Image, We allow only (" . implode(",", $allowed) . ")";
            }

            $backgroundPath = "videos/userPhoto/tmp_background{$id}." . $ext;
            $oldfile = "videos/userPhoto/background{$id}.png";
            $file = "videos/userPhoto/background{$id}.jpg";

            if (!isset($global['systemRootPath'])) {
                $global['systemRootPath'] = '../../';
            }

            $filePath = $global['systemRootPath'] . $backgroundPath;

            $updateBackground = file_put_contents($filePath, $background);

            convertImage($filePath, $global['systemRootPath'] . $file, 70);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if (file_exists($oldfile)) {
                unlink($oldfile);
            }

            if ($updateBackground) {
                $obj->background = 'Background has been updated!';
            } else {
                $obj->background = 'Error updating background.';
            }

            $this->setBackgroundURL($file);
        }

        // Update Profile Image
        if (isset($params['profileImg']) && $params['profileImg'] !== '') {
            $photo = url_get_contents($params['profileImg']);
            $photoPath = "videos/userPhoto/photo{$id}.png";

            if (!isset($global['systemRootPath'])) {
                $global['systemRootPath'] = '../../';
            }

            $filePath = $global['systemRootPath'] . $photoPath;

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $updateProfile = file_put_contents($filePath, $photo);
            if ($updateProfile) {
                $obj->profile = 'Profile has been updated!';
            } else {
                $obj->profile = 'Error updating profile.';
            }

            $this->setPhotoURL($photoPath);
        }

        $formats = "ssi";
        $values[] = $this->photoURL;
        $values[] = $this->backgroundURL;
        $values[] = $this->id;

        $sql = "UPDATE users SET "
            . "photoURL = ?, backgroundURL = ?, "
            . " modified = now() WHERE id = ?";

        $insert_row = sqlDAL::writeSql($sql, $formats, $values);
        $obj->save = $insert_row; // create/update data for photoURL / backgroundURL

        return $obj;
    }

    public function getExtra_info()
    {
        return $this->extra_info;
    }

    public function setExtra_info($extra_info)
    {
        $this->extra_info = $extra_info;
    }

    public static function saveExtraInfo($string, $users_id)
    {
        $sql = "UPDATE users SET "
            . "extra_info = ?, "
            . " modified = now() WHERE id = ?";

        return sqlDAL::writeSql($sql, "si", [$string, $users_id]);
    }

    public static function userGroupsMatch($user_groups, $users_id = 0)
    {
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        if (empty($user_groups)) {
            return true;
        }
        if (empty($users_id)) {
            return false;
        }
        if (!is_array($user_groups)) {
            $user_groups = [$user_groups];
        }
        $user_users_groups = UserGroups::getUserGroups($users_id);
        if (empty($user_users_groups)) {
            return false;
        }
        foreach ($user_users_groups as $value) {
            if (in_array($value['id'], $user_groups)) {
                return true;
            }
        }
        return false;
    }

    public static function getExtraSubscribers($users_id)
    {
        global $config;
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($obj)) {
            return 0;
        }
        $user = new User($users_id);
        $value = $user->getExternalOptions('ExtraSubscribers');
        return intval($value);
    }

    public static function setExtraSubscribers($users_id, $value)
    {
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($obj) || !User::isAdmin()) {
            return false;
        }
        $user = new User($users_id);
        return $user->addExternalOptions('ExtraSubscribers', intval($value));
    }

    public static function getProfilePassword($users_id = '')
    {
        global $config;
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($obj)) {
            return false;
        }
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        $user = new User($users_id);
        $value = $user->getExternalOptions('ProfilePassword');
        return $value;
    }


    public static function getSocialMediaURL($socialMedia, $users_id = '')
    {
        global $config;
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($obj)) {
            return false;
        }
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        $user = new User($users_id);
        $value = $user->getExternalOptions($socialMedia);
        return $value;
    }

    public static function getWebsite($users_id = '')
    {
        return self::getSocialMediaURL('website', $users_id);
    }

    public static function setProfilePassword($users_id, $value)
    {
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($obj) || !User::isAdmin()) {
            return false;
        }
        $user = new User($users_id);
        return $user->addExternalOptions('ProfilePassword', preg_replace('/[^0-9a-z]/i', '', $value));
    }

    public static function getDonationButtons($users_id = '')
    {
        global $config;
        $obj = AVideoPlugin::getObjectDataIfEnabled("CustomizeUser");
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        if (empty($obj) || empty($users_id)) {
            return false;
        }
        $user = new User($users_id);
        $value = $user->getExternalOptions('DonationButtons');
        $json = _json_decode($value);
        if (empty($json)) {
            return [];
        }
        return $json;
    }

    public static function setDonationButtons($users_id, $value)
    {
        $obj = AVideoPlugin::getObjectData("CustomizeUser");
        $user = new User($users_id);
        if (!is_string($value)) {
            $value = _json_encode($value);
        }
        return $user->addExternalOptions('DonationButtons', $value);
    }

    public static function getRedirectCustomUrl($users_id = '')
    {
        global $config;
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        $user = new User($users_id);
        $value = $user->getExternalOptions('redirectCustomUrl');
        $json = _json_decode($value, true);
        if (empty($json)) {
            return ['url' => '', 'msg' => '', 'autoRedirect' => 0];
        }
        return $json;
    }

    public static function setRedirectCustomUrl($users_id, $value)
    {
        $user = new User($users_id);
        if (!is_string($value)) {
            $value = _json_encode($value);
        }
        return $user->addExternalOptions('redirectCustomUrl', $value);
    }


    static function getChannelPanel($users_id)
    {
        global $advancedCustom;
        $u = new User($users_id);
        if (empty($u->getUser())) {
            echo "<!-- user ID not found {$users_id} -->";
            return false;
        }
        $objGallery = AVideoPlugin::getObjectData("Gallery");
        $get = ['channelName' => $u->getChannelName()];
        $current = getCurrentPage();
        $rowCount = getRowCount();
        $sort = @$_POST['sort'];
        $_POST['current'] = 1;
        $_REQUEST['rowCount'] = $objGallery->screenColsLarge;
        $_POST['sort']['created'] = "DESC";
        $uploadedVideos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, $users_id);
        $_POST['current'] = $current;
        $_REQUEST['rowCount'] = $rowCount;
        $_POST['sort'] = $sort;
        if (empty($uploadedVideos)) {
            return '';
        }
        if (empty($advancedCustom)) {
            $advancedCustom = AVideoPlugin::getDataObject('CustomizeAdvanced');
        }
?>
        <div class="panel panel-default">
            <div class="panel-heading" style="position: relative;">
                <img src="<?php echo User::getPhoto($users_id); ?>" class="img img-thumbnail img-responsive pull-left" style="max-height: 100px; margin: 0 10px;" alt="User Photo" />
                <a href="<?php echo User::getChannelLink($users_id); ?>" class="btn btn-default">
                    <i class="fas fa-play-circle"></i>
                    <?php echo User::getNameIdentificationById($users_id); ?>
                </a>
                <div class="pull-right">
                    <?php echo User::getAddChannelToGalleryButton($users_id); ?>
                    <?php echo User::getBlockUserButton($users_id); ?>
                    <?php echo Subscribe::getButton($users_id); ?>
                    <?php echo CustomizeUser::getCallerButton($users_id, 'btn-xs'); ?>
                </div>
            </div>
            <div class="panel-body gallery ">
                <div class="clearfix" style="margin-bottom: 10px;"></div>
                <div class="clear clearfix galeryRowElement">
                    <?php
                    createGallerySection($uploadedVideos, false);
                    ?>
                </div>
            </div>
            <div class="panel-footer channelsFooter clearfix" style="font-size: 0.8em">
                <?php
                if (empty($advancedCustom->doNotDisplayViews)) {
                ?>
                    <div class=" text-muted pull-left">
                        <?php echo number_format_short(VideoStatistic::getChannelsTotalViews($users_id)), " ", __("Views in the last 30 days"); ?>
                    </div>
                <?php
                }
                ?>
                <div class="pull-right">
                    <?php
                    if (class_exists('UserConnections')) {
                        echo UserConnections::getConnectionButtons($users_id);
                    }
                    ?>
                </div>
            </div>
        </div>
<?php
    }

    static function swapUser($users_id)
    {
        if (!Permissions::canAdminUsers()) {
            return false;
        }
        _session_start();
        $_SESSION['swapUser'] = $_SESSION['user'];
        $user = self::getUserFromID($users_id);
        //var_dump($users_id, $user);exit;
        _error_log("swapUser {$users_id}");
        $_SESSION['user'] = $user;
        return $_SESSION['user']['id'];
    }

    static function cancelSwapUser()
    {
        if (!self::isSwapBackActive()) {
            return false;
        }
        _session_start();
        $_SESSION['user'] = $_SESSION['swapUser'];
        unset($_SESSION['swapUser']);
        return $_SESSION['user']['id'];
    }

    static function isSwapBackActive()
    {
        if (empty($_SESSION['swapUser'])) {
            return false;
        }
        return $_SESSION['swapUser']['id'];
    }
}
