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

    // Lista para armazenar comandos SQL
    $commands = [];
    // Lista para armazenar comandos de criação de tabelas
    $createTableCommands = [];
    // Lista para armazenar todas as tabelas identificadas
    $tables = [];

    // Separar comandos SQL e identificar tabelas
    foreach ($lines as $line) {
        // Pular se for um comentário ou linha vazia
        if (substr($line, 0, 2) == '--' || trim($line) == '')
            continue;

        // Adicionar esta linha ao segmento atual
        $templine .= $line;
        // Se tiver um ponto e vírgula no final, é o final da consulta
        if (substr(trim($line), -1) == ';') {
            $commands[] = $templine;
            if (stripos($templine, 'CREATE TABLE') !== false) {
                $createTableCommands[] = $templine;
                $tableName = preg_split('/[\s`]+/', $templine)[2]; // Extrair o nome da tabela
                $tables[] = $tableName;
            }
            // Resetar a variável temporária para vazia
            $templine = '';
        }
    }

    // Executar DROP TABLE IF EXISTS separado de CREATE TABLE
    foreach ($tables as $table) {
        $dropTableCommand = 'DROP TABLE IF EXISTS `' . $table . '`;';
        echo "Executing: $dropTableCommand\n"; // Imprimir o comando SQL
        try {
            if (!$global['mysqli']->query($dropTableCommand)) {
                echo ('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $dropTableCommand . '\': ' . $global['mysqli']->error . '<br /><br />');
            }
        } catch (\Throwable $th) {
           echo 'Error: '.$th->getMessage().PHP_EOL;
        }
    }

    // Executar comandos de criação de tabela
    foreach ($createTableCommands as $command) {
        echo "Executing: $command\n"; // Imprimir o comando SQL
        if (!$global['mysqli']->query($command)) {
            echo ('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $command . '\': ' . $global['mysqli']->error . '<br /><br />');
        }
    }

    // Adicionar LOCK TABLES para todas as tabelas identificadas
    if (!empty($tables)) {
        $lockTables = 'LOCK TABLES ' . implode(' WRITE, ', $tables) . ' WRITE;';
        echo "Executing: $lockTables\n"; // Imprimir o comando SQL
        if (!$global['mysqli']->query($lockTables)) {
            echo ('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $lockTables . '\': ' . $global['mysqli']->error . '<br /><br />');
            return;
        }
    }

    // Executar todos os outros comandos SQL
    foreach ($commands as $command) {
        if (!in_array($command, $createTableCommands)) {
            echo "Executing: $command\n"; // Imprimir o comando SQL
            if (!$global['mysqli']->query($command)) {
                echo ('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $command . '\': ' . $global['mysqli']->error . '<br /><br />');
            }
        }
    }

    // Desbloquear as tabelas no final
    $global['mysqli']->query('UNLOCK TABLES;');
}
