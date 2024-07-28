<?php

//streamer config
$global['createDatabase'] = 1;
$doNotIncludeConfig = 1;
require_once __DIR__ . '/../videos/configuration.php';

if (php_sapi_name() !== 'cli') {
    return die('Command Line only');
}

ob_end_flush();

$globPattern = "{$global['systemRootPath']}videos/mysqldump-*.sql";
echo "Searching [{$globPattern}]" . PHP_EOL;
$glob = glob($globPattern);
foreach ($glob as $key => $file) {
    echo "($key) {$file} " . humanFileSize(filesize($file)) . PHP_EOL;
}

// Check for command line argument
if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == '-1') {
    $filename = end($glob);
} else {
    echo "Type the number of what file you want to restore or just press enter to get the latest" . PHP_EOL;
    $option = trim(readline(""));

    if ($option === '') {
        $filename = end($glob);
    } else {
        $option = intval($option);
        $filename = $glob[$option];
    }
}
/*
echo 'We will make a backup first ...' . PHP_EOL;
$restore = 1;

//include './mysqlDump.php';

echo PHP_EOL . "Backup file created at {$file}" . PHP_EOL;
*/

$global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, '', @$mysqlPort);
try {
    $createSQL = "DROP DATABASE IF EXISTS {$mysqlDatabase};";
    $global['mysqli']->query($createSQL);
} catch (\Throwable $th) {
    echo ($th->getMessage());
}
$createSQL = "CREATE DATABASE IF NOT EXISTS {$mysqlDatabase};";
echo $createSQL . PHP_EOL;
$global['mysqli']->query($createSQL);
$global['mysqli']->select_db($mysqlDatabase);

echo "Execute filename {$filename}" . PHP_EOL;
executeFile($filename);

function executeFile($filename) {
    global $global;
    $templine = '';
    // Read in entire file
    $lines = file($filename);

    // Lista para armazenar comandos de criação de tabela
    $createTableCommands = [];
    // Lista para armazenar todos os outros comandos SQL
    $otherCommands = [];

    // Separar os comandos de criação de tabela dos outros comandos
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || trim($line) == '')
            continue;

        // Add this line to the current segment
        $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1) == ';') {
            if (stripos($templine, 'CREATE TABLE') !== false) {
                // Extrair o nome da tabela
                $tableName = preg_split('/[\s`]+/', $templine)[2];
                // Adicionar o comando DROP TABLE IF EXISTS antes do CREATE TABLE
                $createTableCommands[] = 'DROP TABLE IF EXISTS `' . $tableName . '`;' . "\n" . $templine;
            } else {
                $otherCommands[] = $templine;
            }
            // Reset temp variable to empty
            $templine = '';
        }
    }

    // Executar comandos de criação de tabela com DROP TABLE IF EXISTS
    foreach ($createTableCommands as $command) {
        echo $command.PHP_EOL;
        if (!$global['mysqli']->query($command)) {
            echo ('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $command . '\': ' . $global['mysqli']->error . '<br /><br />');
        }
    }

    // Identificar todas as tabelas no arquivo SQL
    $tables = [];
    foreach ($createTableCommands as $command) {
        if (stripos($command, 'CREATE TABLE') !== false) {
            $tableName = preg_split('/[\s`]+/', $command)[4]; // Extrair o nome da tabela
            $tables[] = $tableName;
        }
    }

    // Adicionar LOCK TABLES para todas as tabelas identificadas
    if (!empty($tables)) {
        $lockTables = 'LOCK TABLES ' . implode(' WRITE, ', $tables) . ' WRITE;';
        if (!$global['mysqli']->query($lockTables)) {
            echo ('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $lockTables . '\': ' . $global['mysqli']->error . '<br /><br />');
            return;
        }
    }

    // Executar todos os outros comandos com tabelas bloqueadas
    foreach ($otherCommands as $command) {
        if (!$global['mysqli']->query($command)) {
            echo ('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $command . '\': ' . $global['mysqli']->error . '<br /><br />');
        }
    }

    // Desbloquear as tabelas no final
    $global['mysqli']->query('UNLOCK TABLES;');
}
