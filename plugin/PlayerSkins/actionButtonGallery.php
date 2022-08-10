<?php
$link = Video::getEPGLink($videos_id);
if (!empty($link)) {
    ?>
    <button type="button" class="btn label label-default" onclick="avideoModalIframeFullTransparent('<?php echo $link; ?>');" data-toggle="tooltip" title="<?php echo __("TV Guide"); ?>">
        <i class="fas fa-list-alt"></i> 
        <?php echo __("TV Guide"); ?>
    </button>    
    <?php
}
?>