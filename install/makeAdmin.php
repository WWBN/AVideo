<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
echo "Enter the username or press enter to skip:";
echo "\n";
ob_flush();
$userName = trim(readline(""));

if (!empty($userName)) {
    $user = new User(0, $userName, false);
    if (!empty($user->getBdId())) {
        $sql = "UPDATE users SET isAdmin = 1, status = 'a' where id = ".$user->getBdId();

        $insert_row = sqlDAL::writeSql($sql);
        if ($insert_row) {
            echo "Your user {$userName} is admin now";
            echo "\n";
            die();
        }
    } else {
        echo "User ({$userName}) Not found";
        echo "\n";
        die();
    }
}
echo "Bye";
echo "\n";
die();
