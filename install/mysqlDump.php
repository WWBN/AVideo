<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();

$file = Video::getStoragePath().'mysqldump-'.date('YmdHis').'.sql';

passthru("mysqldump --opt -u '{$mysqlUser}' -p'{$mysqlPass}' -h {$mysqlHost} {$mysqlDatabase} > {$file}");

echo PHP_EOL."Dump file created at {$file}".PHP_EOL;
