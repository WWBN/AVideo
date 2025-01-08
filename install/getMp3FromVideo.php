<?php
// Streamer config
require_once __DIR__.'/../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    die('Command Line only');
}
$global['printLogs'] = 1;
// Prompt user for video ID
echo "Enter the video ID: ";
$videos_id = trim(fgets(STDIN));

// Validate input
if (!is_numeric($videos_id) || $videos_id <= 0) {
    die("Invalid video ID. Please enter a valid positive integer.\n");
}

// Load the video
$video = new Video('', '', $videos_id);

if ($video->getType() === Video::$videoTypeVideo) {
    global $global;
    $global['convertVideoToMP3FileIfNotExistsSteps'] = []; // Initialize steps tracker
    $global['convertVideoToMP3FileIfNotExistsFileAlreadyExists'] = false; // Reset global tracker
    $global['convertVideoToMP3FilePath'] = ''; // Reset global file path

    // Attempt to convert the video to MP3
    $converted = convertVideoToMP3FileIfNotExists($videos_id);

    if ($global['convertVideoToMP3FileIfNotExistsFileAlreadyExists']) {
        echo "MP3 file already exists at: {$global['convertVideoToMP3FilePath']}\n";
        echo "Do you want to remove and try again? (y/n): ";
        $response = trim(fgets(STDIN));
        if (strtolower($response) === 'y') {
            // Force conversion
            $converted = convertVideoToMP3FileIfNotExists($videos_id, 1);
        }
    }

    // Print the results
    echo "Conversion Results for Video ID {$videos_id}:\n";

    echo $converted ? "Conversion successful!\n" : "Conversion failed.\n";

    // Print the tracked steps
    echo "Execution Steps:\n";
    foreach ($global['convertVideoToMP3FileIfNotExistsSteps'] as $step) {
        echo "- {$step}\n";
    }

    $duration = getDurationFromFile($global['convertVideoToMP3FilePath']);
    echo "MP3 Duration: {$duration}\n";
} else {
    echo "The specified video is not of type 'Video'. No conversion attempted.\n";
}
