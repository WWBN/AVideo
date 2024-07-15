<?php
$obj = AVideoPlugin::getDataObject('CustomizeAdvanced');
$videoAB = new Video('', '', $videos_id);
$trailer = $videoAB->getTrailer1();
//var_dump($_REQUEST);exit;
if (!isValidURL($trailer)) {
    if (!empty($_REQUEST['playlist_id'])) {
        $trailer = PlayLists::getTrailerIfIsSerie($_REQUEST['playlist_id']);
    }
}
if (isValidURL($trailer)) {
    echo '<!-- invalid trailer URL -->';
?>
    <button type="button" class="btn btn-default no-outline" onclick="avideoModalIframe('<?php echo parseVideos($trailer, 1); ?>');" data-toggle="tooltip" title="<?php echo __("Trailer"); ?>">
        <i class="fa fa-video"></i> <?php echo __("Trailer"); ?>
    </button>
<?php
}

if($obj->allowDownloadMP3){
    $filename = $videoAB->getFilename();
    $paths = Video::getPaths($filename);
    $mp3File = "{$paths['path']}{$video['filename']}.mp3";
    if (file_exists($mp3File)) {
        $mp3URL = getVideosURLAudio($mp3File, true);
        //var_dump($mp3URL);
        $mp3URL['mp3']['url'] = addQueryStringParameter($mp3URL['mp3']['url'], 'download', 1);
        ?>
        <a href="<?php echo $mp3URL['mp3']['url']; ?>" class="btn btn-default no-outline" data-toggle="tooltip" title="<?php echo __("MP3"); ?>" target="_blank">
            <i class="fas fa-download"></i> <?php echo __("MP3"); ?>
        </a>
        <?php
    }else{
        CustomizeAdvanced::createMP3($videos_id);
        echo "<!-- allowDownloadMP3: there is no mp3 to download -->";
    }
}else{
    echo '<!-- allowDownloadMP3: mp3 not allowed to download  -->';
}
?>