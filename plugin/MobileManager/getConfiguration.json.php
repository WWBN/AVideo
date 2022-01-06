<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../videos/configuration.php';

allowOrigin();
$obj = AVideoPlugin::getObjectData("MobileManager");
$obj->EULA = nl2br($obj->EULA->value);
$obj->YPTSocket = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
$obj->language = $config->getLanguage();
@include_once "{$global['systemRootPath']}locale/{$obj->language}.php";
$obj->translations = $t;
if (!empty($obj->YPTSocket)) {
    $refl = new ReflectionClass('SocketMessageType');
    $obj->webSocketTypes = json_encode($refl->getConstants());
    $obj->webSocketURL = addQueryStringParameter(YPTSocket::getWebSocketURL(true), 'page_title', 'Mobile APP');
}
$obj->tabMenuItems = [];
$obj->leftMenuItems = [];
$obj->tabMenuItemsInABrowser = [];
$obj->leftMenuItemsInABrowser = [];
if (AVideoPlugin::isEnabledByName("TopMenu")) {
    if (empty($_POST['sort'])) {
        $_POST['sort'] = ['item_order'=>"ASC"];
    }
    $tabMenu = Menu::getAllActive(Menu::$typeMobileTabMenu);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $obj->tabMenuItems[] = $value2;
        }
    }
    $tabMenu = Menu::getAllActive(Menu::$typeMobileLeftMenu);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $obj->leftMenuItems[] = $value2;
        }
    }
    $tabMenu = Menu::getAllActive(Menu::$typeMobileTabMenuInABrowser);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $value2['target'] = '_blank';
            $obj->tabMenuItems[] = $value2;
        }
    }
    $tabMenu = Menu::getAllActive(Menu::$typeMobileLeftMenuInABrowser);
    foreach ($tabMenu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        foreach ($menuItems as $value2) {
            $value2['target'] = '_blank';
            $obj->leftMenuItems[] = $value2;
        }
    }
}
echo json_encode($obj);
