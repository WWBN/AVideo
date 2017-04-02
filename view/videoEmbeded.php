<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
$video = Video::getVideo();

//var_dump($video);exit;
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="img/favicon.ico">
        <title><?php echo $global['webSiteTitle']; ?> :: <?php echo $video['title']; ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>

        <style>
        </style>
    </head>

    <body>
        <div align="center" class="embed-responsive embed-responsive-16by9">
            <video poster="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.jpg" controls crossorigin class="img img-responsive" id="mainVideo">
                <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>" type="video/mp4">
                <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.webm" type="video/webm">
                <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
            </video>
        </div>

    </body>
</html>
