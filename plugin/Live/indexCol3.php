<div class="clear clearfix"></div>

<div class="tabbable-line <?php echo getCSSAnimationClassAndStyle('animate__fadeInRight', 'live'); ?>"  id="indexTabs">
    <ul class="nav nav-tabs">
        <?php
        $active = 'active';
        echo "<!-- change the users_id $users_id line ".__LINE__." -->";
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

echo "<!-- change the users_id $users_id line ".__LINE__." -->";
        ?>
    </ul>
    <div class="tab-content">
        <?php
        $active = 'in active';
        if (Live::canRestream()) {
            echo "<!-- change the users_id $users_id line ".__LINE__." -->";
            ?>
            <div id="tabRestream" class="tab-pane fade <?php echo $active; ?>">
                <?php include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/livePanel.php'; ?>
            </div>
            <?php
            $active = '';
        }
        echo "<!-- change the users_id $users_id line ".__LINE__." -->";

        if (empty($objLive->hideAdvancedStreamKeys)) {
            echo "<!-- change the users_id $users_id line ".__LINE__." -->";
            ?>
            <div id="tabStreamKey" class="tab-pane fade <?php echo $active; ?>">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/tabs/tabStreamKey.php';
                $active = '';
                ?>
            </div>
            <?php
        }
        echo "<!-- change the users_id $users_id line ".__LINE__." -->";

        if (empty($objLive->hideShare)) {
            echo "<!-- change the users_id $users_id line ".__LINE__." -->";
            ?>
            <div id="tabShare" class="tab-pane fade <?php echo $active; ?>">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/tabs/tabShare.php';
                $active = '';
                ?>
            </div>
            <?php
        }
        echo "<!-- change the users_id $users_id line ".__LINE__." -->";
        if (User::isAdmin()) {
            echo "<!-- change the users_id $users_id line ".__LINE__." -->";
            ?>
            <div id="tabLiveAdmin" class="tab-pane fade <?php echo $active; ?>">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/tabs/tabAdmin.php';
                $active = '';
                ?>
            </div>
            <?php
        }
        echo "<!-- change the users_id $users_id line ".__LINE__." -->";
        ?>

    </div>
</div>
<div class="<?php echo getCSSAnimationClassAndStyle('animate__fadeInRight', 'live'); ?>">
    <?php
echo "<!-- change the users_id $users_id line ".__LINE__." -->";
    AVideoPlugin::getLivePanel();
    echo "<!-- change the users_id $users_id line ".__LINE__." -->";
    ?>
</div>
