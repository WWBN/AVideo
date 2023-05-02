<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../videos/configuration.php';

allowOrigin();
$objMM = AVideoPlugin::getObjectData("MobileManager");

$customizeUser = AVideoPlugin::getDataObject('CustomizeUser');
$objMM->doNotShowPhoneOnSignup = $customizeUser->doNotShowPhoneOnSignup;

$chat2 = AVideoPlugin::getDataObjectIfEnabled('Chat2');
if(!empty($chat2)){
    $objMM->chat2ShowOnLive = $chat2->showOnLive;
    $objMM->chat2ShowOnUserVideos = $chat2->showOnUserVideos;
}else{
    $objMM->chat2ShowOnLive = false;
    $objMM->chat2ShowOnUserVideos = false;
}

$objMM->homePageURL = AVideoPlugin::getMobileHomePageURL();

$objMM->logo = getURL($config->getLogo());
$objMM->favicon = $config->getFavicon(true);
$objMM->title = $config->getWebSiteTitle();
$objMM->version = $config->getVersion();
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
echo json_encode($objMM);
