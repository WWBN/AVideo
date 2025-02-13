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
echo "Enter the password:";
echo "\n";
ob_flush();
$userPass = trim(readline(""));

if (!empty($userName) && !empty($userPass)) {
    $user = new User(0, $userName, $userPass);
    if (!empty($user->getBdId())) {
        echo "User already exists {$userName} id=".$user->getBdId();
        echo "\n";
        die();
    } else{
        $user->setIsAdmin(1);
        $user->setEmail("{$userName}@{$userName}.com");
        $user->setName($userName);
        $user->setEmailVerified(1);
        $userId = $user->save();
        $sql = "UPDATE users SET isAdmin = 1, status = 'a' where id = {$userId}";

        $insert_row = sqlDAL::writeSql($sql);
        if ($insert_row) {
            echo "Your user {$userName} is admin now";
            echo "\n";
            die();
        }
    }
}
echo "Bye";
echo "\n";
die();
