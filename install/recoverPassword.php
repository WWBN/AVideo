<?php
//streamer config
require_once '../videos/configuration.php';

if(!isCommandLineInterface()){
    return die('Command Line only');
}
echo "Enter the username or press enter to skip:";
echo "\n";
ob_flush();
$userName = trim(readline(""));

if(!empty($userName)){
    $user = new User(0, $userName, false);
    if(!empty($user->getBdId())){
        echo "Enter a new password for the user {$userName} or press enter to skip:";
        echo "\n";
        ob_flush();
        $password = trim(readline(""));
        if(!empty($password)){
            echo "Confirm the new password for the user {$userName}:";
            echo "\n";
            ob_flush();
            $password2 = trim(readline(""));
            if($password===$password2){
                $user->setPassword($password);
                if($user->save()){
                    echo "Your new password was saved";
                    echo "\n";
                    die();
                }
            }else{
                echo "The passwords do not match";
                echo "\n";
                die();
            }
        }
    }else{
        echo "User ({$userName}) Not found";
        echo "\n";
        die();
    }
}
echo "Bye";
echo "\n";
die();




