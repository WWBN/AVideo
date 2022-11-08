<?php
$videoAB = new Video('', '', $videos_id);
if(!isValidURL($videoAB->getTrailer1())){
    echo '<!-- invalid trailer URL -->';
    return false;
}
?>
<button type="button" class="btn btn-default no-outline" onclick="avideoModalIframe('<?php echo parseVideos($videoAB->getTrailer1(), 1); ?>');" data-toggle="tooltip" title="<?php echo __("Trailer"); ?>">
    <i class="fa fa-video"></i> <?php echo __("Trailer"); ?>
</button>
