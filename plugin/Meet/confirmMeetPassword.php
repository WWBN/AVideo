<?php
require_once '../../videos/configuration.php';

require_once $global['systemRootPath'] . 'plugin/Meet/validateMeet.php';

if (Meet::validatePassword($meet_schedule_id, @$_POST['meet_password'])) {
    $url = Meet::getMeetLink($meet_schedule_id);
    header("Location: {$url}");
    exit;
}
$meet = new Meet_schedule($meet_schedule_id);

$img = User::getBackgroundURLFromUserID($meet->getUsers_id());
$photo = User::getPhoto($meet->getUsers_id());
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Confirm Meet Password") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            body {
                padding-top: 0;
            }
            footer{
                display: none;
            }
            #bg{
                position: fixed;
                width: 100%;
                height: 100%;
                background-image: url('<?php echo $global['webSiteRootURL'], $img; ?>');
                background-size: cover;
                opacity: 0.3;
                filter: alpha(opacity=30); /* For IE8 and earlier */
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        //include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div id="bg"></div>

        <!-- Modal -->
        <div id="myModal" class="modal fade in" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title">
                            <center>
                                <i class="fas fa-lock"></i> <?php echo __("Meet"); ?> <?php echo $meet->getTopic(); ?> <?php echo __("is Private"); ?>
                            </center>
                        </h1>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <img src="<?php echo $photo; ?>" class="img img-responsive"/>
                            </div>
                            <div class="col-sm-6">
                                <center>
                                    <form method="post" action="<?php echo getSelfURI(); ?>">
                                        <?php
                                        if (!empty($_POST['meet_password'])) {
                                            ?>
                                            <div class="alert alert-danger"><?php echo __("Your password does not match!"); ?></div>
                                            <?php
                                        }
                                        ?>
                                        <div class="form-group">
                                            <label for="meet_password"><?php echo __("This Meet Requires a Password"); ?></label>
                                            <input type="text" class="form-control" id="meet_password" name="meet_password" placeholder="<?php echo __("Password"); ?>" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check-circle"></i> <?php echo __("Confirm"); ?></button>
                                            </div>
                                            <div class="col-md-6">
                                                <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-danger  btn-block"><i class="fas fa-times-circle"></i> <?php echo __("Cancel"); ?></a>
                                            </div>
                                        </div>
                                    </form>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script type="text/javascript">
            $(window).on('load', function () {
                $('#myModal').modal('show');
            });
        </script>
    </body>
</html>
