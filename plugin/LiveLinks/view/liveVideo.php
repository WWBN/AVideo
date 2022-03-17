<?php
if ($t['id'] > 0) {
    $liveLink = LiveLinks::getSourceLink($t['id']);
} else {
    $liveLink = $t['link'];
}
$posterURL = LiveLinks::getImage($t['id']);

$disableYoutubeIntegration = false;
if (!empty($advancedCustom->disableYoutubePlayerIntegration) || isMobile()) {
    $disableYoutubeIntegration = true;
}
$video['videoLink'] = $liveLink;
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
    if (($disableYoutubeIntegration) || ((strpos($liveLink, "youtu.be") == false) && (strpos($liveLink, "youtube.com") == false) && (strpos($liveLink, "vimeo.com") == false))) {
        $_GET['isEmbedded'] = "e";
        $isVideoTypeEmbed = 1;
        $url = parseVideos($liveLink);
        if ($config->getAutoplay()) {
            $url = addQueryStringParameter($url, 'autoplay', 1);
        }
        $htmlMediaTag = "<!-- Embed liveLink {$liveLink} -->";
        $htmlMediaTag .= '<video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" style="display: none; height: 0;width: 0;" ></video>';
        $htmlMediaTag .= '<div id="main-video" class="embed-responsive-item">';
        $htmlMediaTag .= '<iframe class="embed-responsive-item" scrolling="no" allowfullscreen="true" src="' . $url . '"></iframe>';
        $htmlMediaTag .= '<script>$(document).ready(function () {addView(' . $video['id'] . ', 0);});</script>';
        $htmlMediaTag .= '</div>';
    } else {
        // youtube!
        if ((stripos($liveLink, "youtube.com") != false) || (stripos($liveLink, "youtu.be") != false)) {
            $_GET['isEmbedded'] = "y";
        } else if ((stripos($liveLink, "vimeo.com") != false)) {
            $_GET['isEmbedded'] = "v";
        }
        //$_GET['isMediaPlaySite'] = $video['id'];
        //PlayerSkins::playerJSCodeOnLoad($video['id'], @$video['url']);
        //PlayerSkins::getStartPlayerJS('');
        $htmlMediaTag = "<!-- Embed liveLink YoutubeIntegration {$liveLink} -->";
        $htmlMediaTag .= '<video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" class="embed-responsive-item video-js vjs-default-skin vjs-16-9 vjs-big-play-centered" controls></video>';
        $htmlMediaTag .= '<script>var player;$(document).ready(function () {$(".vjs-control-bar").css("opacity: 1; visibility: visible;");});</script>';
    }

    /*
      $htmlMediaTag .= '<video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" style="display: none; height: 0;width: 0;" ></video>';
      $htmlMediaTag .= '<div id="main-video" class="embed-responsive-item">';
      $htmlMediaTag .= '<iframe class="embed-responsive-item" scrolling="no" allowfullscreen="true" src="' . $url . '"></iframe>';
      $htmlMediaTag .= '</div>';
     * 
     */
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