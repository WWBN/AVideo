<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../videos/configuration.php';

allowOrigin();
ob_end_flush();
$objMM = AVideoPlugin::getObjectData("MobileYPT");

$customizeUser = AVideoPlugin::getDataObject('CustomizeUser');
if(AVideoPlugin::isEnabled('YouPHPFlix2')){
    $firstPage = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIPlugin=YouPHPFlix2&APIName=firstPage";
}else{
    $firstPage = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIPlugin=Gallery&APIName=firstPage";
}
_error_log('getConfiguration line '.__LINE__);
if(User::isLogged()){
    $firstPage = addQueryStringParameter($firstPage, 'user', User::getUserName());
    $firstPage = addQueryStringParameter($firstPage, 'pass', User::getUserPass());
    $firstPage = addQueryStringParameter($firstPage, 'webSiteRootURL', $global['webSiteRootURL']);
}
_error_log('getConfiguration line '.__LINE__);
$objMM->firstPageEndpoint = $firstPage;

$objMM->firstPage = json_decode(url_get_contents_with_cache($objMM->firstPageEndpoint, 300, "", 0, true, true));

_error_log('getConfiguration line '.__LINE__);
$objMM->doNotShowPhoneOnSignup = $customizeUser->doNotShowPhoneOnSignup;

$objMM->doNotShowPhoneOnSignup = $customizeUser->doNotShowPhoneOnSignup;

$chat2 = AVideoPlugin::getDataObjectIfEnabled('Chat2');
if(!empty($chat2)){
    $objMM->chat2ShowOnLive = $chat2->showOnLive;
    $objMM->chat2ShowOnUserVideos = $chat2->showOnUserVideos;
}else{
    $objMM->chat2ShowOnLive = false;
    $objMM->chat2ShowOnUserVideos = false;
}

$notifications = AVideoPlugin::getDataObjectIfEnabled('Notifications');
if(!empty($notifications)){
    $objMM->oneSignalEnabled = !_empty($notifications->oneSignalEnabled);
    $objMM->oneSignalAPPID = $notifications->oneSignalAPPID;
}else{
    $objMM->oneSignalEnabled = false;
    $objMM->oneSignalAPPID = '';
}

_error_log('getConfiguration line '.__LINE__);
$objMM->homePageURL = AVideoPlugin::getMobileHomePageURL();

_error_log('getConfiguration line '.__LINE__);
$objMM->logo = getURL($config->getLogo());
$objMM->favicon = $config->getFavicon(true);
$objMM->title = $config->getWebSiteTitle();
$objMM->version = $config->getVersion();
$objMM->encoder = $config->getEncoderURL(true);
$objMM->EULA_original = $objMM->EULA->value;
$objMM->EULA = nl2br($objMM->EULA->value);
$objMM->YPTSocket = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
$objMM->language = $config->getLanguage();
@include_once "{$global['systemRootPath']}locale/{$objMM->language}.php";
$objMM->translations = $t;
if (!empty($objMM->YPTSocket)) {
    $refl = new ReflectionClass('SocketMessageType');
    $objMM->webSocketTypes = json_encode($refl->getConstants());
    $objMM->webSocketURL = addQueryStringParameter(YPTSocket::getWebSocketURL(true), 'page_title', 'Mobile APP');
}
_error_log('getConfiguration line '.__LINE__);
$objMM->tabMenuItems = [];
$objMM->leftMenuItems = [];
$objMM->tabMenuItemsInABrowser = [];
$objMM->leftMenuItemsInABrowser = [];
if (AVideoPlugin::isEnabledByName("TopMenu")) {
    if (empty($_POST['sort'])) {
        $_POST['sort'] = ['item_order'=>"ASC"];
    }
    _error_log('getConfiguration line '.__LINE__);
    $tabMenu = Menu::getAllActive(Menu::$typeMobileTabMenu);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $objMM->tabMenuItems[] = $value2;
        }
    }
    _error_log('getConfiguration line '.__LINE__);
    $tabMenu = Menu::getAllActive(Menu::$typeMobileLeftMenu);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $objMM->leftMenuItems[] = $value2;
        }
    }
    _error_log('getConfiguration line '.__LINE__);
    $tabMenu = Menu::getAllActive(Menu::$typeMobileTabMenuInABrowser);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $value2['target'] = '_blank';
            $objMM->tabMenuItems[] = $value2;
        }
    }
    _error_log('getConfiguration line '.__LINE__);
    $tabMenu = Menu::getAllActive(Menu::$typeMobileLeftMenuInABrowser);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $value2['target'] = '_blank';
            $objMM->leftMenuItems[] = $value2;
        }
    }
    _error_log('getConfiguration line '.__LINE__);
}
_error_log('getConfiguration line '.__LINE__);
$str = _json_encode($objMM);
_error_log('getConfiguration line strlen='.strlen($str));
echo $str;
exit;