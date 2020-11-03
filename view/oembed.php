<?php
header('Access-Control-Allow-Credentials: true');
// force not to cache
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require_once '../videos/configuration.php';

if(empty($_REQUEST['url'])){
    forbiddenPage("URL not defined");
}

if(!isSameDomain($global['webSiteRootURL'], $_REQUEST['url'])){
    forbiddenPage("It is not from the same domain");
}
$url = parse_url($_REQUEST['url']);
parse_str($url["query"], $vars);

if(empty($vars["v"])){
    forbiddenPage("Video variable not found");
}

$videos_id = intval($vars["v"]);

$video = Video::getVideo($videos_id);

if(empty($video)){
    forbiddenPage("Video not found");
}

$format = 'json';

if(empty($_REQUEST['format']) || $_REQUEST['format']=='xml'){
    $format = 'xml';
}

$videos_id = intval($video['id']);
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
$title = html2plainText($video['title']);
$siteTitle = html2plainText($config->getWebSiteTitle());
$description = html2plainText($video['description']);
$link = Video::getLinkToVideo($videos_id);
$embedURL = Video::getLinkToVideo($videos_id, $video['clean_title'], true);
$duration = Video::getItemDurationSeconds($video['duration']);
$code = str_replace("{embedURL}", $embedURL, $advancedCustom->embedCodeTemplate);

if ($format === 'xml') {
    header('Content-type: application/xml');
    ?><?xml version="1.0" encoding="UTF-8"?>    
<oembed>
  <version>1.0</version>
  <type>rich</type>
  <width><?php echo $imgw; ?></width>
  <height><?php echo $imgh; ?></height>
  <title><?php echo $title; ?></title>
  <url><?php echo $link; ?></url>
  <provider_name><?php echo $siteTitle; ?></provider_name>
  <provider_url><?php echo $global['webSiteRootURL']; ?></provider_url>
  <html><?php echo htmlentities($code); ?></html>
</oembed>
    <?php
} else {
    header('Content-Type: application/json');
    $obj=new stdClass();
    $obj->version = 1.0;
    $obj->type = "rich";
    $obj->width = $imgw;
    $obj->height = $imgh;
    $obj->title = $title;
    $obj->url = $link;
    $obj->provider_name = $siteTitle;
    $obj->provider_url = $global['webSiteRootURL'];
    $obj->html = $code;
    die(json_encode($obj));
    
}


?>