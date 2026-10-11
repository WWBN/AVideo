<?php

//streamer config

use SebastianBergmann\CodeCoverage\Report\PHP;

$global['createDatabase'] = 1;
$doNotIncludeConfig = 1;
try {
    require_once __DIR__ . '/../videos/configuration.php';
} catch (\Throwable $th) {
    echo ('Error loading configuration: ' . $th->getMessage()).PHP_EOL;
}

if (php_sapi_name() !== 'cli') {
    die('Command Line only');
}

ob_end_flush();


// Example usage
$globPattern = "{$global['systemRootPath']}videos/mysqldump-*.sql";
echo "Searching [{$globPattern}] and private backups for this installation" . PHP_EOL;
$glob = getMySQLDumpFilesForRestore();
if (empty($glob)) {
    echo "No database dump files found." . PHP_EOL;
    exit(1);
}
foreach ($glob as $key => $file) {
    echo "($key) {$file} " . humanFileSize(filesize($file)) . PHP_EOL;
}

// Select the file to restore
if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == '-1') {
    $filename = end($glob);
} else {
    echo "Type the number of the file you want to restore, or press Enter to restore the latest file" . PHP_EOL;
    $option = trim(readline());

    if ($option === '') {
        $filename = end($glob);
    } else {
        $option = intval($option);
        $filename = $glob[$option] ?? '';
    }
}

if (empty($filename) || !is_file($filename)) {
    echo "No valid database dump was selected." . PHP_EOL;
    exit(1);
}

// Restore the selected file
if (restoreMySQLBackup($filename)) {
    echo "Database restored successfully from {$filename}" . PHP_EOL;
} else {
    echo "Failed to restore the database from {$filename}" . PHP_EOL;
}

?>
