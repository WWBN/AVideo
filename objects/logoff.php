<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

if (!empty($advancedCustomUser->afterLogoffGoToMyChannel)) {
    $redirectUri = User::getChannelLink();
}else{
    $redirectUri = $global['webSiteRootURL'];
}

User::logoff();
Category::clearCacheCount();
header("location: {$redirectUri}");
