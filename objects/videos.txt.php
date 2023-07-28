<?php
error_reporting(0);
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-type: text/plain');
$showOnlyLoggedUserVideos = true;
if (Permissions::canModerateVideos()) {
    $showOnlyLoggedUserVideos = false;
}
if(!User::isLogged()){
    die('Need a login');
}
setRowCount(99999);
$videos = Video::getAllVideosLight('', $showOnlyLoggedUserVideos, false);
foreach ($videos as $key => $value) {
    if (empty($_GET['type'])) {
        echo Video::getPermaLink($videos[$key]['id']);
    } else {
        switch ($_GET['type']) {
            case 'csv':
                $data = array();

                foreach ($videos as $key => $value) {
                    $cat = new Category($value['categories_id']);
                    $us = new User($value['users_id']);
                    $data[] = array(
                        'id'=>$value['id'],
                        'Type'=>$value['type'],
                        'Channel Name'=>$us->getChannelName(),
                        'Title'=>$value['title'],
                        'Duration'=>$value['duration'],
                        'Views'=>$value['views_count'],
                        'Created'=>$value['created'],
                        'User id'=>$value['users_id'],
                        'Categories id'=>$value['categories_id'],
                        'Category'=>$cat->getName()
                    );
                }


                // Define the CSV file name
                $filename = User::getUserChannelName().'_videos.csv';

                // Send the headers for a file download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                // Open a file pointer to the output stream
                $fp = fopen('php://output', 'w');

                // get column names from first array keys
                fputcsv($fp, array_keys($data[0]));

                foreach ($data as $row) {
                    fputcsv($fp, $row);
                }

                fclose($fp);
                exit;
                break;
            
            default:// seo
                echo Video::getURLFriendlyFromCleanTitle($videos[$key]['clean_title']);
                break;
        }
    }
    echo PHP_EOL;
}
