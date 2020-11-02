<?php
echo "<!-- OpenGraph -->";
if (empty($videos_id)) {
    echo "<!-- OpenGraph no video id -->";
    if (!empty($_GET['videoName'])) {
        echo "<!-- OpenGraph videoName {$_GET['videoName']} -->";
        $video = Video::getVideoFromCleanTitle($_GET['videoName']);
    }
} else {
    echo "<!-- OpenGraph videos_id {$videos_id} -->";
    $video = Video::getVideoLight($videos_id);
}
if (empty($video)) {
    echo "<!-- OpenGraph no video -->";
    return false;
}
$videos_id = $video['id'];
$source = Video::getSourceFile($video['filename']);
$imgw = 1024;
$imgh = 768;
if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
    $img = $source['url'];
    $data = getimgsize($source['path']);
    $imgw = $data[0];
    $imgh = $data[1];
} else if ($video['type'] == "audio") {
    $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
}
$type = 'video';
if ($video['type'] === 'pdf') {
    $type = 'pdf';
}
if ($video['type'] === 'article') {
    $type = 'article';
}
$images = Video::getImageFromFilename($video['filename'], $type);
if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
    $img = $images->posterPortrait;
    $data = getimgsize($images->posterPortraitPath);
    $imgw = $data[0];
    $imgh = $data[1];
} else {
    $img = $images->poster;
}
$twitter_site = $advancedCustom->twitter_site;
?>
<link rel="image_src" href="<?php echo $img; ?>" />
<meta property="og:image" content="<?php echo $img; ?>" />
<meta property="og:image:secure_url" content="<?php echo $img; ?>" />
<meta property="og:image:type" content="image/jpeg" />
<meta property="og:image:width"        content="<?php echo $imgw; ?>" />
<meta property="og:image:height"       content="<?php echo $imgh; ?>" />

<meta property="fb:app_id"             content="774958212660408" />
<meta property="og:title"              content="<?php echo html2plainText($video['title']); ?>" />
<meta property="og:description"        content="<?php echo html2plainText($video['description']); ?>" />
<meta property="og:url"                content="<?php echo Video::getLinkToVideo($videos_id); ?>" />
<meta property="og:type"               content="video.other" />

<?php
$sourceMP4 = Video::getSourceFile($video['filename'], ".mp4");
if (!AVideoPlugin::isEnabledByName("SecureVideosDirectory") && !empty($sourceMP4['url'])) {
    ?>
    <meta property="og:video" content="<?php echo $sourceMP4['url']; ?>" />
    <meta property="og:video:secure_url" content="<?php echo $sourceMP4['url']; ?>" />
    <meta property="og:video:type" content="video/mp4" />
    <meta property="og:video:width" content="<?php echo $imgw; ?>" />
    <meta property="og:video:height" content="<?php echo $imgh; ?>" />
    <?php
} else {
    ?>
    <meta property="og:video" content="<?php echo Video::getLinkToVideo($videos_id); ?>" />
    <meta property="og:video:secure_url" content="<?php echo Video::getLinkToVideo($videos_id); ?>" />
    <?php
}
?>
<meta property="video:duration" content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />
<meta property="duration" content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />

<!-- Twitter cards -->
<?php
if (!empty($advancedCustom->twitter_player)) {
    ?>
    <meta name="twitter:card" content="player" />
    <meta name="twitter:player" content="<?php echo Video::getLinkToVideo($videos_id, $video['clean_title'], true); ?>" />
    <meta name="twitter:player:width" content="480" />
    <meta name="twitter:player:height" content="480" />    
    <?php
} else {
    if (!empty($advancedCustom->twitter_summary_large_image)) {
        ?>
        <meta name="twitter:card" content="summary_large_image" />   
        <?php
    } else {
        ?>
        <meta name="twitter:card" content="summary" />   
        <?php
    }
}
?>
<meta name="twitter:site" content="<?php echo $twitter_site; ?>" />
<meta name="twitter:url" content="<?php echo Video::getLinkToVideo($videos_id); ?>"/>
<meta name="twitter:title" content="<?php echo html2plainText($video['title']); ?>"/>
<meta name="twitter:description" content="<?php echo html2plainText($video['description']); ?>"/>
<meta name="twitter:image" content="<?php echo $img; ?>"/>
<?php
