<?php
$video = new Video('', '', $videos_id);
if(!isValidURL($video->getTrailer1())){
    echo '<!-- invalid trailer URL -->';
    return false;
}
?>

<button type="button" class="btn btn-link btn-xs videoTrailerBtnLabel" onclick="avideoModalIframe('<?php echo htmlspecialchars(parseVideos($video->getTrailer1(), 1), ENT_QUOTES, 'UTF-8'); ?>');" data-toggle="tooltip" title="<?php echo __("Trailer"); ?>">
    <i class="fa fa-video"></i> <?php echo __("Trailer"); ?>
</button>
