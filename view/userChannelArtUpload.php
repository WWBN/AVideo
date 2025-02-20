<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!User::isLogged()) {
    gotToLoginAndComeBackHere('');
}

$channelArtRelativePath = User::getBackgroundURLFromUserID(User::getId());

$finalWidth = 2560;
$finalHeight = 1440;

$screenWidth = 1024;
$screenHeight = 576;

$factorW = $screenWidth / $finalWidth;
$_page = new Page(array('Channel Art'));
$_page->setIncludeInBody('view/userChannelArtUploadInclude.php');
$_page->print();
?>
