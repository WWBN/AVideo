<?php
global $global;

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = dirname(__FILE__) . '/../../../';
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';

if (empty($obj)) {
    $obj = AVideoPlugin::getDataObject('Gallery');
}

$itemsPerPage = 4;
$total = Subscribe::getTotalSubscribedChannels(User::getId());
$page = getCurrentPage();
$channels = Subscribe::getSubscribedChannels(User::getId(), $itemsPerPage, $page);
if (empty($channels)) {
    return '';
}
$totalPages = ceil($total / $itemsPerPage);
if ($totalPages < $page) {
    $page = $totalPages;
}
?>
<!-- mainAreaChannel start -->
<div class="row">
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
</div>
<!-- mainAreaChannel end -->