<?php
global $isEmbed;
$isEmbed = 1;
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$dir = $global['systemRootPath'] . 'plugin/PlayerSkins/skins/';
$names = array();
foreach (glob($dir . '*.css') as $file) {
    $path_parts = pathinfo($file);
    $names[] = $path_parts['filename'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
        </script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?> :: Player Sample</title>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.3.1.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video.js" type="text/javascript"></script>
        <?php
        if (!empty($_GET['playerSkin'])) {
            ?>
            <link href="<?php echo $global['webSiteRootURL']; ?>plugin/PlayerSkins/skins/<?php echo $names[intval(array_search($_GET['playerSkin'], $names))]; ?>.css" rel="stylesheet" type="text/css"/>
            <?php
        }
        ?>
        <style>
            body {
                padding: 0 !important;
                margin: 0 !important;
                background-color: black;
                height: 100vh;
            }
        </style>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <video id="mainVideo" poster="<?php echo $global['webSiteRootURL']; ?>plugin/PlayerSkins/bg.jpg" controls
               class="video-js vjs-default-skin vjs-big-play-centered" loop style="height: 100%; width: 100%;">
            <source src="bg.mp4" type="video/mp4">
            <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
        </video>
        <script>
        $(document).ready(function () {
            if (typeof player === 'undefined') {
                player = videojs('mainVideo');
            }
        });
        </script>
    </body>
</html>
