<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
header('Content-Type: application/mpegurl');

$_REQUEST['site'] = get_domain($global['webSiteRootURL']);
$json = PlayList::getEPG();
echo "#EXTM3U refresh=\"60\"".PHP_EOL;
foreach ($json->sites as $key => $value) {
    if ($key == $_REQUEST['site']) {
        $site = $value;
        foreach ($site->channels as $users_id => $channel) {
            $identification = User::getNameIdentificationById($users_id);
            foreach ($channel->playlists as $playlist) {
                if(!PlayLists::showOnTV($playlist->playlists_id)){
                    continue;
                }
                $pl = new PlayList($playlist->playlists_id);
                $link = PlayLists::getLinkToM3U8($playlist->playlists_id, $playlist->key, $playlist->live_servers_id);
                $u = new User($pl->getId());
                $groupTitle = str_replace('"', "", $u->getChannelName());
                $title = str_replace('"', "", PlayLists::getNameOrSerieTitle($playlist->playlists_id));
                $image = PlayLists::getImage($playlist->playlists_id);
                echo '#EXTINF:-1 tvg-id="'."{$playlist->playlists_id}.{$users_id}.{$_REQUEST['site']}".'" tvg-logo="'.$image.'" group-title="'.$groupTitle.'", '.$title.PHP_EOL;
                echo $link.PHP_EOL;
            }
        }
    }
}
?>