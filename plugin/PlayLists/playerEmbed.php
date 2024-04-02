<?php
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/PlayListElement.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';

if (!PlayList::canSee($_GET['playlists_id'], User::getId())) {
    forbiddenPage(_('You cannot see this playlist') . ' ' . basename(__FILE__));
}
$global['doNotLoadPlayer'] = 1;
/*
$video = PlayLists::isPlayListASerie($_GET['playlists_id']);
if (!empty($video)) {
    $video = Video::getVideo($video['id']);
    include $global['systemRootPath'] . 'view/modeYoutube.php';
    exit;
}
 * 
 */

$playListObj = new PlayList($_GET['playlists_id']);

$playList = PlayList::getVideosFromPlaylist($_GET['playlists_id']);

foreach ($playList as $playlist_index => $value) {
    $playList[$playlist_index]['alternativeLink'] = PlayLists::getLink($_GET['playlists_id'], 1, $playlist_index);
}

$video = PlayLists::isPlayListASerie($_GET['playlists_id']);

$playlist_index = intval(@$_REQUEST['playlist_index']);

if (!empty($video['id'])) {
    AVideoPlugin::getEmbed($video['id']);
    setVideos_id($video['id']);
} else if (!empty($playListData[$playlist_index])) {
    setVideos_id($playListData[$playlist_index]->getVideos_id());
    $video = Video::getVideo($playListData[$playlist_index]->getVideos_id());
}

if (empty($playList)) {
    videoNotFound('');
}

$videos = array();
foreach ($playList as $key => $value) {
    $videos[$key] = $value;
    $videos[$key]['id'] = $value['videos_id'];
}
$_page = new Page(array($playListObj->getName()));
$_page->setExtraStyles(array('view/css/social.css', 'plugin/Gallery/style.css'));
$_page->setExtraScripts(array('plugin/Gallery/script.js'));
?>
<style>
    .clearfix {
        margin-bottom: 10px;
    }
</style>
<div class="container-fluid " style="overflow: hidden;">
    <div class="gallery">
        <?php
        if (!empty($playList)) {
            if (isMobile()) {
                createGallerySection($videos, true, true, 6, 4, 2, 1, false);
            } else {
                createGallerySection($videos, true, true, 6, 6, 4, 2, false);
            }
        } ?>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.galleryVideo a').click(function(event) {
            event.preventDefault();
            //avideoModalIframeFull($(this).attr('alternativeLink'));
            var url = $(this).attr('embed');
            if (empty(url)) {
                console.log('$(\'.galleryVideo a\').click does not have embed');
                url = $(this).attr('href');
            }
            url = addGetParam(url, 'controls', -1);
            url = addGetParam(url, 'showinfo', 0);
            url = addGetParam(url, 'autoplay', 1);
            console.log('$(\'.galleryVideo a\').click open', url);
            avideoAddIframeIntoElement(this, url, '');
        });
    });
</script>
<?php
$_page->print();
?>