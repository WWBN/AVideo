<?php
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header('HTTP/1.0 404 Not Found', true, 404);
$img = "".getCDN()."view/img/this-video-is-not-available.jpg";
$poster = "".getCDN()."view/img/this-video-is-not-available.jpg";
$imgw = 1280;
$imgh = 720;
unset($_SESSION['type']);
session_write_close();
$video = array();
$video['id'] = 0;
$video['type'] = 'notfound';
$video['rotation'] = 0;
$video['videoLink'] = "";
$video['title'] = __("Video Not Available");
$video['clean_title'] = "video-not-available";
$video['description'] = "";
$video['duration'] = "";
$video['creator'] = "";
$video['likes'] = "";
$video['dislikes'] = "";
$video['category'] = "embed";
$video['views_count'] = 0;
$video['filename'] = "";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __('Video Not Found'); ?></title>
        <link href="<?php echo getCDN(); ?>plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <div class="container-fluid principalContainer" id="modeYoutubePrincipal">
            <?php
            require "{$global['systemRootPath']}view/modeYoutubeBundle.php";
            ?>
        </div>        
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        showCloseButton();
        ?>
        <script>
            $(function () { 
                <?php
                if(!empty($_REQUEST['404ErrorMsg'])){
                    echo "avideoAlertInfo(\"{$_REQUEST['404ErrorMsg']}\");";
                }
                ?>
            });
        </script>
    </body>
</html>
