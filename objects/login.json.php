<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/functions.php';

$timeLog = __FILE__ . "::Login ";
TimeLogStart($timeLog);

// gettig the mobile submited value
$inputJSON = url_get_contents('php://input');
$input = _json_decode($inputJSON, true); //convert JSON into array
if (!empty($input)) {
    foreach ($input as $key => $value) {
        $_POST[$key] = $value;
    }
}

TimeLogEnd($timeLog, __LINE__);

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';

Category::clearCacheCount();
TimeLogEnd($timeLog, __LINE__);

if (!preg_match("|^" . $global['webSiteRootURL'] . "|", $_POST['redirectUri'])) {
    $_POST['redirectUri'] = $global['webSiteRootURL'];
}
_error_log("Start Login Request redirectUri=" . $_POST['redirectUri']);

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

TimeLogEnd($timeLog, __LINE__);
if (!empty($_GET['type'])) {
    if (!empty($_GET['redirectUri'])) {
        _session_start();
        $_SESSION['redirectUri'] = getRedirectUri();
    }
    if ($_GET['type'] === "Apple") {
        $obj = AVideoPlugin::getDataObjectIfEnabled('LoginApple');
        if (empty($obj)) {
            die('Apple Login is disabled');
        }
        $config = [
            'callback' => HttpClient\Util::getCurrentUrl() . "?type={$_GET['type']}",
            'providers' => [
                $_GET['type'] => [
                    "enabled" => true,
                    "keys" => [
                        "id" => trim($obj->id),
                        "team_id" => trim($obj->team_id),
                        "key_id" => trim($obj->key_id),
                        "key_content" => trim($obj->key_content->value),
                    ],
                    "scope" => "name email",
                    "verifyTokenSignature" => true,
                ],
            ],
                /* optional : set debug mode
                  'debug_mode' => true,
                  // Path to file writable by the web server. Required if 'debug_mode' is not false
                  'debug_file' => __FILE__ . '.log', */
        ];
    } else {
        $login = AVideoPlugin::getLogin();
        foreach ($login as $value) {
            if(!is_object($value['loginObject'])){
                _error_log('Error on getLogin: '. json_encode($value), AVideoLog::$ERROR);
                continue;
            }
            $obj = $value['loginObject']->getDataObject();
            if ($value['parameters']->type === $_GET['type']) {
                $id = $obj->id;
                $key = $obj->key;
                break;
            }
        }
        if (empty($id)) {
            die(sprintf(__("%s ERROR: You must set an ID on config"), $_GET['type']));
        }

        if (empty($key)) {
            die(sprintf(__("%s ERROR: You must set a KEY on config"), $_GET['type']));
        }
        $scope = 'email';
        if ($_GET['type'] === "Yahoo") {
            $scope = 'sdpp-w';
        }
        if ($_GET['type'] === 'LinkedIn') {
            $scope = ("r_liteprofile r_emailaddress w_member_social");
        }

        $config = [
            'callback' => HttpClient\Util::getCurrentUrl() . "?type={$_GET['type']}",
            'providers' => [
                $_GET['type'] => [
                    'enabled' => true,
                    'keys' => ['id' => $id, 'secret' => $key, 'key' => $id],
                    "includeEmail" => true,
                    'scope' => $scope,
                    'trustForwarded' => false,
                ],
            ],
                /* optional : set debug mode
                  'debug_mode' => true,
                  // Path to file writeable by the web server. Required if 'debug_mode' is not false
                  'debug_file' => __FILE__ . '.log', */
        ];
    }
    try {
        $hybridauth = new Hybridauth($config);

        $adapter = $hybridauth->authenticate($_GET['type']);

        $tokens = $adapter->getAccessToken();
        $userProfile = $adapter->getUserProfile();

        //print_r($tokens);
        //print_r($userProfile);
        if (!empty($userProfile->email)) {
            $user = $userProfile->email;
        } else {
            $user = $userProfile->displayName;
        }
        $name = $userProfile->displayName;
        $photoURL = $userProfile->photoURL;
        $email = $userProfile->email;
        $pass = rand();
        //createUserIfNotExists($user, $pass, $name, $email, $photoURL, $isAdmin = false, $emailVerified = false);
        User::createUserIfNotExists($user, $pass, $name, $email, $photoURL, false, true);
        $userObject = new User(0, $user, $pass);
        $userObject->login(true);
        $adapter->disconnect();

        if (!empty($_SESSION['redirectUri'])) {
            _session_start();
            $location = $_SESSION['redirectUri'];
            //header("Location: {$_SESSION['redirectUri']}");
            $_SESSION['redirectUri'] = '';
            unset($_SESSION['redirectUri']);
        } else {
            $location = $global['webSiteRootURL'];
            //header("Location: {$global['webSiteRootURL']}");
        }
    } catch (\Exception $e) {
        $location = "{$global['webSiteRootURL']}user?error=" . urlencode($e->getMessage());
        //header("Location: {$global['webSiteRootURL']}user?error=" . urlencode($e->getMessage()));
        //echo $e->getMessage();
    }
    if (!isSameDomainAsMyAVideo($location)) {
        $location = $global['webSiteRootURL'];
    }
    header('Content-Type: text/html'); ?>
    <script>
        window.opener = self;
        if (window.name == 'loginYPT') {
            window.close();
        } else {
            document.location = "<?php echo $location; ?>";
        }
    </script>
    <?php
    return;
}

