<?php
require_once '../../videos/configuration.php';

$videos_id = intval($_REQUEST['videos_id']);
if (empty($videos_id)) {
    forbiddenPage('videos_id cannot be empty');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit videos_id ' . $videos_id);
}
if (!AVideoPlugin::isEnabledByName('TopMenu')) {
    forbiddenPage('Plugin is disabled ');
}
$video = Video::getVideoLight($videos_id);
$img = Video::getPoster($videos_id);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Set Info</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        //var_dump($video);
        ?>

        <?php
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURL);
        foreach ($menu as $key => $value) {
            $menuItems = MenuItem::getAllFromMenu($value['id'], true);
            foreach ($menuItems as $key2 => $value2) {
                ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>
                            <?php
                            if (!empty($value2['icon'])) {
                                ?>
                                <i class="<?php echo $value2['icon'] ?>"></i> 
                                <?php
                            }
                            ?>
                            <?php echo __($value2['title']); ?>
                        </h1>
                    </div>
                    <div class="panel-body">
                        <input type="url" class="form-control" placeholder="https://mysitetowhiutelist.com/" id="menuURL<?php echo $value2['id']; ?>" value="<?php echo TopMenu::getVideoMenuURL($videos_id, $value2['id']); ?>"/>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-block btn-success" onclick="saveMenuInfo<?php echo $value2['id']; ?>();"><i class="fas fa-solid"></i> <?php echo __('Save'); ?></button>
                    </div>
                </div>
                <script>
                    function saveMenuInfo<?php echo $value2['id']; ?>() {
                        modal.showPleaseWait();

                        var data = {
                            url: $('#menuURL<?php echo $value2['id']; ?>').val(),
                            menu_item_id: <?php echo $value2['id']; ?>,
                            videos_id: <?php echo intval($videos_id); ?>
                        }

                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/addVideoInfoSave.json.php',
                            data: data,
                            type: 'post',
                            success: function (response) {
                                modal.hidePleaseWait();
                                if (!response.error) {
                                    avideoAlert("<?php echo __("Congratulations!"); ?>", "", "success");
                                } else {
                                    avideoAlert("<?php echo __("Error"); ?>", response.msg, "error");
                                }
                            }
                        });
                        return false;
                    }

                </script>
                <?php
            }
        }
        ?>
        
                
        <?php
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForLoggedUsers);
        foreach ($menu as $key => $value) {
            $menuItems = MenuItem::getAllFromMenu($value['id'], true);
            foreach ($menuItems as $key2 => $value2) {
                ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>
                            <?php
                            if (!empty($value2['icon'])) {
                                ?>
                                <i class="<?php echo $value2['icon'] ?>"></i> 
                                <?php
                            }
                            ?>
                            <?php echo __($value2['title']); ?>
                            (<?php echo __('Logged Users Only'); ?>)
                        </h1>
                    </div>
                    <div class="panel-body">
                        <input type="url" class="form-control" placeholder="https://mysitetowhiutelist.com/" id="menuURL<?php echo $value2['id']; ?>" value="<?php echo TopMenu::getVideoMenuURL($videos_id, $value2['id']); ?>"/>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-block btn-success" onclick="saveMenuInfo<?php echo $value2['id']; ?>();"><i class="fas fa-solid"></i> <?php echo __('Save'); ?></button>
                    </div>
                </div>
                <script>
                    function saveMenuInfo<?php echo $value2['id']; ?>() {
                        modal.showPleaseWait();

                        var data = {
                            url: $('#menuURL<?php echo $value2['id']; ?>').val(),
                            menu_item_id: <?php echo $value2['id']; ?>,
                            videos_id: <?php echo intval($videos_id); ?>
                        }

                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/addVideoInfoSave.json.php',
                            data: data,
                            type: 'post',
                            success: function (response) {
                                modal.hidePleaseWait();
                                if (!response.error) {
                                    avideoAlert("<?php echo __("Congratulations!"); ?>", "", "success");
                                } else {
                                    avideoAlert("<?php echo __("Error"); ?>", response.msg, "error");
                                }
                            }
                        });
                        return false;
                    }

                </script>
                <?php
            }
        }
        ?>
                
                <?php
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanWatchVideo);
        foreach ($menu as $key => $value) {
            $menuItems = MenuItem::getAllFromMenu($value['id'], true);
            foreach ($menuItems as $key2 => $value2) {
                ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>
                            <?php
                            if (!empty($value2['icon'])) {
                                ?>
                                <i class="<?php echo $value2['icon'] ?>"></i> 
                                <?php
                            }
                            ?>
                            <?php echo __($value2['title']); ?>
                            (<?php echo __('Paid Users Only'); ?>)
                        </h1>
                    </div>
                    <div class="panel-body">
                        <input type="url" class="form-control" placeholder="https://mysitetowhiutelist.com/" id="menuURL<?php echo $value2['id']; ?>" value="<?php echo TopMenu::getVideoMenuURL($videos_id, $value2['id']); ?>"/>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-block btn-success" onclick="saveMenuInfo<?php echo $value2['id']; ?>();"><i class="fas fa-solid"></i> <?php echo __('Save'); ?></button>
                    </div>
                </div>
                <script>
                    function saveMenuInfo<?php echo $value2['id']; ?>() {
                        modal.showPleaseWait();

                        var data = {
                            url: $('#menuURL<?php echo $value2['id']; ?>').val(),
                            menu_item_id: <?php echo $value2['id']; ?>,
                            videos_id: <?php echo intval($videos_id); ?>
                        }

                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/addVideoInfoSave.json.php',
                            data: data,
                            type: 'post',
                            success: function (response) {
                                modal.hidePleaseWait();
                                if (!response.error) {
                                    avideoAlert("<?php echo __("Congratulations!"); ?>", "", "success");
                                } else {
                                    avideoAlert("<?php echo __("Error"); ?>", response.msg, "error");
                                }
                            }
                        });
                        return false;
                    }

                </script>
                <?php
            }
        }
        ?>
                
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
