<?php
require_once '../../videos/configuration.php';
require_once '../../objects/functions.php';

require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    header("location: {$global['webSiteRootURL']}user");
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: <?php echo __("Upload your file"); ?></title>

        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

        <!-- Google web fonts -->
        <link href="http://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700" rel='stylesheet' />

        <!-- The main CSS file -->
        <link href="view/mini-upload-form/assets/css/style.css" rel="stylesheet" />


    </head>

    <body>
        <?php
        include '../include/navbar.php';
        ?>

        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-lg-9">
                    <form id="upload" method="post" action="fileUpload" enctype="multipart/form-data">
                        <div id="drop">
                            <?php echo __("Drop Here"); ?>

                            <a><?php echo __("Browse"); ?></a>
                            <input type="file" name="upl" multiple />
                        </div>

                        <ul>
                            <!-- The file uploads will be shown here -->
                        </ul>

                    </form>

                </div>
                <div class="col-xs-12 col-sm-12 col-lg-3">
                    <div class="alert alert-info">
                        <h1>
                            <span class="glyphicon glyphicon-info-sign" style="font-size:1em;"></span>
                            <?php echo __("Your maximum file size is:"), " ", "" . get_max_file_size() . ""; ?>
                        </h1>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h1>
                            <span class="glyphicon glyphicon-warning-sign" style="font-size:1em;"></span>
                            <?php echo __("This page works only with a MP4 File, if you have or need any other format, try to install your own <a href='https://github.com/DanielnetoDotCom/YouPHPTube-Encoder' class='btn btn-warning'>encoder</a> or use the <a href='https://encoder.youphptube.com/' class='btn btn-warning'>public</a> one"); ?>
                        </h1>
                    </div>
                    <?php
                    if (!empty($global['videoStorageLimitMinutes'])) {
                        $secondsTotal = getMinutesTotalVideosLength();
                        ?>
                        <div class="alert alert-warning"><?php printf(__("You have about %s minutes left of video storage!"), ($global['videoStorageLimitMinutes']-$secondsTotal)); ?></div>
                        <?php
                    }
                    ?>
                </div>
            </div>


        </div><!--/.container-->

<?php
include '../include/footer.php';
?>


        <!-- JavaScript Includes -->
        <script src="view/mini-upload-form/assets/js/jquery.knob.js"></script>

        <!-- jQuery File Upload Dependencies -->
        <script src="view/mini-upload-form/assets/js/jquery.ui.widget.js"></script>
        <script src="view/mini-upload-form/assets/js/jquery.iframe-transport.js"></script>
        <script src="view/mini-upload-form/assets/js/jquery.fileupload.js"></script>

        <!-- Our main JS file -->
        <script src="view/mini-upload-form/assets/js/script.js"></script>


    </body>
</html>
