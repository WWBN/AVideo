<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!CreateUserManager::isManager()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Managers</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
        <style>
        </style>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#ug">Users Groups</a></li>
                <?php
                if(User::isAdmin()){
                ?>
                <li><a data-toggle="tab" href="#mn">Managers</a></li>
                <?php
                }
                ?>
            </ul>

            <div class="tab-content">
                <div id="ug" class="tab-pane fade in active">
                    <?php
                        include_once './editorGroups.php';
                    ?>
                </div>
                <?php
                if(User::isAdmin()){
                ?>
                <div id="mn" class="tab-pane fade">
                    <?php
                        include_once './editorManagers.php';
                    ?>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
    </body>
</html>
