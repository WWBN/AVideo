<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/Channel.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$totalChannels = Channel::getTotalChannels();

if (!empty($_GET['page'])) {
    $_POST['current'] = intval($_GET['page']);
} else {
    $_POST['current'] = 1;
}
$current = $_POST['current'];
$_POST['rowCount'] = 10;
$channels = Channel::getChannels();

$totalPages = ceil($totalChannels / $_POST['rowCount']);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Channels"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <div class="panel" >

                <div class="panel-body" >
                    <ul class="pages">
                    </ul>
                    <?php
                    foreach ($channels as $value) {
                        $get = array('channelName' => $value['channelName']);
                        ?>
                        <div class="  bgWhite clear clearfix" style="margin: 10px 0;">
                            <div class="clear clearfix">
                                <img src="<?php echo User::getPhoto($value['id']); ?>"
                                     class="img img-thumbnail img-responsive pull-left" style="max-height: 100px; margin: 0 10px;" />
                                <a href="<?php echo User::getChannelLink($value['id']); ?>" class="btn btn-default">
                                    <i class="fas fa-play-circle"></i>
                                    <?php
                                    echo User::getNameIdentificationById($value['id']);
                                    ?>
                                </a>
                                <span class="pull-right">
                                    <?php echo Subscribe::getButton($value['id']); ?>
                                </span>
                                <div>
                                    <?php echo stripslashes(str_replace('\\\\\\\n', '<br/>', $value['about'])); ?>
                                </div>
                            </div>
                            <div class="clear clearfix">
                                <h2><?php echo __("Preview"); ?></h2>
                                <?php
                                $_POST['current'] = 1;
                                $_POST['rowCount'] = 6;
                                $_POST['sort']['created'] = "DESC";
                                $uploadedVideos = Video::getAllVideosAsync("viewable", $value['id']);
                                foreach ($uploadedVideos as $value2) {
                                    $imgs = Video::getImageFromFilename($value2['filename'], "video", true);
                                    $poster = $imgs->thumbsJpg;
                                    ?>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 ">
                                        <a href="<?php echo Video::getLink($value2['id'], $value2['clean_title'], false, $get); ?>" title="<?php echo $value2['title']; ?>" >
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

                    <ul class="pages">
                    </ul>
                </div>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(function () {
                $('.pages').bootpag({
                    total: <?php echo $totalPages; ?>,
                    page: <?php echo $current; ?>,
                    maxVisible: 10
                }).on('page', function (event, num) {
                    document.location = "<?php echo $global['webSiteRootURL']; ?>channels?page=" + num;
                });
            });
        </script>
    </body>
</html>
