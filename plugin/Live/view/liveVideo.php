<?php
$_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
$poster = Live::getRegularPosterImage($livet['users_id'], $_REQUEST['live_servers_id'], @$_REQUEST['live_schedule'], @$_REQUEST['ppv_schedule_id']);
$posterURL = $global['webSiteRootURL'] . $poster . '?' . filectime($global['systemRootPath'] . $poster);
$playerSkinsObj = AVideoPlugin::getObjectData("PlayerSkins");
$isLive = 1;
?>
<!-- Live -->
<?php
$link = Live::getM3U8File($uuid);
$htmlMediaTag = '<video poster="' . $posterURL . '" controls '.PlayerSkins::getPlaysinline().'
                       class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered liveVideo vjs-16-9"
                       id="mainVideo">
                    <source src="' . $link . '" type="application/x-mpegURL">
                </video>';

$htmlMediaTag .= getLiveUsersLabelHTML();
echo PlayerSkins::getMediaTag(false, $htmlMediaTag);
//include $global['systemRootPath'] . 'plugin/PlayerSkins/buffering.debug.php';
?>
<!-- Live finish -->
