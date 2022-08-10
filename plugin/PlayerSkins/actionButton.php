<?php
$link = Video::getEPGLink($videos_id);
if (!empty($link)) {
    ?>
    <button type="button" class="btn btn-default no-outline" onclick="avideoModalIframeFullTransparent('<?php echo $link; ?>');" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Electronic Programming Guide"); ?>">
        <i class="fas fa-list-alt"></i> 
        <?php echo __("EPG"); ?>
    </button>    
    <?php
}
?>