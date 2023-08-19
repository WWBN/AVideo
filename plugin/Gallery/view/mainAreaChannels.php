<?php
global $global;

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = dirname(__FILE__) . '/../../../';
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';

if (empty($obj) || empty($obj->SubscribedChannelsRowCount)) {
    $obj = AVideoPlugin::getDataObject('Gallery');
}

$itemsPerPage = 4;
$total = Subscribe::getTotalSubscribedChannels(User::getId());
$page = getCurrentPage();
$channels = Subscribe::getSubscribedChannels(User::getId(), $itemsPerPage, $page);
if (empty($channels)) {
    ?>
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong><?php echo __("No Channels Followed Yet"); ?></strong>
        <p><?php echo __("You haven't started following any channels yet. Browse around to find channels that interest you and click the 'Follow' button to start getting updates!"); ?></p>
        <hr>
        <button class="btn btn-primary" onclick="avideoModalIframeFull(webSiteRootURL + 'channels');return false;">
            <i class="fas fa-search"></i> <?php echo __("Browse Channels"); ?>
        </button>
    </div>
    <?php
    return '';
}
$totalPages = ceil($total / $itemsPerPage);
if ($totalPages < $page) {
    $page = $totalPages;
}
?>
<!-- mainAreaChannel start -->
<div class="mainAreaChannels">  
    <?php
    foreach ($channels as $value) {
        $_POST['disableAddTo'] = 0;
        createChannelItem($value['users_id'], $value['photoURL'], $value['identification'], $obj->SubscribedChannelsRowCount);
    }
    ?>
</div>
<div class="col-sm-12" style="z-index: 1;">
    <?php
//getPagination($total, $page = 0, $link = "", $maxVisible = 10, $infinityScrollGetFromSelector="", $infinityScrollAppendIntoSelector="")
    echo getPagination($totalPages, $page, "{$global['webSiteRootURL']}plugin/Gallery/view/mainAreaChannels.php", 10, ".mainAreaChannels", ".mainAreaChannels");
    ?>
</div>
<!-- mainAreaChannel end -->