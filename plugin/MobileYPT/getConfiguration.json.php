<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../videos/configuration.php';
/*
AVideoPlugin::getObjectData("Cache");
ObjectYPT::deleteALLCache();
ObjectYPT::deleteAllSessionCache();
Cache::deleteAllCache();
*/
allowOrigin();
$objMM = AVideoPlugin::getObjectData("MobileYPT");

$customizeUser = AVideoPlugin::getDataObject('CustomizeUser');
if(AVideoPlugin::isEnabledByName('YouPHPFlix2')){
    $firstPage = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIPlugin=YouPHPFlix2&APIName=firstPage";
}else{
    $firstPage = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIPlugin=Gallery&APIName=firstPage";
}
if(User::isLogged()){
    $firstPage = addQueryStringParameter($firstPage, 'rowCount', 50);
    $firstPage = addQueryStringParameter($firstPage, 'user', User::getUserName());
    $firstPage = addQueryStringParameter($firstPage, 'pass', User::getUserPass());
    $firstPage = addQueryStringParameter($firstPage, 'webSiteRootURL', $global['webSiteRootURL']);
}
$objMM->firstPageEndpoint = $firstPage;

//$content = url_get_contents($objMM->firstPageEndpoint, "", 0, true, true);
$content = url_get_contents_with_cache($objMM->firstPageEndpoint, 600, "", 0, false, true);

$objMM->firstPage = _json_decode($content);

$objMM->disableNativeSignUp = $customizeUser->disableNativeSignUp;
$objMM->doNotShowPhoneOnSignup = $customizeUser->doNotShowPhoneOnSignup;

$objMM->userMustBeLoggedIn = $customizeUser->userMustBeLoggedIn || $objMM->doNotAllowAnonimusAccess;
$objMM->forceLoginToBeTheEmail = $customizeUser->forceLoginToBeTheEmail;

$chat2 = AVideoPlugin::getDataObjectIfEnabled('Chat2');
if(!empty($chat2)){
    $objMM->chat2IsEnabled = true;
    $objMM->chat2ShowOnLive = $chat2->showOnLive;
    $objMM->chat2ShowOnUserVideos = $chat2->showOnUserVideos;
}else{
    $objMM->chat2IsEnabled = false;
    $objMM->chat2ShowOnLive = false;
    $objMM->chat2ShowOnUserVideos = false;
}

$objMM->stats = getStatsNotifications();

$notifications = AVideoPlugin::getDataObjectIfEnabled('Notifications');
if(!empty($notifications)){
    $objMM->oneSignalEnabled = !_empty($notifications->oneSignalEnabled);
    $objMM->oneSignalAPPID = $notifications->oneSignalAPPID;
    $objMM->oneSignalFIREBASE_SENDER_ID = $notifications->oneSignalFIREBASE_SENDER_ID;
}else{
    $objMM->oneSignalEnabled = false;
    $objMM->oneSignalAPPID = '';
    $objMM->oneSignalFIREBASE_SENDER_ID = '';
}

$objMM->homePageURL = AVideoPlugin::getMobileHomePageURL();

$objMM->logo = getURL($config->getLogo());
$objMM->favicon = $config->getFavicon(true);
$objMM->title = $config->getWebSiteTitle();
$objMM->version = $config->getVersion();
$objMM->encoder = $config->getEncoderURL(true);
$objMM->EULA_original = $objMM->EULA->value;
$objMM->EULA = nl2br($objMM->EULA->value);
$objMM->YPTSocket = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
$unset = array('debugSocket', 'debugAllUsersSocket',
'server_crt_file', 'server_key_file', 'allow_self_signed', 'showTotalOnlineUsersPerVideo',
'showTotalOnlineUsersPerLive', 'showTotalOnlineUsersPerLiveLink', 'enableCalls');

foreach ($unset as $value) {
    unset($objMM->YPTSocket->$value);
}

$objMM->enabledLangs = getEnabledLangs();
$objMM->flags = Layout::getAvailableFlags();
$objMM->defaultLang = getLanguage();

@include_once "{$global['systemRootPath']}locale/{$objMM->defaultLang}.php";
$objMM->translations = $t;


if (!empty($objMM->YPTSocket)) {
    $refl = new ReflectionClass('SocketMessageType');
    $objMM->webSocketTypes = json_encode($refl->getConstants());
    $objMM->webSocketURL = addQueryStringParameter(YPTSocket::getWebSocketURL(false), 'page_title', 'Mobile APP');
}
$objMM->tabMenuItems = [];
$objMM->leftMenuItems = [];
$objMM->tabMenuItemsInABrowser = [];
$objMM->leftMenuItemsInABrowser = [];
if (AVideoPlugin::isEnabledByName("TopMenu")) {
    if (empty($_POST['sort'])) {
        $_POST['sort'] = ['item_order'=>"ASC"];
    }
    $tabMenu = Menu::getAllActive(Menu::$typeMobileTabMenu);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $objMM->tabMenuItems[] = $value2;
        }
    }
    $tabMenu = Menu::getAllActive(Menu::$typeMobileLeftMenu);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $objMM->leftMenuItems[] = $value2;
        }
    }
    $tabMenu = Menu::getAllActive(Menu::$typeMobileTabMenuInABrowser);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $value2['target'] = '_blank';
            $objMM->tabMenuItems[] = $value2;
        }
    }
    $tabMenu = Menu::getAllActive(Menu::$typeMobileLeftMenuInABrowser);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $value2['target'] = '_blank';
            $objMM->leftMenuItems[] = $value2;
        }
    }
}

$objMM->defaultIsPortrait = defaultIsPortrait();

$str = _json_encode($objMM);
_error_log('getConfiguration strlen='.strlen($str));
echo $str;
exit;
