<?php
if(isMobile()){
    return false;
}
?>

<button type="button" class="btn btn-default no-outline show-when-is-expanded hidden-xs" onclick="compress();" data-toggle="tooltip" data-placement="bottom"  title="<?php echo __("Compress"); ?>">
    <i class="fas fa-compress-arrows-alt"></i> <small class="hidden-sm hidden-xs"><?php echo __("Compress"); ?></small>
</button>

<button type="button" class="btn btn-default no-outline show-when-is-compressed hidden-xs" onclick="expand();" data-toggle="tooltip" data-placement="bottom"  title="<?php echo __("Expand"); ?>">
    <i class="fas fa-expand-arrows-alt"></i> <small class="hidden-sm hidden-xs"><?php echo __("Expand"); ?></small>
</button>