<?php
$_REQUEST['catName'] = $_cat['clean_name'];
if (!empty($liveobj) && empty($liveobj->doNotShowLiveOnCategoryList)) {
    $currentCat = $_cat;
    echo '<!-- ' . basename(__FILE__) . ' -->';
    include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategoryLive.php';
}
unset($_POST['sort']);
$_POST['sort']['v.created'] = "DESC";
$_POST['sort']['likes'] = "DESC";
$videos = Video::getAllVideos("viewableNotUnlisted", false, !$obj->hidePrivateVideos);
if (empty($videos)) {
    return;
}
if (empty($_cat['clean_name'])) {
    return;
}
global $contentSearchFound;
if (empty($contentSearchFound)) {
    $contentSearchFound = !empty($videos);
}
?>
<div class="clear clearfix">
    <?php
    if (canPrintCategoryTitle($_cat['name'])) {
    ?>
        <h3 class="galleryTitle">
            <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $_cat['clean_name']; ?>">
                <i class="<?php echo $_cat['iconClass']; ?>"></i> <?php echo $_cat['name']; ?>
            </a>
        </h3>
    <?php
    }
    createGallerySection($videos, true, true);
    ?>
</div>