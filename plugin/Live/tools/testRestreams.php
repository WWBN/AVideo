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
    $bitrates = [];
    $speeds = [];
    $sizes = [];
    $frames = [];

    // Usa expressões regulares para extrair as informações necessárias
    preg_match_all('/time=([\d:.]+)/', $fileContent, $timeMatches);
    preg_match_all('/bitrate=([\d.]+kbits\/s)/', $fileContent, $bitrateMatches);
    preg_match_all('/speed=([\d.]+x)/', $fileContent, $speedMatches);
    preg_match_all('/Lsize=\s*([\d.]+kB)/', $fileContent, $sizeMatches);
    preg_match_all('/frame=\s*(\d+)/', $fileContent, $frameMatches);

    if (!empty($timeMatches[1])) {
        $duration = end($timeMatches[1]);
    }
    if (!empty($bitrateMatches[1])) {
        $bitrates = array_map(function($v) { return (float) str_replace('kbits/s', '', $v); }, $bitrateMatches[1]);
    }
    if (!empty($speedMatches[1])) {
        $speeds = array_map(function($v) { return (float) str_replace('x', '', $v); }, $speedMatches[1]);
    }
    if (!empty($sizeMatches[1])) {
        $sizes = array_map(function($v) { return (float) str_replace('kB', '', $v); }, $sizeMatches[1]);
    }
    if (!empty($frameMatches[1])) {
        $frames = array_map('intval', $frameMatches[1]);
    }

    // Calcula as médias
    $averageBitrate = !empty($bitrates) ? array_sum($bitrates) / count($bitrates) : 0;
    $averageSpeed = !empty($speeds) ? array_sum($speeds) / count($speeds) : 0;
    $averageSize = !empty($sizes) ? array_sum($sizes) / count($sizes) : 0;
    $averageFrames = !empty($frames) ? array_sum($frames) / count($frames) : 0;

    // Cria um array com as informações extraídas
    $info = [
        "duration" => $duration,
        "average_bitrate" => number_format($averageBitrate, 2) . 'kbits/s',
        "average_speed" => number_format($averageSpeed, 2) . 'x',
        "average_size" => humanFileSize($averageSize),
        "average_frames" => number_format($averageFrames, 2)
    ];

    // Verifica se todas as informações foram extraídas com sucesso
    if (empty($duration) && empty($averageBitrate) && empty($averageSpeed) && empty($averageSize) && empty($averageFrames)) {
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
    echo "selectedFile: {$selectedFile}\n";
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
