<?php
$zindex = 1000;
$sectionName = "YouTubeVideos";
$objGallery = AVideoPlugin::getObjectData("Gallery");
if (showThis($sectionName)) {
    $object = $this->listVideos();
    if ($object->error && User::isAdmin()) {
        ?>
        <div class="alert alert-danger">
            <h1>YouTubeAPI Error</h1>
            <?php
            if (empty($object->msg->error)) {
                echo $object->msg;
            } else {
                foreach ($object->msg->error as $key => $value) {
                    if (!is_object($value) && !is_array($value)) {
                        echo "<b>{$key}</b>: $value<br>";
                    } else {
                        foreach ($value as $key2 => $value2) {
                            if (!is_object($value2) && !is_array($value2)) {
                                echo "<b>{$key2}</b>: $value2<br>";
                            } else {
                                foreach ($value2 as $key3 => $value3) {
                                    if (!is_object($value3) && !is_array($value3)) {
                                        echo "<b>{$key3}</b>: $value3<br>";
                                    } else {
                                        var_dump($value3);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            ?>
        </div>
        <?php
    }
    ?>
    <div class="clear clearfix">
        <h3 class="galleryTitle">
            <a class="btn-light" href="<?php echo $global['webSiteRootURL']; ?>?showOnly=<?php echo $sectionName; ?>">
                <i class="fab fa-youtube"></i>
                <?php echo $obj->gallerySectionTitle; ?>
            </a>
        </h3>
        <?php
        foreach ($object->videos as $video) {
            $youtubeEmbedLink = "{$global['webSiteRootURL']}evideo/".  encryptString(json_encode($video));
            $youtubeTitle = $video->title;
            $youtubeThumbs = $video->thumbnails;
            ?>
            <div class="col-lg-<?php echo 12 / $objGallery->screenColsLarge; ?> col-md-<?php echo 12 / $objGallery->screenColsMedium; ?> col-sm-<?php echo 12 / $objGallery->screenColsSmall; ?> col-xs-<?php echo 12 / $objGallery->screenColsXSmall; ?> galleryVideo thumbsImage fixPadding" style="z-index: <?php echo $zindex--; ?>; min-height: 175px;" itemscope itemtype="http://schema.org/VideoObject">
                <a class="evideo" href="<?php echo $youtubeEmbedLink; ?>" title="<?php echo $youtubeTitle; ?>">
                    <div class="aspectRatio16_9">
                        <img src="<?php echo $youtubeThumbs; ?>" alt="<?php echo $youtubeTitle; ?>" class="thumbsJPG img img-responsive" />
                    </div>
                </a>
                <a class="h6 evideo" href="<?php echo $youtubeEmbedLink; ?>" title="<?php echo $youtubeTitle; ?>">
                    <h2><?php echo $youtubeTitle; ?></h2>
                </a>
            </div>
            <?php
        }
        if($_GET['page'] > 1 && !empty($object->prevPageToken)){
        ?>
        <a href="<?php echo "{$global['webSiteRootURL']}page/".($_GET['page']-1)."?pageToken={$object->prevPageToken}&search=".(@$_GET['search']); ?>" class="btn btn-primary btn-sm pull-left">
            <i class="fas fa-angle-double-left"></i> <?php echo __("Previous"); ?>
        </a>
        <?php
        }
        if(!empty($object->nextPageToken)){
        ?>
        <a href="<?php echo "{$global['webSiteRootURL']}page/".($_GET['page']+1)."?pageToken={$object->nextPageToken}&search=".(@$_GET['search']); ?>" class="btn btn-primary btn-sm pull-right">
            <?php echo __("Next"); ?> <i class="fas fa-angle-double-right"></i>
        </a>
        <?php
        }
        ?>
    </div>
    <?php
}
?>