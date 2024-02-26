<?php
if (empty($currentCat) || empty($currentCat['clean_name'])) {
    echo '<!-- empty category on category Live -->';
    return false;
}

$galleryObj = AVideoPlugin::getObjectData("Gallery");
if (empty($galleryObj->showCategoryLiveRow)) {
    echo '<!-- empty showCategoryLiveRow on category Live -->';
    return false;
}
global $categoryLiveVideos;
$categoryLiveVideos = getLiveVideosFromCategory($currentCat['id']);

if (!empty($categoryLiveVideos)) {
    global $contentSearchFound;
    $contentSearchFound = true;
    ?>
    <div class="clear clearfix">
        <?php
        if (canPrintCategoryTitle($currentCat['name'])) {
            ?>
            <h3 class="galleryTitle">
                <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $currentCat['clean_name']; ?>">
                    <i class="<?php echo $currentCat['iconClass']; ?>"></i> <?php echo $currentCat['name']; ?>
                </a>
            </h3>
            <?php
        }
        createGalleryLiveSection($categoryLiveVideos);
        ?>
    </div>
    <?php
}else{
    echo '<!-- Live videos list is empty -->';
}