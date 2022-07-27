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
    
    $users = User::getAllUsers(true);
    
    foreach ($users as $value) {
        $user = new User($value['id']);
        $addToUG = array($userGroup);
        $currentUG =UserGroups::getUserGroups($value['id']);
        if(!empty($currentUG)){
            foreach ($currentUG as $ug) {
                $addToUG[] = $ug["users_groups_id"];
            }
        }
        $user->setUserGroups($addToUG);
        if($user->save(true)){
            echo "Success: saved user [{$value['id']}] {$value['user']} :". json_encode($addToUG).PHP_EOL;
        }else{
            echo "**ERROR: saving user [{$value['id']}] {$value['user']} :". json_encode($addToUG).PHP_EOL;
        }
    }
}
echo "Bye";
echo "\n";
die();
