<?php
$_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
$poster = Live::getPosterImage($livet['users_id'], $_REQUEST['live_servers_id'], @$_REQUEST['live_schedule']);
$posterURL = $global['webSiteRootURL'] . $poster . '?' . filectime($global['systemRootPath'] . $poster);
$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");
$isLive = 1;
?>
<!-- Live -->
<?php
$htmlMediaTag = '<video poster="' . $posterURL . '" controls '.PlayerSkins::getPlaysinline().' 
                       class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered liveVideo vjs-16-9" 
                       id="mainVideo">
                    <source src="' . Live::getM3U8File($uuid) . '" type="application/x-mpegURL">
                </video>';
if(!empty($_REQUEST['debugLive'])){
    $live_servers_id = Live::getLiveServersIdRequest();
    var_dump(__LINE__, $live_servers_id, $_REQUEST['live_servers_id']);
    if (empty($_REQUEST['live_servers_id'])) {
        $url = '';
        if (!empty($_POST['tcurl'])) {
            $url = $_POST['tcurl'];
        }
        if (empty($url)) {
            $url = @$_POST['swfurl'];
        }
        if (!empty($url)) {
            var_dump(__LINE__, Live_servers::getServerIdFromRTMPHost($url));
        }
    }
}

$htmlMediaTag .= getLiveUsersLabelHTML();
echo PlayerSkins::getMediaTag(false, $htmlMediaTag);
?>
<!-- Live finish -->
