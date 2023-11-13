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

echo "Do you want to remove instead of add the video into the usergroup? (yes/no)".PHP_EOL;
ob_flush();
$remove = trim(readline(""));

$remove = strtolower($remove) == 'yes';

foreach ($rows as $key => $value) {
    echo "[$key] {$value}".PHP_EOL;
}

$global['limitForUnlimitedVideos'] = $global['rowCount'] = $_REQUEST['rowCount'] = 999999;

echo "Enter the user group number or press enter to skip:".PHP_EOL;
ob_flush();
$userGroup = trim(readline(""));

if(empty($rows[$userGroup])){
    die('This user group does not exists');
}

if (!empty($userGroup)) {
    
    $videos = Video::getAllVideosLight('', false, true);
    mysqlBeginTransaction();
    foreach ($videos as $value) {
        if($remove){
            if(UserGroups::deleteVideoGroups($value['id'], $userGroup, false)){
                echo "Success: removed video [{$value['id']}] {$value['title']} :".PHP_EOL;
            }else{
                echo "**ERROR: removing video [{$value['id']}] {$value['title']} :".PHP_EOL;
            }
        }else{
            if(UserGroups::updateVideoGroups($value['id'], $userGroup, true)){
                echo "Success: saved video [{$value['id']}] {$value['title']} :".PHP_EOL;
            }else{
                echo "**ERROR: saving video [{$value['id']}] {$value['title']} :".PHP_EOL;
            }
        }
    }
    mysqlCommit();
}
clearCache();
echo "Bye";
echo "\n";
die();