header('Content-Type: application/json');
TimeLogEnd($timeLog, __LINE__);
$object = new stdClass();
if (!empty($_GET['user'])) {
    $_POST['user'] = $_GET['user'];
}
if (!empty($_GET['pass'])) {
    $_POST['pass'] = $_GET['pass'];
}
if (!empty($_GET['encodedPass'])) {
    $_POST['encodedPass'] = $_GET['encodedPass'];
}
if (empty($_POST['user']) || empty($_POST['pass'])) {
    _error_log("User or pass empty on login POST: " . json_encode($_POST));
    _error_log("User or pass empty on login GET: " . json_encode($_GET));
    _error_log("User or pass empty on login Request: " . json_encode($_REQUEST));
    $inputJSON = file_get_contents('php://input');
    _error_log("User or pass empty on login php://input: " . ($inputJSON));
    $object->error = __("User and Password can not be blank");
    die(json_encode($object));
}
$user = new User(0, $_POST['user'], $_POST['pass']);

_error_log("login.json.php trying to login");
$resp = $user->login(false, @$_POST['encodedPass']);
_error_log("login.json.php login respond something");
TimeLogEnd($timeLog, __LINE__);
$object->isCaptchaNeed = User::isCaptchaNeed();
if ($resp === User::USER_NOT_VERIFIED) {
    _error_log("login.json.php User not verified");
    $object->error = __("Please verify your email address");
    die(json_encode($object));
}

if ($resp === User::CAPTCHA_ERROR) {
    _error_log("login.json.php invalid captcha");
    $object->error = __("Invalid Captcha");
    die(json_encode($object));
}

if ($resp === User::REQUIRE2FA) {
    _error_log("login.json.php 2fa login is required");
    $object->error = __("2FA login is required");
    die(json_encode($object));
}

//_error_log("login.json.php setup object");
$object->siteLogo = $global['webSiteRootURL'] . $config->getLogo();
$object->id = User::getId();
$object->user = User::getUserName();
$object->donationLink = User::donationLink();
$object->name = User::getName();
//_error_log("login.json.php get name identification");
$object->nameIdentification = User::getNameIdentification();
$object->pass = User::getUserPass();
$object->email = User::getMail();
//_error_log("login.json.php get channel name");
$object->channelName = User::_getChannelName($object->id);
$object->photo = User::getPhoto();
$object->backgroundURL = User::getBackground($object->id);
$object->isLogged = User::isLogged();
$object->isAdmin = User::isAdmin();
$object->canUpload = User::canUpload();
$object->canComment = User::canComment();
$object->canMeet = AVideoPlugin::isEnabledByName('Meet');
$object->canCreateCategory = Category::canCreateCategory();
$object->theme = getCurrentTheme();
$object->canStream = User::canStream();
$object->redirectUri = @$_POST['redirectUri'];
$object->embedChatUrl = '';
$object->embedChatUrlMobile = '';
if (AVideoPlugin::isEnabledByName('Chat2') && method_exists('Chat2', 'getChatRoomLink')) {
    $object->embedChatUrl = Chat2::getChatRoomLink(User::getId(), 1, 1, 0, true);
    $object->embedChatUrlMobile = addQueryStringParameter($object->embedChatUrl, 'mobileMode', 1);
    $object->embedChatUrlMobile = addQueryStringParameter($object->embedChatUrlMobile, 'user', $object->user);
    $object->embedChatUrlMobile = addQueryStringParameter($object->embedChatUrlMobile, 'pass', $object->pass);
}
//_error_log("login.json.php setup object done");

