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
_session_write_close();
$user_id = isChannel();
$user = new User($user_id);
if ($user->getStatus() === 'i') {
    forbiddenPage(__("This user is inactive"));
}
$isMyChannel = $user_id == User::getId();

$channelPassword = User::getProfilePassword($user_id);
if (!empty($channelPassword)) {
    forbiddenPage('This channel is password protected', false, $channelPassword);
}
AVideoPlugin::getChannel($user_id, $user);
$channelFluidLayout = true;
// verify the width to match with the old profile bg image
$bgImagePath = $global['systemRootPath'] . $user->getBackgroundURL();
$bgSize = getimagesize($bgImagePath);
if ($bgSize[0] < 2048) {
    $channelFluidLayout = false;
}
$metaDescription = " Channel - {$_GET['channelName']}";

$page = new Page(array($_GET['channelName'],"Channel"), 'userChannel');
$page->setIncludeInHead(array('view/channelHead.php'));
$page->setIncludeInFooter(array('plugin/YouPHPFlix2/view/modeFlixFooter.php'));
$page->setIncludeFooter(false);
?>
<!-- <?php echo json_encode($bgSize); ?> [<?php echo $bgImagePath; ?>] -->
<div class="container<?php echo!empty($channelFluidLayout) ? "-fluid" : ""; ?>">
    <?php
    include $global['systemRootPath'] . 'view/channelBody.php';
    ?>
</div>
<?php
$page->print(false);
?>
