<?php
global $global, $config, $isChannel;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$isChannel = 1;
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
session_write_close();
$user_id = isChannel();
$user = new User($user_id);
if($user->getStatus()==='i'){
    forbiddenPage(__("This user is inactive"));
}
$isMyChannel = $user_id == User::getId();
AVideoPlugin::getChannel($user_id, $user);
$channelFluidLayout = true;
// verify the width to match with the old profile bg image
$bgImagePath = $global['systemRootPath'] . $user->getBackgroundURL();
$bgSize = getimagesize($bgImagePath);
if($bgSize[0]<2048){
    $channelFluidLayout = false;
}
$metaDescription = " Channel - {$_GET['channelName']}";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo @$_GET['channelName'].getSEOComplement(); ?> :: <?php echo __("Channel"); ?> :: <?php echo $config->getWebSiteTitle(); ?> </title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        include $global['systemRootPath'] . 'view/channelHead.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?> userChannel">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container<?php echo !empty($channelFluidLayout)?"-fluid":""; ?>">
            <?php
                include $global['systemRootPath'] . 'view/channelBody.php';
            ?>
        </div>
        <?php
        //include $global['systemRootPath'] . 'view/include/footer.php';
        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixFooter.php';
        ?>
    </body>
</html>
