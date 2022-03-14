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

function createMenuSaveForm($menu) {
    global $global, $videos_id;
    foreach ($menu as $key => $value) {
        $menuItems = MenuItem::getAllFromMenu($value['id'], true);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <?php
                    if (!empty($value['icon'])) {
                        ?>
                        <i class="<?php echo $value['icon'] ?>"></i> 
                        <?php
                    }
                    ?>
                    <?php echo __($value['menuName']); ?>
                </strong>
            </div>
            <div class="panel-body">
                <?php
                foreach ($menuItems as $key2 => $value2) {
                    ?>
                    <div class="row">
                        <div class="col-xs-9">
                            <div class="form-group">
                                <div class="input-group">
                                    <label class="input-group-addon" for="menuURL<?php echo $value2['id']; ?>">
                                        <?php
                                        if (!empty($value2['icon'])) {
                                            ?>
                                            <i class="<?php echo $value2['icon'] ?>"></i> 
                                            <?php
                                        }
                                        ?>
                                        <?php echo __($value2['title']); ?>
                                    </label>
                                    <input type="url" class="form-control" placeholder="<?php echo __($value2['title']); ?>" id="menuURL<?php echo $value2['id']; ?>" value="<?php echo TopMenu::getVideoMenuURL($videos_id, $value2['id']); ?>"/>
                                </div>
                            </div>

                        </div>

                        <div class="col-xs-3">
                            <button class="btn btn-block btn-success" onclick="saveMenuInfo<?php echo $value2['id']; ?>();">
                                <i class="fas fa-save"></i> 
                                <?php echo __('Save'); ?>
                            </button>
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
                ?>
            </div>
        </div>
        <?php
    }
}
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
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURL);
        createMenuSaveForm($menu);
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForLoggedUsers);
        createMenuSaveForm($menu);
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanWatchVideo);
        createMenuSaveForm($menu);
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanNotWatchVideo);
        createMenuSaveForm($menu);

        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
