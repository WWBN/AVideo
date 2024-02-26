<!-- Live Link <?php echo $t['id']; ?> -->
<?php
AVideoPlugin::loadPlugin('LiveLinks');
if ($t['id'] > 0) {
    $liveLink = LiveLinks::getSourceLink($t['id']);
    $liveLinkObj = new LiveLinksTable($t['id']);
    $endTime = strtotime(convertFromDefaultTimezoneTimeToMyTimezone($liveLinkObj->getEnd_date()));
    $endInSeconds = $endTime - time();
    if($endInSeconds<0){
        forbiddenPage('Live Finished');
    }
    if(!LiveLinks::userCanWatch(User::getId(), $t['id'])){
        forbiddenPage('Live is private');
    }
} else {
    $liveLink = $t['link'];
}
$posterURL = LiveLinks::getImage($t['id']);

$disableYoutubeIntegration = !PlayerSkins::isYoutubeIntegrationEnabled();
$video['videoLink'] = $liveLink;
if (isValidM3U8Link($liveLink)) {

    $htmlMediaTag = '<video poster="' . $posterURL . '" controls '.PlayerSkins::getPlaysinline().'
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
        $htmlMediaTag .= '<video '.PlayerSkins::getPlaysinline().' id="mainVideo" style="display: none; height: 0;width: 0;" ></video>';
        $htmlMediaTag .= '<div id="main-video" class="embed-responsive-item">';
        $htmlMediaTag .= '<iframe class="embed-responsive-item" scrolling="no" '.Video::$iframeAllowAttributes.' src="' . $url . '"></iframe>';
        $htmlMediaTag .= '<script>$(document).ready(function () {addView(' . intval($video['id']) . ', 0);});</script>';
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
        $htmlMediaTag .= '<video '.PlayerSkins::getPlaysinline().' id="mainVideo" class="embed-responsive-item video-js vjs-default-skin vjs-16-9 vjs-big-play-centered" controls></video>';
        $htmlMediaTag .= '<script>var player;$(document).ready(function () {$(".vjs-control-bar").css("opacity: 1; visibility: visible;");});</script>';
    }
}


$htmlMediaTag .= getLiveUsersLabelHTML();
if (!empty($_REQUEST['embed'])) {
    echo $htmlMediaTag;
} else {
    echo PlayerSkins::getMediaTag(false, $htmlMediaTag);
}
if (!empty($endInSeconds) && $endInSeconds < 604800) { //1 week
    ?>
    <script>
        $(document).ready(function () {
            var endInSeconds = <?php echo $endInSeconds; ?>;
            console.log('live will finish in', endInSeconds);
            setTimeout(function () {
                console.log('live finish now');
                $('#main-video').remove();
                avideoConfirm('Live Finished').then(function (value) {
                    document.location = webSiteRootURL;
                });
            }, endInSeconds*1000);
        });
    </script>
    <?php
}
?>
<!-- Live link finish -->