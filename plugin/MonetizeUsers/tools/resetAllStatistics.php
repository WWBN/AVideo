<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
$isEnabled = AVideoPlugin::isEnabledByName('MonetizeUsers');

if (empty($isEnabled)) {
    return die('Plugin disabled');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$tables = ['wallet_log', 'statistics', 'videos_statistics', 'monetize_user_reward_log'];

foreach ($tables as $table) {
    $sql = "TRUNCATE TABLE " . $table;

    if (sqlDAL::writeSql($sql)) {
        echo "Table " . $table . " truncated successfully.<br>".PHP_EOL;
    } else {
        echo "Error truncating table " . $table . ": " . $conn->error . "<br>".PHP_EOL;
    }
}

$sql = " UPDATE wallet SET balance = 0 WHERE id > 0";
if (sqlDAL::writeSql($sql)) {
    echo "Table wallet balance reset.<br>".PHP_EOL;
} else {
    echo "Error on Table wallet balance reset<br>".PHP_EOL;
}

$sql = " UPDATE wallet SET balance = 10000 WHERE users_id = 1";
if (sqlDAL::writeSql($sql)) {
    echo "Table wallet admin balance reset.<br>".PHP_EOL;
} else {
    echo "Error on Table wallet admin balance reset<br>".PHP_EOL;
}