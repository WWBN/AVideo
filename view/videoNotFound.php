<?php
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header('HTTP/1.0 404 Not Found', true, 404);
$img = ImagesPlaceHolders::getVideoNotFoundPoster(ImagesPlaceHolders::$RETURN_URL);
$poster = $img;
$imgw = 1280;
$imgh = 720;
unset($_SESSION['type']);
_session_write_close();
$video = [];
$video['id'] = 0;
$video['type'] = 'notfound';
$video['rotation'] = 0;
$video['videoLink'] = '';
$video['title'] = __("Video Not Available");
$video['clean_title'] = "video-not-available";
$video['description'] = '';
$video['duration'] = '';
$video['creator'] = '';
$video['likes'] = '';
$video['dislikes'] = '';
$video['category'] = "embed";
$video['views_count'] = 0;
$video['filename'] = '';

$_page = new Page(array('Video not found'), 'videoNotFound');
$_page->setExtraStyles(
    array(
        'plugin/Gallery/style.css',
    )
);
?>
<!-- view videoNotFound.php -->
<div class="container-fluid principalContainer" id="modeYoutubePrincipal" style="overflow: hidden;">
    <?php
    require "{$global['systemRootPath']}view/modeYoutubeBundle.php";
    ?>
</div>
<?php
showCloseButton();
?>
<script>
    $(function() {
        <?php
        if (!empty($_REQUEST['404ErrorMsg'])) {
            echo "avideoAlertInfo(\"{$_REQUEST['404ErrorMsg']}\");";
        }
        ?>
    });
</script>
<?php
$_page->print();
?>
