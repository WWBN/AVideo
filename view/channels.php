<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/Channel.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$channels = Channel::getChannels();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Channels"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>   
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <div class="bgWhite list-group-item" >
                <?php
                foreach ($channels as $value) {
                    ?>
                    <div class="  bgWhite clear clearfix" style="margin: 10px 0;">
                        <div class="clear clearfix">
                            <img src="<?php echo User::getPhoto($value['id']); ?>" 
                                 class="img img-thumbnail img-responsive pull-left" style="max-height: 100px; margin: 0 10px;" />

                            <a href="<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $value['id']; ?>/" class="btn btn-default">
                                <i class="fa fa-youtube-play"></i>
                                <?php echo User::getNameIdentificationById($value['id']); ?> 
                            </a>
                            <span class="pull-right">
                                <?php echo Subscribe::getButton($value['id']); ?> 
                            </span>
                            <div>
                                <?php echo $value['about']; ?>
                            </div>
                        </div>
                        <div class="clear clearfix">
                            <h2>Preview</h2>
                            <?php
                            $_POST['current'] = 1;
                            $_POST['rowCount'] = 6;
                            $uploadedVideos = Video::getAllVideos("viewable", $value['id']);
                            foreach ($uploadedVideos as $value2) {
                                $imgs = Video::getImageFromFilename($value2['filename']);
                                $poster = $imgs->thumbsJpg;
                                ?>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 ">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value2['clean_title']; ?>" title="<?php echo $value2['title']; ?>" >
                                        <img src="<?php echo $poster; ?>" alt="<?php echo $value2['title']; ?>" class="img img-responsive img-thumbnail" />
                                    </a>
                                    <div class="text-muted" style="font-size: 0.8em;"><?php echo $value2['title']; ?></div>

                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <?php
        include 'include/footer.php';
        ?>
        <script>
            $(function () {
            });
        </script>
    </body>
</html>



