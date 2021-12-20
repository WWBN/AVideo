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
if(!empty($obj->YPTSocket)){
    $refl = new ReflectionClass('SocketMessageType');
    $obj->webSocketTypes = json_encode($refl->getConstants());
    $obj->webSocketURL = addQueryStringParameter(YPTSocket::getWebSocketURL(true), 'page_title', 'Mobile APP');
}
if(AVideoPlugin::isEnabledByName("TopMenu")){
    $obj->tabMenuItems = array(); 
    $obj->leftMenuItems = array(); 
    if(empty($_POST['sort'])){
        $_POST['sort'] = array('item_order'=>"ASC");
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
}
echo json_encode($obj);
