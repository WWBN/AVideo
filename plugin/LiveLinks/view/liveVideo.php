<?php
$liveLink = LiveLinks::getSourceLink($t['id']);
$posterURL = LiveLinks::getImage($t['id']);
?>
<link href="<?php echo getCDN(); ?>plugin/Live/view/live.css" rel="stylesheet" type="text/css"/>

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