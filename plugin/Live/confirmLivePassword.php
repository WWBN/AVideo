<?php
require_once '../../videos/configuration.php';

$key = @$_REQUEST['key'];

if (empty($key)) {
    forbiddenPage('Key is undefined');
}

$row = LiveTransmition::getFromKey($key);

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}

$livet = LiveTransmition::getFromRequest();
$liveTitle = $livet['title'];
$liveDescription = $livet['description'];
$liveUrl = Live::getLinkToLiveFromUsers_id($user_id);

$img = "{$global['webSiteRootURL']}plugin/Live/getImage.php?u={$_GET['u']}&format=jpg";
if (!empty($_REQUEST['live_schedule'])) {
    $img = addQueryStringParameter($img, 'live_schedule', intval($_REQUEST['live_schedule']));
}
$imgw = 640;
$imgh = 360;
$global['ignoreChat2'] = 1;
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Confirm Password"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

        <meta property="fb:app_id"             content="774958212660408" />
        <meta property="og:url"                content="<?php echo $liveUrl; ?>" />
        <meta property="og:type"               content="video.other" />
        <meta property="og:title"              content="<?php echo str_replace('"', '', $liveTitle); ?> - <?php echo $config->getWebSiteTitle(); ?>" />
        <meta property="og:description"        content="<?php echo str_replace('"', '', $liveTitle); ?>" />
        <meta property="og:image"              content="<?php echo $img; ?>" />
        <meta property="og:image:width"        content="<?php echo $imgw; ?>" />
        <meta property="og:image:height"       content="<?php echo $imgh; ?>" />
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
                background-image: url('<?php echo $img; ?>');
                background-size: cover;
                opacity: 0.3;
                filter: alpha(opacity=30); /* For IE8 and earlier */
            }
        </style>
    </head>
    <body>
        <div id="bg"></div>

        <!-- Modal -->
        <div id="myModal" class="modal fade in" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title">
                            <center>
                                <i class="fas fa-lock"></i> <?php echo $video['title']; ?> <?php echo __("is Private"); ?>
                            </center>
                        </h1>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <img src="<?php echo $img; ?>" class="img img-responsive"/>
                            </div>
                            <div class="col-sm-6">
                                <center>
                                    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                        <?php
                                        if (!empty($_POST['live_password'])) {
                                            ?>
                                            <div class="alert alert-danger"><?php echo __("Your password does not match!"); ?></div>
                                            <?php
                                        }
                                        ?>
                                        <div class="form-group">
                                            <label for="live_password"><?php echo __("This Live Requires a Password"); ?></label>
                                            <?php
                                            echo getInputPassword('live_password', 'class="form-control"', __("Password"));
                                            ?>
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
