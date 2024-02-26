<button type="button" 
        class="btn btn-default btn-light btn-sm btn-xs btn-block " 
        onclick="avideoModalIframe(webSiteRootURL +\'plugin/CustomizeUser/getSource.php?videos_id='+ row.id+'\');" 
        data-row-id="right"  data-toggle="tooltip" data-placement="left" 
        title=<?php printJSString("Source files") ?>>
    <i class="fas fa-file"></i> <?php echo __("Source files"); ?>
</button>
