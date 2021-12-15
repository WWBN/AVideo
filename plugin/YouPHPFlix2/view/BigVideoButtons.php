<div class="col-md-12">
    <?php
    $obj2 = AVideoPlugin::getObjectData("YouPHPFlix2");
    $canWatchPlayButton = "";
    $get = $_GET;
    $rowLink = YouPHPFlix2::getLinkToVideo($video['id'], true);
    if (User::canWatchVideoWithAds($video['id'])) {
        $canWatchPlayButton = "canWatchPlayButton";
    } else if ($obj2->hidePlayButtonIfCannotWatch) {
        $canWatchPlayButton = "hidden";
        $url = "{$global['webSiteRootURL']}user";
        $url = addQueryStringParameter($url, 'redirectUri', $rowLink);
        if (!User::isLogged()) {
            ?>
            <a class="btn btn-default" 
               href="<?php echo $url; ?>">
                <i class="fas fa-sign-in-alt"></i>
                <span class="hidden-xs"><?php echo __("Login"); ?></span>
            </a>
            <?php
        }
    }
    $_GET = $get;
    ?>
    <a class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>" 
       href="<?php echo $rowLink; ?>"
       embed="<?php echo Video::getLinkToVideo($video['id'], $video['clean_title'], true); ?>">
        <i class="fa fa-play"></i>
        <span class=""><?php echo __("Play"); ?></span>
    </a>
    <?php
    if (!empty($video['trailer1'])) {
        ?>
        <a href="#" class="btn btn-warning" onclick="flixFullScreen('<?php echo parseVideos($video['trailer1'], 1, 0, 0, 0, 1); ?>', '');return false;">
            <span class="fa fa-film"></span>
            <span class=""><?php echo __("Trailer"); ?></span>
        </a>
        <?php
    }
    ?>
    <?php
    echo AVideoPlugin::getNetflixActionButton($video['id']);
    getSharePopupButton($video['id']);
    ?>
</div>