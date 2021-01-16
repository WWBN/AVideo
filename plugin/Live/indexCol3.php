<?php
if (!empty($obj->experimentalWebcam)) {
    include $global['systemRootPath'] . 'plugin/Live/tabs/experimentalWebCam.php';
}
?>
<div class="clear clearfix"></div>

<div class="tabbable-line"  id="indexTabs">
    <ul class="nav nav-tabs">
        <li class="active" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use streaming software or hardware"); ?>"><a data-toggle="tab" href="#tabStreamKey"><i class="fas fa-key"></i> <?php echo __("Stream Key"); ?></a></li>
        <li class="" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Share information about your live"); ?>"><a data-toggle="tab" href="#tabShare"><i class="fa fa-share"></i> <?php echo __("Share"); ?></a></li>
        <?php
        if (empty($obj->disableRestream)) {
            ?>
            <li class="" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Live stream to other platforms simultaneously"); ?>"><a data-toggle="tab" href="#tabRestream"><i class="fas fa-sync"></i> <?php echo __("Restream"); ?></a> </li>
            <?php
        }
        ?>
    </ul>
    <div class="tab-content">
        <div id="tabStreamKey" class="tab-pane fade in active">
            <?php
            include $global['systemRootPath'] . 'plugin/Live/tabs/tabStreamKey.php';
            ?>
        </div>
        <div id="tabShare" class="tab-pane fade"> 
            <?php
            include $global['systemRootPath'] . 'plugin/Live/tabs/tabShare.php';
            ?>
        </div>
        <?php
        if (empty($obj->disableRestream)) {
            ?>
            <div id="tabRestream" class="tab-pane fade"> 
                <?php
                include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/livePanel.php';
                ?>
            </div>
            <?php
        }
        ?>

    </div> 
</div>  
<?php
AVideoPlugin::getLivePanel();
?>