<?php
if(!empty($videos_id)){
    $link = Video::getEPGLink($videos_id);
    if (!empty($link)) {
        ?>
        <button type="button" class="btn btn-default no-outline videoTVGuideBtn" onclick="avideoModalIframeFullTransparent('<?php echo $link; ?>');" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("TV Guide"); ?>">
            <i class="fas fa-list-alt"></i> 
            <?php echo __("TV Guide"); ?>
        </button>    
        <?php
    }
}
?>