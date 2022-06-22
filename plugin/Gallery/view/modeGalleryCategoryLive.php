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

$videos = getLiveVideosFromCategory($currentCat['id']);

/*
  $videosL = $videosLL = $videos = array();
  if (AVideoPlugin::isEnabledByName("Live")) {
  $videosL = Live::getAllVideos();
  }
  if (AVideoPlugin::isEnabledByName("LiveLinks")) {
  $videosLL = LiveLinks::getAllVideos('a');
  }
  $videos = array_merge($videosL, $videosLL);
 * 
 */
if (!empty($videos)) {
    $contentSearchFound = true;
    ?>
    <div class="clear clearfix">
        <?php
        if (canPrintCategoryTitle($currentCat['name'])) {
            ?>
            <h3 class="galleryTitle">
                <a class="btn-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $currentCat['clean_name']; ?>">
                    <i class="<?php echo $currentCat['iconClass']; ?>"></i> <?php echo $currentCat['name']; ?>
                </a>
            </h3>
            <?php
        }
        createGalleryLiveSection($videos);
        ?>
    </div>
    <?php
}else{
    echo '<!-- Live videos list is empty -->';
}