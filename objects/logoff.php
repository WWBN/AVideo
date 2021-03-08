<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

if (!empty($advancedCustomUser->afterLogoffGoToMyChannel)) {
    $redirectUri = User::getChannelLink();
}else if (!empty($advancedCustomUser->afterLogoffGoToURL)) {
    $redirectUri = $advancedCustomUser->afterLogoffGoToURL;
}else{
    $redirectUri = $global['webSiteRootURL'];
}

User::logoff();
Category::clearCacheCount();
header("location: {$redirectUri}");
exit;
