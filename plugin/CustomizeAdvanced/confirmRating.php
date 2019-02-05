<?php
require_once '../videos/configuration.php';

$images = Video::getImageFromFilename($video['filename']);
$img = $images->poster;
if (!empty($images->posterPortrait)) {
    $img = $images->posterPortrait;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Confirm Rating</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #bg{
                position: fixed;
                width: 100%;
                height: 100%;
                background-image: url('<?php echo $images->poster; ?>');
                background-size: cover;
                opacity: 0.3;
                filter: alpha(opacity=30); /* For IE8 and earlier */
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div id="bg"></div>

        <!-- Modal -->
        <div id="myModal" class="modal fade in" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Title: <?php echo $video['title']; ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <img src="<?php echo $img; ?>" class="img img-responsive"/>
                            </div>
                            <div class="col-sm-6">
                                <center>
                                <?php
                                    include $global['systemRootPath'] . 'view/rrating/rating-'.$video['rrating'].'_text.php';
                                ?>
                                </center>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer" >
                        <a href="<?php echo $_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'], "?")===false?"?":"&"; ?>rrating=1" class="btn btn-success pull-right"><i class="fas fa-check-circle"></i> <?php echo __("Confirm"); ?></a>
                        <button class="btn btn-danger pull-right" onclick="closeConfirmRating();"><i class="fas fa-times-circle"></i> <?php echo __("Cancel"); ?></button>
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
            
            function closeConfirmRating(){
                if(typeof window.top.closeFlixFullScreen !== 'undefined'){
                    window.top.closeFlixFullScreen();
                }else{
                    document.location = "<?php echo $global['webSiteRootURL']; ?>";
                }
            }
        </script>
    </body>
</html>
