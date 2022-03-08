<?php
if ($t['id'] > 0) {
    $liveLink = LiveLinks::getSourceLink($t['id']);
} else {
    $liveLink = $t['link'];
}
$posterURL = LiveLinks::getImage($t['id']);
?>
<link href="<?php echo getCDN(); ?>plugin/Live/view/live.css" rel="stylesheet" type="text/css"/>

<!-- Live Link -->
<?php
if (isValidM3U8Link($liveLink)) {

    $htmlMediaTag = '<video poster="' . $posterURL . '" controls playsinline webkit-playsinline="webkit-playsinline" 
                       class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered liveVideo vjs-16-9" 
                       id="mainVideo">
                    <source src="' . $liveLink . '" type="application/x-mpegURL">
                </video>';
} else {

    $isVideoTypeEmbed = 1;
    $url = parseVideos($liveLink);
    if ($config->getAutoplay()) {
        $url = addQueryStringParameter($url, 'autoplay', 1);
    }
    $htmlMediaTag = "<!-- Embed liveLink {$liveLink} -->";
    $htmlMediaTag .= '<video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" style="display: none; height: 0;width: 0;" ></video>';
    $htmlMediaTag .= '<div id="main-video" class="embed-responsive-item">';
    $htmlMediaTag .= '<iframe class="embed-responsive-item" scrolling="no" allowfullscreen="true" src="' . $url . '"></iframe>';
    $htmlMediaTag .= '</div>';
}


$htmlMediaTag .= getLiveUsersLabelHTML();

echo PlayerSkins::getMediaTag(false, $htmlMediaTag);
?>
<!-- Live link finish -->
<script>
<?php
echo PlayerSkins::getStartPlayerJS();
?>
</script>