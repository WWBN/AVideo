<?php
$liveLink = "Invalid link";
if (filter_var($t['link'], FILTER_VALIDATE_URL)) {
    $url = parse_url($t['link']);
    if ($url['scheme'] == 'https') {
        $liveLink = $t['link'];
    } else {
        $liveLink = "{$global['webSiteRootURL']}plugin/LiveLinks/proxy.php?livelink=" . urlencode($t['link']);
    }
}
$posterURL = "{$global['webSiteRootURL']}plugin/Live/view/OnAir.jpg";
?>
<link href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/live.css" rel="stylesheet" type="text/css"/>

<!-- Live Link -->
<?php
$htmlMediaTag = '<video poster="' . $posterURL . '" controls playsinline webkit-playsinline="webkit-playsinline" 
                       class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered liveVideo vjs-16-9" 
                       id="mainVideo">
                    <source src="' . $liveLink . '" type="application/x-mpegURL">
                </video>';

$htmlMediaTag .= getLiveUsersLabelHTML();
echo PlayerSkins::getMediaTag(false, $htmlMediaTag);
?>
<!-- Live link finish -->
<script>
<?php
echo PlayerSkins::getStartPlayerJS();
?>
</script>