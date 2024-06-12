<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

if(!isCommandLineInterface()){
    forbiddenPage('Command line only');
}

ob_end_flush();

function listLogFiles($directory) {
    $files = array_diff(scandir($directory), array('..', '.'));
    $logFiles = [];

    foreach ($files as $file) {
        if (strpos($file, 'ffmpeg_restreamer') !== false) {
            $logFiles[] = $file;
        }
    }

    // Ordena os arquivos por data de modificação
    usort($logFiles, function($a, $b) use ($directory) {
        return filemtime("$directory/$b") - filemtime("$directory/$a");
    });

    // Retorna os últimos 20 arquivos
    return array_slice($logFiles, 0, 20);
}

function parseFfmpegLog($filePath) {
    // Verifica se o arquivo existe
    if (!file_exists($filePath)) {
        return json_encode([
            "error" => true,
            "msg" => "File not found",
            "info" => null
        ]);
    }

    // Abre o arquivo para leitura
    $fileContent = file_get_contents($filePath);

    // Verifica se o conteúdo foi lido com sucesso
    if ($fileContent === false) {
        return json_encode([
            "error" => true,
            "msg" => "Failed to read the file",
            "info" => null
        ]);
    }

    // Inicializa as variáveis para armazenar as informações extraídas
    $duration = '';
    $bitrate = '';
    $speed = '';
    $size = '';
    $frames = '';

    // Usa expressões regulares para extrair as informações necessárias
    if (preg_match('/time=([\d:.]+)/', $fileContent, $matches)) {
        $duration = $matches[1];
    }
    if (preg_match('/bitrate=([\d.]+kbits\/s)/', $fileContent, $matches)) {
        $bitrate = $matches[1];
    }
    if (preg_match('/speed=([\d.]+x)/', $fileContent, $matches)) {
        $speed = $matches[1];
    }
    if (preg_match('/Lsize=\s*([\d.]+kB)/', $fileContent, $matches)) {
        $size = $matches[1];
    }
    if (preg_match('/frame=\s*(\d+)/', $fileContent, $matches)) {
        $frames = $matches[1];
    }

    // Cria um array com as informações extraídas
    $info = [
        "duration" => $duration,
        "bitrate" => $bitrate,
        "speed" => $speed,
        "size" => $size,
        "frames" => $frames
    ];

    // Verifica se todas as informações foram extraídas com sucesso
    if (empty($duration) && empty($bitrate) && empty($speed) && empty($size) && empty($frames)) {
        return json_encode([
            "error" => true,
            "msg" => "Failed to extract information from log",
            "info" => null
        ]);
    }

    // Retorna as informações em formato JSON
    return json_encode([
        "error" => false,
        "msg" => "Success",
        "info" => $info
    ], JSON_PRETTY_PRINT);
}

function getLogFileInfo($directory, $fileName) {
    $filePath = "$directory/$fileName";
    if (!file_exists($filePath)) {
        return [
            "error" => true,
            "msg" => "File not found",
            "info" => null
        ];
    }

    $createdTime = filectime($filePath);
    $modifiedTime = filemtime($filePath);

    return [
        "created" => date("Y-m-d H:i:s", $createdTime),
        "modified" => date("Y-m-d H:i:s", $modifiedTime)
    ];
}

// Exemplo de uso
$directory = '/var/www/tmp/';
echo "Start".PHP_EOL;
$logFiles = listLogFiles($directory);

echo "Últimos 20 logs disponíveis:\n";
foreach ($logFiles as $index => $file) {
    echo ($index + 1) . ": $file\n";
}

echo "Digite o número do arquivo que você deseja extrair informações: ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$selectedFileIndex = (int)trim($line) - 1;

if (isset($logFiles[$selectedFileIndex])) {
    $selectedFile = $logFiles[$selectedFileIndex];
    $fileInfo = getLogFileInfo($directory, $selectedFile);
    $logData = parseFfmpegLog("$directory/$selectedFile");

    echo "Informações do arquivo selecionado:\n";
    echo json_encode($fileInfo, JSON_PRETTY_PRINT) . "\n";
    echo "Dados do log:\n";
    echo $logData . "\n";
} else {
    echo "Seleção inválida.\n";
}

fclose($handle);

?>
