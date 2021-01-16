<?php
if(empty($currentCat)){
    return false;
}

$galleryObj = AVideoPlugin::getObjectData("Gallery");
if(empty($galleryObj->showCategoryLiveRow)){
    return false;
}

$videosL = $videosLL = $videos = array();
if (AVideoPlugin::isEnabledByName("Live")) {
    $videosL = Live::getAllVideos();
}
if (AVideoPlugin::isEnabledByName("LiveLinks")) {
    $videosLL = LiveLinks::getAllVideos();
}
$videos = array_merge($videosL, $videosLL);
if (!empty($videos)) {
    $contentSearchFound = true;
    ?>
    <div class="row clear clearfix">
        <?php 
        if(canPrintCategoryTitle($currentCat['name'])){
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
}