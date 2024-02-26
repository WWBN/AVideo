<?php
if (User::isLogged()) {
    $tags = User::getTags(User::getId());
    $tagsStr = '';
    foreach ($tags as $value) {
        $tagsStr .= "<span class=\"label label-{$value->type} fix-width\">{$value->text}</span>";
    }
    $user = new User("");
    $user->loadSelfUser();
    if (!empty($_REQUEST['basicInfoOnly'])) {
        include $global['systemRootPath'] . './view/userBasicInfo.php';
    } else {
        ?>
        <div class="row">
            <div>
                <div class="panel panel-default" id="userTabsPanel">
                    <div class="panel-heading tabbable-line">
                        <div class="pull-right hidden-md hidden-sm hidden-xs">
                            <?php echo $tagsStr; ?>
                        </div>
                        <div class="clearfix"></div>
                        <ul class="nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#basicInfo" id="aBasicInfo"><?php echo __("Basic Info") ?></a></li>

                            <?php if (empty($advancedCustomUser->disablePersonalInfo)) { ?>
                                <li><a data-toggle="tab" href="#personalInfo" id="aPersonalInfo"><?php echo __("Personal Info") ?></a></li>
                            <?php } ?>
                            <?php echo AVideoPlugin::profileTabName($user->getId()); ?>
                        </ul>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div id="basicInfo" class="tab-pane fade in active" style="padding: 10px 0;">
                                <?php include $global['systemRootPath'] . './view/userBasicInfo.php'; ?>
                            </div>

                            <?php if (empty($advancedCustomUser->disablePersonalInfo)) { ?>
                                <div id="personalInfo" class="tab-pane fade"  style="padding: 10px 0;">
                                    <?php
                                    include $global['systemRootPath'] . './view/userPersonalInfo.php';
                                    ?>
                                </div>
                            <?php } ?>
                            <?php echo AVideoPlugin::profileTabContent($user->getId()); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php
    }
    ?>


    <script>
        $(document).ready(function () {
    <?php
    if (!empty($_REQUEST['tab'])) {
        $tab = preg_replace('/[^a-z0-9_-]/i', '', $_REQUEST['tab']);
        ?>
                $('#userTabsPanel a[href="#<?php echo $tab; ?>"]').trigger('click');
        <?php
    }
    ?>
        });
    </script>
    <?php
} else {
    include $global['systemRootPath'] . './view/userLogin.php';
}
?>