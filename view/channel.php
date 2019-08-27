<?php
global $global, $config, $isChannel;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
session_write_close();
if (empty($_GET['channelName'])) {
    if (User::isLogged()) {
        $_GET['user_id'] = User::getId();
    } else {
        return false;
    }
} else {
    $_GET['channelName'] = xss_esc($_GET['channelName']);
    $user = User::getChannelOwner($_GET['channelName']);
    if (!empty($user)) {
        $_GET['user_id'] = $user['id'];
    } else {
        $_GET['user_id'] = $_GET['channelName'];
    }
}
$user_id = $_GET['user_id'];
$isMyChannel = $user_id == User::getId();
YouPHPTubePlugin::getChannel($user_id, $user);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Channel"); ?> :: <?php echo @$_GET['channelName']; ?> </title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        include $global['systemRootPath'] . 'view/channelHead.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <?php
            include $global['systemRootPath'] . 'view/channelBody.php';
            ?>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
