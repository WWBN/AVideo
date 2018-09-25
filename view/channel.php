<?php
global $global, $config, $isChannel;
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';

if (empty($_GET['channelName'])) {
    if (User::isLogged()) {
        $_GET['user_id'] = User::getId();
    } else {
        return false;
    }
} else {
    $user = User::getChannelOwner($_GET['channelName']);
    if (!empty($user)) {
        $_GET['user_id'] = $user['id'];
    } else {
        $_GET['user_id'] = $_GET['channelName'];
    }
}
$user_id = $_GET['user_id'];

$isMyChannel = false;
if (User::isLogged() && $user_id == User::getId()) {
    $isMyChannel = true;
}

$user = new User($user_id);
$_GET['channelName'] = $user->getChannelName();

$_POST['sort']['created'] = "DESC";

if (empty($_GET['current'])) {
    $_POST['current'] = 1;
} else {
    $_POST['current'] = $_GET['current'];
}
$current = $_POST['current'];
$rowCount = 25;
$_POST['rowCount'] = $rowCount;
$uploadedVideos = Video::getAllVideos("a", $user_id);
$uploadedTotalVideos = Video::getTotalVideos("a", $user_id);

$totalPages = ceil($uploadedTotalVideos / $rowCount);

unset($_POST['sort']);
unset($_POST['rowCount']);
unset($_POST['current']);

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Channel"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script>
            /*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
            $.widget.bridge('uibutton', $.ui.button);
            $.widget.bridge('uitooltip', $.ui.tooltip);
            var channelName = '<?php echo $_GET['channelName']; ?>';
        </script>
        <!-- users_id = <?php echo $user_id; ?> -->
        <link href="<?php echo $global['webSiteRootURL']; ?>/plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>
        <style>
            .galleryVideo {
                padding-bottom: 10px;
            }
        </style>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <div class="bgWhite list-group-item gallery clear clearfix" >
                <div class="row bg-info profileBg" style="background-image: url('<?php echo $global['webSiteRootURL'], $user->getBackgroundURL(); ?>')">
                    <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo $user->_getName(); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;"/>
                </div>
                <div class="row"><div class="col-6 col-md-12">
                        <h1 class="pull-left">
                            <?php
                            echo $user->getNameIdentificationBd();
                            ?></h1>
                        <span class="pull-right">
                            <?php
                            echo Subscribe::getButton($user_id);
                            ?>
                        </span>
                    </div></div>
                <div class="col-md-12">
                    <?php echo nl2br($user->getAbout()); ?>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php
                            if ($isMyChannel) {
                                ?>
                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                                    <span class="glyphicon glyphicon-film"></span>
                                    <span class="glyphicon glyphicon-headphones"></span>
                                    <?php echo __("My videos"); ?>
                                </a>
                                <?php
                            } else {
                                echo __("My videos");
                            }
                            echo YouPHPTubePlugin::getChannelButton();
                            ?>
                        </div>
                        <div class="panel-body">
                            <?php
                            if (!empty($uploadedVideos[0])) {
                                $video = $uploadedVideos[0];
                                $obj = new stdClass();
                                $obj->BigVideo = true;
                                $obj->Description = false;
                                include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
                                unset($uploadedVideos[0]);
                            }
                            ?>
                            <div class="row mainArea">
                                <?php
                                createGallerySection($uploadedVideos);
                                ?>
                            </div>
                        </div>

                        <div class="panel-footer">
                            <ul id="channelPagging"></ul>
                            <script>
                                $(document).ready(function () {
                                    $('#channelPagging').bootpag({
                                        total: <?php echo $totalPages; ?>,
                                        page: <?php echo $current; ?>,
                                        maxVisible: 10
                                    }).on('page', function (event, num) {
                                        document.location = ("<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $_GET['channelName']; ?>?current=" + num);
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="channelPlaylists">
                <?php
                    include $global['systemRootPath'] . 'view/channelPlaylist.php';
                ?>
                </div>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/script.js" type="text/javascript"></script>
    </body>
</html>
