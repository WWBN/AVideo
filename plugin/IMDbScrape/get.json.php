<?php

header('Content-Type: application/json');
require_once '../../videos/configuration.php';
//require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';
require_once $global['systemRootPath'] . 'plugin/IMDbScrape/imdb.class.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$plugin = YouPHPTubePlugin::getObjectData("IMDbScrape");

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isAdmin() && !Video::canEdit($_GET['videos_id'])) {
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$video = new Video('', '', $_GET['videos_id']);

$oIMDB = new IMDB($video->getTitle());
if ($oIMDB->isReady) {
    $videoFileName = $video->getFilename();

    if (empty($_GET['what']) || $_GET['what'] == 1) {
        // get poster
        $poster = $oIMDB->getPoster('big', true);
        $filename = "{$global['systemRootPath']}videos/{$videoFileName}_portrait.jpg";
        im_resizeV2($poster, $filename, $plugin->posterWidth, $plugin->posterHeight);
    }

    if (empty($_GET['what']) || $_GET['what'] == 2) {
        // get description
        $description = $oIMDB->getDescription();
        $video->setDescription($description);
    }

    if (empty($_GET['what']) || $_GET['what'] == 3) {
        // get rate
        $rate = $oIMDB->getRating();
        $video->setRate($rate);
    }

    if (empty($_GET['what']) || $_GET['what'] == 4) {
        $encodeTrailerInWebm = intval($plugin->encodeTrailerInWebm);
        // trailer
        $trailer = $oIMDB->getTrailerAsUrl(true);
        $encoderURL = $config->getEncoderURL() . "youtubeDl.json?webm={$encodeTrailerInWebm}&videoURL=" . urlencode($trailer) . "&webSiteRootURL=" . urlencode($global['webSiteRootURL']) . "&user=" . urlencode(User::getUserName()) . "&pass=" . urlencode(User::getUserPass());
        error_log("IMDB encoder URL {$encoderURL}");
        $json = url_get_contents($encoderURL);
        error_log("IMDB encoder answer {$json}");
        $json = json_decode($json);
        if (!empty($json->videos_id)) {
            $trailerVideo = new Video('', '', $json->videos_id);
            $trailerVideo->setStatus('u');
            $video->setTrailer1(Video::getPermaLink($json->videos_id, true));
        }
    }
    $video->save();

    $obj->error = false;
} else {
    $obj->msg = "Movie not found";
}

echo json_encode($obj);
