<?php
global $global, $config;

require_once '../../../../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesDB.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesGive.php';

$objGallery = AVideoPlugin::getObjectData("Gallery");

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = new stdClass();

if (empty($_REQUEST['combine_sites_id'])) {
    $obj->msg = "Empty combine_sites_id";
    die(json_encode($obj));
}

$o = new CombineSitesDB($_REQUEST['combine_sites_id']);

if (empty($o->getSite_url())) {
    $obj->msg = "Site not found";
    die(json_encode($obj));
}

if ($o->getStatus() !== 'a') {
    $obj->msg = "Site inactive";
    die(json_encode($obj));
}


$site = $o->getSite_url();

//echo $url;exit;
$obj = CombineSites::getContent($_REQUEST['combine_sites_id']);
//var_dump(is_object($obj), empty($obj->error), $obj);
if (is_object($obj) && empty($obj->error)) {
    ?>
    <div class="clear clearfix">
        <h3 class="galleryTitle">
            <a class="btn-default" href="<?php echo $obj->link; ?>">
                <?php echo $obj->image; ?>
                <?php echo $obj->title; ?>
            </a>   
        </h3>

        <?php
        foreach ($obj->evideos as $video) {
            $youtubeEmbedLink = "{$global['webSiteRootURL']}evideo/" . encryptString(json_encode($video));
            $youtubeTitle = $video->title;
            $youtubeThumbs = $video->thumbnails;
            ?>
            <div class="col-lg-<?php echo 12 / $objGallery->screenColsLarge; ?> col-md-<?php echo 12 / $objGallery->screenColsMedium; ?> col-sm-<?php echo 12 / $objGallery->screenColsSmall; ?> col-xs-<?php echo 12 / $objGallery->screenColsXSmall; ?> galleryVideo thumbsImage fixPadding" style="min-height: 175px;" itemscope itemtype="http://schema.org/VideoObject">
                <a class="evideo" href="<?php echo $youtubeEmbedLink; ?>" title="<?php echo $youtubeTitle; ?>">
                    <div class="aspectRatio16_9">
                        <img src="<?php echo $youtubeThumbs; ?>" alt="<?php echo $youtubeTitle; ?>" class="thumbsJPG img img-responsive" />
                    </div>
                </a>
                <a class="h6 evideo" href="<?php echo $youtubeEmbedLink; ?>" title="<?php echo $youtubeTitle; ?>">
                    <h2><?php echo $youtubeTitle; ?></h2>
                </a>
                <div class="galeryDetails" style="overflow: hidden;">
                    <?php
                    if (empty($advancedCustom->doNotDisplayViews)) {
                        ?>
                        <div>
                            <i class="fa fa-eye"></i>
                            <span itemprop="interactionCount">
                                <?php echo number_format($video->views_count, 0); ?> <?php echo __("Views"); ?>
                            </span>
                        </div>
                    <?php } ?>
                    <div>
                        <i class="far fa-clock"></i>
                        <?php echo humanTiming(strtotime($video->videoCreation)), " ", __('ago'); ?>
                    </div>
                    <?php if (!empty($video->trailer1)) { ?>
                        <div>
                            <span onclick="showTrailer('<?php echo parseVideos($video->trailer1, 1); ?>'); return false;" class="cursorPointer" >
                                <i class="fa fa-video"></i> <?php echo __("Trailer"); ?>
                            </span>
                        </div>
                    <?php }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
} else {
    if (User::isAdmin()) {
        ?>
<div class="alert alert-danger">We could not load the Site <?php echo $site; ?><br><?php var_dump($obj); ?></div>
        <?php
    }
}
