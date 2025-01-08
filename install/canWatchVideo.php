<?php
// Streamer config
require_once __DIR__ . '/../videos/configuration.php';
ob_end_flush();

if (!isCommandLineInterface()) {
    die('This script must be run from the command line.');
}

// Prompt for the user ID
echo "Enter the User ID: ";
$users_id = intval(trim(fgets(STDIN)));

// Prompt for the video ID
do {
    echo "Enter the Video ID: ";
    $videos_id = trim(fgets(STDIN));
    if (!is_numeric($videos_id) || $videos_id <= 0) {
        echo "Invalid Video ID. Please enter a valid positive integer.\n";
        $videos_id = null;
    }
} while (empty($videos_id));

// Ignore cache is mandatory
$ignoreCache = true;

// Check if the user can watch the video
$canWatch = User::canWatchVideo($videos_id, $users_id, $ignoreCache);
$reason = $global['canWatchVideoReason'] ?? 'Reason not specified.';

// Display the result
echo "\nCan User ID {$users_id} watch Video ID {$videos_id}?\n";
echo $canWatch ? "Yes\n" : "No\n";
echo "Reason: {$reason}\n";


$canWatch = User::canWatchVideoWithAds($videos_id, $users_id, $ignoreCache);
$reason = $global['canWatchVideoReason'] ?? 'Reason not specified.';

// Display the result
echo "\nADs: Can User ID {$users_id} watch Video ID {$videos_id}?\n";
echo $canWatch ? "Yes\n" : "No\n";
echo "Reason: {$reason}\n";
