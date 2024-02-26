<?php
$output = _ob_get_clean();
_ob_start();
echo AVideoPlugin::getUploadMenuButton();
$getUploadMenuButton = _ob_get_clean();
_ob_start();
if (!empty($getUploadMenuButton)) {
    ?>
    <li>
        <div data-toggle="tooltip" title="<?php echo __("Submit your videos"); ?>" data-placement="left"  class="btn-group">
            <button type="button" class="btn btn-default  dropdown-toggle navbar-btn pull-left faa-parent animated-hover"  data-toggle="dropdown">
                <i class="<?php echo $advancedCustom->uploadButtonDropdownIcon ?? "fas fa-video"; ?>"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? __($advancedCustom->uploadButtonDropdownText) : ""; ?> <span class="caret"></span>
            </button>
            <?php echo '<!-- navbar line ' . __LINE__ . '-->'; ?>
            <ul class="dropdown-menu dropdown-menu-right dropdown-menu-arrow " role="menu" id="uploadMenu">
                <?php echo $getUploadMenuButton; ?>
            </ul>
        </div>
    </li>
    <?php
    $getUploadMenuButton = _ob_get_clean();
    _ob_start();
}
echo $output . $getUploadMenuButton;
?>