if ((empty($object->redirectUri) || $object->redirectUri === $global['webSiteRootURL'])) {
    if (!empty($advancedCustomUser->afterLoginGoToMyChannel)) {
        $object->redirectUri = User::getChannelLink();
    } elseif (!empty($advancedCustomUser->afterLoginGoToURL)) {
        $object->redirectUri = $advancedCustomUser->afterLoginGoToURL;
    }
}

if (empty($advancedCustomUser->userCanNotChangeCategory) || User::isAdmin()) {
    //_error_log("login.json.php get categories");
    $object->categories = Category::getAllCategories(true);
    if (is_array($object->categories)) {
        array_multisort(array_column($object->categories, 'hierarchyAndName'), SORT_ASC, $object->categories);
    }
} else {
    $object->categories = [];
}
//_error_log("login.json.php get user groups");
TimeLogEnd($timeLog, __LINE__);
$object->userGroups = UserGroups::getAllUsersGroups();
TimeLogEnd($timeLog, __LINE__);
$object->streamServerURL = '';
$object->streamKey = '';
if ($object->isLogged) {
    $timeLog2 = __FILE__ . "::Is Logged ";
    TimeLogStart($timeLog2);

    //_error_log("login.json.php get Live");
    $p = AVideoPlugin::loadPluginIfEnabled("Live");
    if (!empty($p)) {
        require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
        $trasnmition = LiveTransmition::createTransmitionIfNeed(User::getId());
        if (!empty($trasnmition)) {
            $object->streamServerURL = $p->getServer() . "?p=" . User::getUserPass();
            $object->streamKey = $trasnmition['key'];
        } else {
            _error_log('login.json.php transmissionKey is empty [' . User::getId() . ']');
        }
    } else {
        _error_log('login.json.php live plugin is disabled');
    }
    TimeLogEnd($timeLog2, __LINE__);
    //_error_log("login.json.php get MobileManager");
    $p = AVideoPlugin::loadPluginIfEnabled("MobileManager");
    if (!empty($p)) {
        $object->streamer = _json_decode(url_get_contents($global['webSiteRootURL'] . "objects/status.json.php"));
        $object->plugin = $p->getDataObject();
        $object->encoder = $config->getEncoderURL();
    }
    TimeLogEnd($timeLog2, __LINE__);
    //_error_log("login.json.php get VideoHLS");
    $p = AVideoPlugin::loadPluginIfEnabled("VideoHLS");
    if (!empty($p)) {
        $object->videoHLS = true;
    }
    TimeLogEnd($timeLog2, __LINE__);
    //_error_log("login.json.php get Subscriptions");
    $p = AVideoPlugin::loadPluginIfEnabled("Subscription");
    if (!empty($p)) {
        $object->Subscription = Subscription::getAllFromUser($object->id);
    }
    TimeLogEnd($timeLog2, __LINE__);
    //_error_log("login.json.php get PayPerView");
    $p = AVideoPlugin::loadPluginIfEnabled("PayPerView");
    if (!empty($p) && class_exists('PayPerView')) {
        $object->PayPerView = PayPerView::getAllPPVFromUser($object->id);
    }
    TimeLogEnd($timeLog2, __LINE__);
} else {
    _error_log('login.json.php is not logged');
}

$object->PHPSESSID = session_id();

TimeLogEnd($timeLog, __LINE__);
//_error_log("login.json.php almost complete");
$json = _json_encode($object);
//_error_log("login.json.php complete");
//header("Content-length: " . strlen($json));
_error_log('login.json.php is done');
echo $json;
exit;