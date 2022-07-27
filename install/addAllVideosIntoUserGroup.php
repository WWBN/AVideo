<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$rows = UserGroups::getAllUsersGroupsArray();

if(empty($rows)){
    die('You do not have any user group');
}

foreach ($rows as $key => $value) {
    echo "[$key] {$value}".PHP_EOL;
}
$_REQUEST['rowCount'] = 999999;

echo "Enter the user group number or press enter to skip:".PHP_EOL;
ob_flush();
$userGroup = trim(readline(""));

if(empty($rows[$userGroup])){
    die('This user group does not exists');
}

if (!empty($userGroup)) {
    
    $videos = Video::getAllVideosLight('');
    
    foreach ($videos as $value) {
        if(UserGroups::updateVideoGroups($value['id'], $userGroup, true)){
            echo "Success: saved video [{$value['id']}] {$value['title']} :". json_encode($addToUG).PHP_EOL;
        }else{
            echo "**ERROR: saving video [{$value['id']}] {$value['title']} :". json_encode($addToUG).PHP_EOL;
        }
    }
}
echo "Bye";
echo "\n";
die();
