<?php
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

function setPermissions($directories) {
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            echo "Set Permission $dir ".PHP_EOL;
            setPermissionsRecursively($dir);
        } else {
            echo "The path $dir is not a directory.\n";
        }
    }
}

function setPermissionsRecursively($dir) {
    // Ensure the directory path is safe to use
    $safeDir = escapeshellarg($dir);

    // Set directory permissions to 755
    exec("find $safeDir -type d -exec chmod 755 {} +");

    // Set file permissions to 644
    exec("find $safeDir -type f -exec chmod 644 {} +");

    // Change ownership to www-data
    exec("chown -R www-data:www-data $safeDir");
}

// Example usage
$directories = [
    getVideosDir(),
    "{$global['systemRootPath']}Encoder/videos" . DIRECTORY_SEPARATOR
];

setPermissions($directories);

echo "Permissions have been set successfully.\n";

?>
