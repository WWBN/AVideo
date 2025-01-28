<div class="clear clearfix"></div>

<div class="tabbable-line <?php echo getCSSAnimationClassAndStyle('animate__fadeInRight', 'live'); ?>"  id="indexTabs">
    <ul class="nav nav-tabs">
        <?php
        $active = 'active';
        if (Live::canRestream()) {
            ?>
            <li class="<?php echo $active; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Live stream to other platforms simultaneously"); ?>"><a data-toggle="tab" href="#tabRestream"><i class="fas fa-sync"></i> <?php echo __("Restream"); ?></a> </li>
            <?php
            $active = '';
        }
        if (empty($objLive->hideAdvancedStreamKeys)) {
            ?>
            <li class="<?php echo $active; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use streaming software or hardware"); ?>"><a data-toggle="tab" href="#tabStreamKey"><i class="fas fa-key"></i> <?php echo __("Stream Key"); ?></a></li>
            <?php
            $active = '';
        }
        if (empty($objLive->hideShare)) {
            ?>
            <li class="<?php echo $active; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Share information about your live"); ?>"><a data-toggle="tab" href="#tabShare"><i class="fa fa-share"></i> <?php echo __("Share"); ?></a></li>
            <?php
            $active = '';
        }
        if (User::isAdmin()) {
            ?>
            <li class="<?php echo $active; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Active Lives"); ?>"><a data-toggle="tab" href="#tabLiveAdmin"><i class="fa fa-user"></i> <?php echo __("Active Lives"); ?></a></li>
            <?php
            $active = '';
        }

        ?>
    </ul>
    <div class="tab-content">
        <?php
        $active = 'in active';
        if (Live::canRestream()) {
            ?>
            <div id="tabRestream" class="tab-pane fade <?php echo $active; ?>">
                <?php include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/livePanel.php'; ?>
            </div>
            <?php
            $active = '';
        }

        if (empty($objLive->hideAdvancedStreamKeys)) {
            ?>
            <div id="tabStreamKey" class="tab-pane fade <?php echo $active; ?>">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/tabs/tabStreamKey.php';
                $active = '';
                ?>
            </div>
            <?php
        }

        if (empty($objLive->hideShare)) {
            ?>
            <div id="tabShare" class="tab-pane fade <?php echo $active; ?>">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/tabs/tabShare.php';
                $active = '';
                ?>
            </div>
            <?php
        }
        if (User::isAdmin()) {
            ?>
            <div id="tabLiveAdmin" class="tab-pane fade <?php echo $active; ?>">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/tabs/tabAdmin.php';
                $active = '';
                ?>
            </div>
            <?php
        }
        ?>

    </div>
</div>
<div class="<?php echo getCSSAnimationClassAndStyle('animate__fadeInRight', 'live'); ?>">
    <?php
    AVideoPlugin::getLivePanel();
    ?>
</div>
