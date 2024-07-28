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
//executeFile($filename);
executeFileUsingCommandLine($filename);

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

    // Modificar comandos CREATE TABLE para CREATE TABLE IF NOT EXISTS
    foreach ($createTableCommands as &$command) {
        if (stripos($command, 'CREATE TABLE') !== false && stripos($command, 'IF NOT EXISTS') === false) {
            $command = str_ireplace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $command);
        }
    }

    // Desativar a verificação de chaves estrangeiras
    try {
        $global['mysqli']->query('SET foreign_key_checks = 0;');
    } catch (Exception $e) {
        echo 'sqlDAL::executeFile ' . $filename . ' Error performing query \'SET foreign_key_checks = 0\': ' . $e->getMessage() . PHP_EOL;
        return;
    }

    // Executar DROP TABLE IF EXISTS separado de CREATE TABLE
    foreach ($tables as $table) {
        $dropTableCommand = 'DROP TABLE IF EXISTS `' . $table . '`;';
        try {
            if (!$global['mysqli']->query($dropTableCommand)) {
                throw new Exception($global['mysqli']->error);
            }
        } catch (Exception $e) {
            echo 'sqlDAL::executeFile ' . $filename . ' Error performing query \'' . $dropTableCommand . '\': ' . $e->getMessage() . PHP_EOL;
        }
    }

    // Executar comandos de criação de tabela
    foreach ($createTableCommands as $command) {
        try {
            if (!$global['mysqli']->query($command)) {
                throw new Exception($global['mysqli']->error);
            }
        } catch (Exception $e) {
            echo 'sqlDAL::executeFile ' . $filename . ' Error performing query \'' . $command . '\': ' . $e->getMessage() . PHP_EOL;
        }
    }

    // Adicionar LOCK TABLES para todas as tabelas identificadas
    if (!empty($tables)) {
        $lockTables = 'LOCK TABLES ' . implode(' WRITE, ', $tables) . ' WRITE;';
        try {
            if (!$global['mysqli']->query($lockTables)) {
                throw new Exception($global['mysqli']->error);
            }
        } catch (Exception $e) {
            echo 'sqlDAL::executeFile ' . $filename . ' Error performing query \'' . $lockTables . '\': ' . $e->getMessage() . PHP_EOL;
            return;
        }
    }

    // Executar todos os outros comandos SQL
    foreach ($commands as $command) {
        if (!in_array($command, $createTableCommands)) {
            try {
                if (!$global['mysqli']->query($command)) {
                    throw new Exception($global['mysqli']->error);
                }
            } catch (Exception $e) {
                echo 'sqlDAL::executeFile ' . $filename . ' Error performing query \'' . $command . '\': ' . $e->getMessage() . PHP_EOL;
            }
        }
    }

    // Reativar a verificação de chaves estrangeiras
    try {
        $global['mysqli']->query('SET foreign_key_checks = 1;');
    } catch (Exception $e) {
        echo 'sqlDAL::executeFile ' . $filename . ' Error performing query \'SET foreign_key_checks = 1\': ' . $e->getMessage() . PHP_EOL;
    }

    // Desbloquear as tabelas no final
    try {
        $global['mysqli']->query('UNLOCK TABLES;');
    } catch (Exception $e) {
        echo 'sqlDAL::executeFile ' . $filename . ' Error performing query \'UNLOCK TABLES\': ' . $e->getMessage() . PHP_EOL;
    }
}


function executeFileUsingCommandLine($filename) {
    global $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;

    $command = sprintf(
        'mysql --host=%s --user=%s --password=%s --port=%s %s < %s',
        escapeshellarg($mysqlHost),
        escapeshellarg($mysqlUser),
        escapeshellarg($mysqlPass),
        escapeshellarg($mysqlPort),
        escapeshellarg($mysqlDatabase),
        escapeshellarg($filename)
    );

    echo "Executing command..." . PHP_EOL;
    
    $output = [];
    $return_var = null;
    exec($command, $output, $return_var);
    
    if ($return_var !== 0) {
        echo "Error executing file using command line. Return code: $return_var" . PHP_EOL;
        echo implode(PHP_EOL, $output) . PHP_EOL;
    } else {
        echo "File executed successfully using command line." . PHP_EOL;
    }
}