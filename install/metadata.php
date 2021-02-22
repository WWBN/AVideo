<?php


$global['systemRootPath'] = dirname(dirname(__FILE__)) . '/';

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/videoMetadata.php';

/**
 * @brief return true if running in CLI, false otherwise
 * if is set $_GET['ignoreCommandLineInterface'] will return false
 * @return boolean
 */

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$videos = Video::getAllVideosLight(NULL);
foreach ($videos as $v) {
    VideoMetadata::importMetadataFromVideo($v['id']);
}

?>
