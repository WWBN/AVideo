<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();

$glob = glob(Video::getStoragePath().'mysqldump-*.sql');
foreach($glob as $key => $file) {
    echo "($key) {$file}".PHP_EOL;
}

echo "Type the number of what file you want to restore or just press enter to get the latest".PHP_EOL;
ob_flush();
$option = trim(readline(""));

if($option===''){
    $filename = end($glob); ;
}else{
    $option = intval($option);
    $filename = $glob[$option];
}

echo 'We will make a backup first ...'.PHP_EOL;

$file = Video::getStoragePath().'mysqlBackupBeforeRestore-'.date('YmdHis').'.sql';
passthru("mysqldump --opt -u '{$mysqlUser}' -p'{$mysqlPass}' -h {$mysqlHost} {$mysqlDatabase} > {$file}");
echo PHP_EOL."Backup file created at {$file}".PHP_EOL;

sqlDAL::executeFile($filename);