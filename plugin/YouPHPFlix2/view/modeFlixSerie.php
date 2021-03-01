<?php
global $global, $config;

$playlists_id = intval(@$_REQUEST['playlists_id']);

if (empty($playlists_id)) {
    die('Playlist ID is empty');
}

if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
session_write_close();

$video = Video::getVideoFromSeriePlayListsId($playlists_id);
if (empty($video)) {
    die('Video from playlist is empty ' . $playlists_id);
}

if (empty($_REQUEST['uid'])) {
    $uid = uniqid();
} else {
    $uid = preg_replace('/[^a-z0-0_]/i', '', $_REQUEST['uid']);
}
/*
 * If enable the cache the javascript fails due the uuid
  $uid = '{serie_uid}';

  $cacheName = "modeFlixSerie" . md5(json_encode($_GET)) . User::getId();
  $cache = ObjectYPT::getCache($cacheName, 600);
  if (!empty($cache)) {
  echo str_replace('{serie_uid}', uniqid(), $cache);
  return false;
  }
  ob_start();
 * 
 */
$obj = AVideoPlugin::getObjectData("YouPHPFlix2");

//if ($obj->PlayList) {
    $dataFlickirty = new stdClass();
    $dataFlickirty->wrapAround = true;
    $dataFlickirty->pageDots = !empty($obj->pageDots);
    $dataFlickirty->lazyLoad = true;
    $dataFlickirty->fade = true;
    $dataFlickirty->setGallerySize = false;
    $dataFlickirty->cellAlign = 'left';
    $dataFlickirty->groupCells = true;
    if ($obj->PlayListAutoPlay) {
        $dataFlickirty->autoPlay = 10000;
        $dataFlickirty->wrapAround = true;
    } else {
        $dataFlickirty->wrapAround = true;
    }
    $videos = PlayList::getAllFromPlaylistsID($playlists_id);
    $uidFlickirty = uniqid();
    ?>
    <div class="row topicRow" id="<?php echo $uidFlickirty; ?>-Flickirty">
        <!-- Serie -->
        <?php
        $rowPlayListLink = PlayLists::getLink($playlists_id);
        $rowPlayListLinkEmbed = PlayLists::getLink($playlists_id, true);
        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
        unset($rowPlayListLink);
        unset($rowPlayListLinkEmbed);
        ?>
    </div>
    <script>
        startModeFlix('#<?php echo $uidFlickirty; ?>-Flickirty ');
    </script>
    <?php
    $rowlink = false;
    $rowlinkEmbed = false;
//}
/*
  $cache = ob_get_clean();

  ObjectYPT::setCache($cacheName, $cache);

  echo str_replace('{serie_uid}', uniqid(), $cache);
 * 
 */
?>