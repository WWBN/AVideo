<link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css"/>
<?php
$videos_id = getVideos_id();
$video = Video::getVideoLight($videos_id);

$htmlMediaTag = '<video ' . PlayerSkins::getPlaysinline() . ' preload="auto"
controls class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" id="mainVideo"
data-setup=\'{"techOrder": ["youtube","html5"]}\'>
</video>';
echo PlayerSkins::getMediaTag($video['filename']);

include __DIR__.'/../plugin/PlayerSkins/saveThumbnail.php';

include $global['systemRootPath'] . 'view/include/video.min.js.php';
?>