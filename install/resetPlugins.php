<?php

//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

echo "1 - Reset all plugins Parameters\n";
echo "2 - Reset CustomizeUser Plugin Parameters only\n";
echo "3 - Reset all plugins (Will also inactivate the plugins)\n";
echo "Choose an option: ";
ob_flush();
$option = trim(readline(""));

exec("rm -R ".Video::getStoragePath()."cache/*");
if ($option == 1) {
    $sql = "UPDATE plugins ";
    $sql .= " SET object_data = '' WHERE id > 0";
    sqlDAL::writeSql($sql);
    echo "* Reset all plugins Parameters DONE\n";
    ob_flush();
} else if ($option == 2) {
    $sql = "UPDATE plugins ";
    $sql .= " SET object_data = '' WHERE name = 'CustomizeUser'";
    sqlDAL::writeSql($sql);
    echo "* Reset CustomizeUser Plugin Parameters only DONE\n";
    ob_flush();
} else if ($option == 3) {
    $sql = "DELETE FROM plugins ";
    $sql .= " WHERE id > 0";
    sqlDAL::writeSql($sql);
    echo "* Reset all plugins (All plugins inactivated) DONE\n";
    ob_flush();
}else{
    echo "Bye\n";
}