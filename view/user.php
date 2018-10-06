<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

$tags = User::getTags(User::getId());
$tagsStr = "";
foreach ($tags as $value) {
    $tagsStr .= "<span class=\"label label-{$value->type} fix-width\">{$value->text}</span>";
}
$json_file = url_get_contents("{$global['webSiteRootURL']}plugin/CustomizeAdvanced/advancedCustom.json.php");
// convert the string to a json object
$advancedCustom = json_decode($json_file);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("User"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/Croppie/croppie.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/Croppie/croppie.min.js" type="text/javascript"></script>
    </head>

    <body>
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>

        <div class="container-fluid">
            <?php
            if (User::isLogged()) {
                $user = new User("");
                $user->loadSelfUser();
                ?>
                <div class="row">
                    <div>
                        <form class="form-compact well form-horizontal"  id="updateUserForm" onsubmit="">
                            <?php echo $tagsStr; ?>
                            <fieldset>
                                <legend>
                                    <?php echo __("Update your user") ?>

                                </legend>
                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#basicInfo" id="aBasicInfo"><?php echo __("Basic Info") ?></a></li>

                                    <?php if (empty($advancedCustom->disablePersonalInfo)) { ?>
                                        <li><a data-toggle="tab" href="#personalInfo" id="aPersonalInfo"><?php echo __("Personal Info") ?></a></li>
                                    <?php } ?>
                                </ul>

                                <div class="tab-content">
                                    <div id="basicInfo" class="tab-pane fade in active" style="padding: 10px 0;">
                                        <?php
                                        include './userBasicInfo.php';
                                        ?>
                                    </div>

                                    <?php if (empty($advancedCustom->disablePersonalInfo)) { ?>
                                        <div id="personalInfo" class="tab-pane fade"  style="padding: 10px 0;">
                                            <?php
                                            include './userPersonalInfo.php';
                                            ?>
                                        </div>
                                    <?php } ?>
                                </div>

                                <!-- Button -->
                                <div class="form-group">
                                    <hr>
                                    <div class="col-md-12">
                                        <center>
                                            <button type="submit" class="btn btn-primary btn-block btn-lg" ><?php echo __("Save"); ?> <span class="fa fa-save"></span></button>
                                        </center>
                                    </div>
                                </div>
                            </fieldset>
                        </form>

                    </div>
                </div>

                <?php
            } else {
                include './userLogin.php';
            }
            ?>

        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

    </body>
</html>
