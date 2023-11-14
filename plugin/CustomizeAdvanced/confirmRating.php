<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

setIsConfirmationPage();
$images = Video::getImageFromFilename($video['filename']);
$img = $images->poster;
if (!empty($images->posterPortrait) && !ImagesPlaceHolders::isDefaultImage($images->posterPortrait)) {
    $img = $images->posterPortrait;
}
$imgw = 1280;
$imgh = 720;
$metaDescription = $title = getSEOTitle($video['title']);
$ogURL = Video::getLinkToVideo($video['id'], $video['clean_title'], false, false);
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <title><?php echo __("Confirm Rating") . $config->getPageTitleSeparator() . $title; ?></title>
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
                background-image: url('<?php echo $images->poster; ?>');
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
                        <h2 class="modal-title">
                            <?php echo $video['title']; ?>
                        </h2>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <?php 
                                echo  Video::getRratingIMG($video['rrating']);
                                ?>
                            </div>
                            <div class="col-sm-4">
                                <img src="<?php echo $img; ?>" class="img img-responsive"/>
                            </div>
                            <div class="col-sm-6 text-center">                                
                                <?php 
                                echo  Video::getRratingText($video['rrating']);
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer" >
                        <a href="<?php echo $_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "?") === false ? "?" : "&"; ?>rrating=1" class="btn btn-success pull-right"><i class="fas fa-check-circle"></i> <?php echo __("Confirm"); ?></a>
                        <a href="<?php echo getHomePageURL(); ?>" class="btn btn-danger pull-right"><i class="fas fa-times-circle"></i> <?php echo __("Cancel"); ?></a>
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
