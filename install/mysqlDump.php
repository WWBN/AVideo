<?php
//streamer config
require_once '../videos/configuration.php';

if (php_sapi_name() !== 'cli') {
    return die('Command Line only');
}

ob_end_flush();

$prefix = 'mysqldump';
if(!empty($restore)){
    $prefix = 'mysqldumpBackup';
}

$file = Video::getStoragePath().$prefix.'-'.date('YmdHis').'.sql';
$excludeTables = ['CachesInDB', 'audit'];  // tables to exclude from the dump

// Create a connection to the database to retrieve all table names
$connection = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$res = sqlDAL::readSql("SHOW TABLES");
$row = sqlDAL::fetchAllAssoc($res);
foreach ($row as $value) {    
    $firstElement = reset($value);
    if (!in_array($firstElement, $excludeTables)) {
        $tables[] = $firstElement;
    }
}

if(empty($mysqlPort)){
    $mysqlPort = 3306;
}

// Use the mysqldump command to get the database dump
$dumpCommand = "mysqldump --host=$mysqlHost --port=$mysqlPort --user=$mysqlUser --password=$mysqlPass "
             . "--default-character-set=utf8mb4 $mysqlDatabase $tableList > {$file}";

// Execute the command
system($dumpCommand, $output);

// Check the result
if ($output !== 0) {
    die("Error occurred while taking the database dump.");
}

echo "Database dumped successfully to {$file}".PHP_EOL;

?>
