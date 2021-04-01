<div class="col-md-12">
    <a class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>" 
       href="<?php echo YouPHPFlix2::getLinkToVideo($video['id'], true); ?>"
       embed="<?php echo Video::getLinkToVideo ($video['id'], $video['clean_title'], true); ?>">
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