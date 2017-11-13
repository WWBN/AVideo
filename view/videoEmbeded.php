<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
$video = Video::getVideo();
if (empty($video)) {
    die(__("Video not found"));
}

/*
 * Swap aspect ratio for rotated (vvs) videos
 */
if ($video['rotation'] === "90" || $video['rotation'] === "270") {
    $embedResponsiveClass = "embed-responsive-9by16";
    $vjsClass = "vjs-9-16";
} else {
    $embedResponsiveClass = "embed-responsive-16by9";
    $vjsClass = "vjs-16-9";
}
$obj = new Video("", "", $video['id']);
$resp = $obj->addView();
if ($video['type'] !== "audio") {
    $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
} else {
    $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <?php
        require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
        echo YouPHPTubePlugin::getHeadCode();
        ?>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo $video['title']; ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-3.2.0.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-rotatezoom/videojs.zoomrotate.js" type="text/javascript"></script>
        <style>
        </style>
    </head>

    <body>
        <div class="embed-responsive <?php echo $embedResponsiveClass; ?> ">
            <?php
            if ($video['type'] == "embed") {
                ?>
                <iframe class="embed-responsive-item" src="<?php
                echo parseVideos($video['videoLink']);
                if ($config->getAutoplay()) {
                    echo "?autoplay=1";
                }
                ?>"></iframe>
    <?php
} else if ($video['type'] == "audio" && !file_exists("{$global['systemRootPath']}videos/{$video['filename']}.mp4")) {
    ?>
                <audio controls class="center-block video-js vjs-default-skin vjs-big-play-centered"  id="mainAudio"  data-setup='{ "fluid": true }'
                       poster="<?php echo $global['webSiteRootURL']; ?>img/recorder.gif">
                    <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg" type="audio/ogg" />
                    <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3" type="audio/mpeg" />
                    <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3">horse</a>
                </audio>
    <?php
} else {
    ?>
                <video poster="<?php echo $poster; ?>" controls crossorigin  width="auto" height="auto"
                       class="video-js vjs-default-skin vjs-big-play-centered <?php echo $vjsClass; ?> " id="mainVideo"  data-setup='{"fluid": true }'>
    <?php
    echo getSources($video['filename']);
    ?>
                    <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
                </video>
                <script>
                    $(document).ready(function () {
                        //Prevent HTML5 video from being downloaded (right-click saved)?
                        $('#mainVideo').bind('contextmenu', function () {
                            return false;
                        });
                    });
                </script>
            <?php
        }
        ?>
        </div>
<?php
echo YouPHPTubePlugin::getFooterCode();
?>
    </body>
</html>
