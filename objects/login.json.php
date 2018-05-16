<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'objects/functions.php';
// gettig the mobile submited value
$inputJSON = url_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if(!empty($input) && empty($_POST)){
    foreach ($input as $key => $value) {
        $_POST[$key]=$value;
    }
}


require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/hybridauth/autoload.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

if (!empty($_GET['type'])) {    
    $login = YouPHPTubePlugin::getLogin();
    foreach ($login as $value) {
        $obj = $value['loginObject']->getDataObject();
        if($value['parameters']->type === $_GET['type']){
            $id = $obj->id;
            $key = $obj->key;
            break;
        }
    }
    if(empty($id)){
        die(sprintf(__("%s ERROR: You must set a ID on config"), $_GET['type']));
    }

    if(empty($key)){
        die(sprintf(__("%s ERROR: You must set a KEY on config"), $_GET['type']));
    }
    $config = [
        'callback' => HttpClient\Util::getCurrentUrl()."?type={$_GET['type']}",
        'providers' => [
            $_GET['type'] => [
                'enabled' => true,
                'keys' => ['id' => $id, 'secret' => $key, 'key'=>$id],
                "includeEmail" => true,
            ]
        ],
            /* optional : set debug mode
              'debug_mode' => true,
              // Path to file writeable by the web server. Required if 'debug_mode' is not false
              'debug_file' => __FILE__ . '.log', */
    ];
    try {
        $hybridauth = new Hybridauth($config);

        $adapter = $hybridauth->authenticate($_GET['type']);

        $tokens = $adapter->getAccessToken();
        $userProfile = $adapter->getUserProfile();

        //print_r($tokens);
        //print_r($userProfile);
        if(!empty($userProfile->email)){
            $user = $userProfile->email;
        }else{
            $user = $userProfile->displayName;
        }
        $name = $userProfile->displayName;
        $photoURL = $userProfile->photoURL;
        $email = $userProfile->email;
        $pass = rand();
        User::createUserIfNotExists($user, $pass, $name, $email, $photoURL);
        $userObject = new User(0, $user, $pass);
        $userObject->login(true);
        $adapter->disconnect();
        header("Location: {$global['webSiteRootURL']}");

    } catch (\Exception $e) {
        header("Location: {$global['webSiteRootURL']}user?error=".urlencode($e->getMessage()));
        //echo $e->getMessage();
    }
    return;
}

$object = new stdClass();
if(!empty($_GET['user'])){
    $_POST['user'] = $_GET['user'];
}
if(!empty($_GET['pass'])){
    $_POST['pass'] = $_GET['pass'];
}
if(!empty($_GET['encodedPass'])){
    $_POST['encodedPass'] = $_GET['encodedPass'];
}
if(empty($_POST['user']) || empty($_POST['pass'])){
    $object->error = __("User and Password can not be blank");
    die(json_encode($object));
}
$user = new User(0, $_POST['user'], $_POST['pass']);
$resp = $user->login(false, @$_POST['encodedPass']);

if($resp === User::USER_NOT_VERIFIED){
    $object->error = __("Your user is not verified, we sent you a new e-mail");
    die(json_encode($object));
}
$object->id = User::getId();
$object->user = User::getUserName();
$object->pass = User::getUserPass();
$object->email = User::getMail();
$object->photo = User::getPhoto();
$object->backgroundURL = User::getBackground($object->id);
$object->isLogged = User::isLogged();
$object->isAdmin = User::isAdmin();
$object->canUpload = User::canUpload();
$object->canComment = User::canComment();
$object->categories = Category::getAllCategories();
$object->streamServerURL = "";
$object->streamKey = "";
if($object->isLogged){
    $p = YouPHPTubePlugin::loadPluginIfEnabled("Live");
    if(!empty($p)){
        require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
        $trasnmition = LiveTransmition::createTransmitionIfNeed(User::getId());
        $object->streamServerURL = $p->getServer()."?p=".User::getUserPass();
        $object->streamKey = $trasnmition['key'];
    }
    $p = YouPHPTubePlugin::loadPluginIfEnabled("MobileManager");
    if(!empty($p)){
        $object->streamer = json_decode(url_get_contents($global['webSiteRootURL']."status"));
        $object->plugin = $p->getDataObject();
        $object->encoder = $config->getEncoderURL();
    }
}
echo json_encode($object);
