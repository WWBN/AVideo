<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (User::isLogged() && !empty($_GET['redirectUri'])) {
    header("Location: {$_GET['redirectUri']}");
}
$tags = User::getTags(User::getId());
$tagsStr = "";
foreach ($tags as $value) {
    $tagsStr .= "<span class=\"label label-{$value->type} fix-width\">{$value->text}</span>";
}
//$json_file = url_get_contents("{$global['webSiteRootURL']}plugin/CustomizeAdvanced/advancedCustom.json.php");
// convert the string to a json object
//$advancedCustom = _json_decode($json_file);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("My Account") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo getCDN(); ?>view/js/Croppie/croppie.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getCDN(); ?>view/js/Croppie/croppie.min.js" type="text/javascript"></script>
        <link href="<?php echo getCDN(); ?>view/js/bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getCDN(); ?>view/js/bootstrap-fileinput/js/fileinput.min.js" type="text/javascript"></script>
        <link href="<?php echo getCDN(); ?>view/css/bodyFadein.css" rel="stylesheet" type="text/css"/>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>

        <div class="container-fluid">
            <?php
            if (User::isLogged()) {
                $user = new User("");
                $user->loadSelfUser();
                ?>
                <div class="row">
                    <div>
                        <div class="panel panel-default">
                            <div class="panel-heading tabbable-line">
                                <div class="pull-right">
                                    <?php echo $tagsStr; ?>
                                </div>
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
                                            <?php
                                            include $global['systemRootPath'] . './view/userBasicInfo.php';
                                            ?>
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
            } else {
                include $global['systemRootPath'] . './view/userLogin.php';
            }
            ?>

        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
