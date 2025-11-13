<?php
/*
 * Manual cleanup script for original_v_* files
 * Can be run from command line or install directory
 */

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();

echo "=== AVideo Original Files Cleanup Tool ===\n";
echo "This tool will help you clean up old original_v_* files\n\n";

// Check if Scheduler plugin is enabled
if (!AVideoPlugin::isEnabledByName('Scheduler')) {
    echo "WARNING: Scheduler plugin is not enabled.\n";
    echo "You should enable it to use automatic cleanup.\n\n";
}

$videosDir = getVideosDir();
echo "Videos directory: $videosDir\n";

// Find all original_v_* files with timeout protection
echo "Scanning for original_v_* files (videos folder only)...\n";
$scanStart = time();

// Use opendir/readdir for better performance - only scan videos folder directly
$originalFiles = array();
if ($handle = opendir($videosDir)) {
    while (false !== ($file = readdir($handle))) {
        if (strpos($file, 'original_v_') === 0) {
            $fullPath = $videosDir . $file;
            if (is_file($fullPath)) {
                $originalFiles[] = $fullPath;
            }
        }
    }
    closedir($handle);
} else {
    die("ERROR: Cannot open videos directory: $videosDir\n");
}

$scanTime = time() - $scanStart;
echo "Scan completed in {$scanTime} seconds.\n";

if (empty($originalFiles)) {
    echo "No original_v_* files found.\n";
    exit(0);
}

echo "Found " . count($originalFiles) . " original_v_* files\n\n";

// Get days parameter from command line
$days = 7; // default
if (!empty($argv[1])) {
    $days = intval($argv[1]);
    if ($days <= 0) {
        echo "ERROR: Days parameter must be a positive number\n";
        echo "Usage: php " . basename(__FILE__) . " [days] [--dry-run]\n";
        exit(1);
    }
}

$dryRun = in_array('--dry-run', $argv);

echo "Configuration:\n";
echo "- Days to keep: $days\n";
echo "- Dry run: " . ($dryRun ? 'Yes' : 'No') . "\n\n";

if (!$dryRun) {
    echo "WARNING: This will permanently delete files older than $days days!\n";
    echo "Type 'yes' to continue or anything else to abort: ";

    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);

    if (trim($line) !== 'yes') {
        echo "Aborted by user\n";
        exit(0);
    }
    echo "\n";
}

$now = time();
$timeLimit = $days * 24 * 60 * 60;
$deletedCount = 0;
$totalSize = 0;
$deletedSize = 0;
$totalFiles = count($originalFiles);
$processedFiles = 0;
$startTime = time();

echo "Analyzing $totalFiles files...\n";
echo str_repeat("-", 80) . "\n";
printf("%-40s | %10s | %8s | %s\n", "Filename", "Size", "Age", "Action");
echo str_repeat("-", 80) . "\n";

foreach ($originalFiles as $filePath) {
    $processedFiles++;

    if (!is_file($filePath)) {
        echo "[$processedFiles/$totalFiles] Skipping non-file: " . basename($filePath) . "\n";
        continue;
    }

    $fileName = basename($filePath);
    $fileModTime = filemtime($filePath);
    $fileSize = filesize($filePath);
    $ageInDays = round(($now - $fileModTime) / (24 * 60 * 60), 1);
    $isOld = ($now - $fileModTime) > $timeLimit;

    $totalSize += $fileSize;

    printf("%-40s | %10s | %6.1f d | %s",
        substr($fileName, 0, 40),
        humanFileSize($fileSize),
        $ageInDays,
        $isOld ? ($dryRun ? 'WOULD DELETE' : 'DELETING') : 'KEEPING'
    );

    if ($isOld) {
        if (!$dryRun) {
            if (unlink($filePath)) {
                $deletedCount++;
                $deletedSize += $fileSize;
                echo " ✓\n";
            } else {
                echo " ✗ FAILED\n";
                echo "ERROR: Failed to delete $fileName\n";
            }
        } else {
            $deletedCount++;
            $deletedSize += $fileSize;
            echo "\n";
        }
    } else {
        echo "\n";
    }

    // Show progress every 10 files or if it's a large file
    if ($processedFiles % 10 == 0 || $fileSize > 100 * 1024 * 1024) {
        $percent = round(($processedFiles / $totalFiles) * 100, 1);
        echo "Progress: $processedFiles/$totalFiles files ($percent%) - Freed so far: " . humanFileSize($deletedSize) . "\n";
        flush(); // Force output to show immediately
    }
}

echo str_repeat("-", 80) . "\n";
echo "\nSummary:\n";
$executionTime = time() - $startTime;
echo "Execution time: {$executionTime} seconds\n";
echo "Total files found: " . count($originalFiles) . "\n";
echo "Total size: " . humanFileSize($totalSize) . "\n";
echo "Files " . ($dryRun ? 'that would be deleted' : 'deleted') . ": $deletedCount\n";
echo "Space " . ($dryRun ? 'that would be freed' : 'freed') . ": " . humanFileSize($deletedSize) . "\n";

if ($dryRun) {
    echo "\nThis was a dry run. To actually delete files, run:\n";
    echo "php " . basename(__FILE__) . " $days\n";
} else {
    echo "\nCleanup completed!\n";
    if ($deletedCount > 0) {
        _error_log("Manual cleanup of original_v_* files completed via install script. Deleted $deletedCount files, freed " . humanFileSize($deletedSize) . " of space");
    }
}

echo "\nTo enable automatic cleanup:\n";
echo "1. Enable the Scheduler plugin in the admin panel\n";
echo "2. Configure the cleanup settings\n";
echo "3. Set up a cron job to run the scheduler\n";
?>
