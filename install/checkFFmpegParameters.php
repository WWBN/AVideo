<?php
/**
 * Checks the stored CustomizeAdvanced->ffmpegParameters setting and, on request, replaces a value
 * that is not an allowed list of FFmpeg output options with the default profile.
 *
 * The conversion code never executes a rejected value (it uses the default profile and logs a
 * SECURITY entry), but the stored value stays in the database until it is fixed here or saved
 * again from the plugin settings.
 *
 * php install/checkFFmpegParameters.php          report only
 * php install/checkFFmpegParameters.php --show   also print the stored value (JSON-escaped)
 * php install/checkFFmpegParameters.php --fix    back up the whole plugin row data to a file
 *                                                outside the web root and store the default
 */
require_once __DIR__ . '/../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$fix = in_array('--fix', $argv, true);
$show = in_array('--show', $argv, true);

$plugin = AVideoPlugin::loadPlugin('CustomizeAdvanced');
$row = Plugin::getPluginByName('CustomizeAdvanced');
if (empty($plugin) || empty($row['uuid'])) {
    echo "CustomizeAdvanced plugin not found" . PHP_EOL;
    exit(2);
}
$stored = PluginAbstract::getObjectDataFromDatabase($row['uuid']);
if (!is_object($stored) || !property_exists($stored, 'ffmpegParameters')) {
    echo "ffmpegParameters is not stored; the default profile is used: " . getFFmpegConversionDefaultParameters() . PHP_EOL;
    exit(0);
}

$value = $stored->ffmpegParameters;
$raw = is_string($value) ? $value : json_encode($value);
echo "Stored ffmpegParameters: length=" . strlen((string) $raw) . " sha256=" . hash('sha256', (string) $raw) . PHP_EOL;
if ($show) {
    // JSON-escaped so control characters in a tampered value cannot reach the terminal.
    echo "Value: " . json_encode($raw, JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

if (parseFFmpegConversionParameters($value, $error) !== false) {
    echo "OK: the value is an allowed list of FFmpeg output options." . PHP_EOL;
    exit(0);
}
echo "REJECTED: {$error}. Conversions use the default profile until this is fixed." . PHP_EOL;
if (!$fix) {
    echo "Run again with --fix to back up the plugin data and store the default profile." . PHP_EOL;
    exit(1);
}

$backupFile = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'CustomizeAdvanced-object_data-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.json';
$previousUmask = umask(0077);
$written = file_put_contents($backupFile, (string) $row['object_data']);
umask($previousUmask);
if ($written === false) {
    echo "Could not write the backup file {$backupFile}; nothing was changed." . PHP_EOL;
    exit(2);
}
echo "Backup of the stored plugin data: {$backupFile} (move it to your evidence storage)" . PHP_EOL;

$stored->ffmpegParameters = getFFmpegConversionDefaultParameters();
if (!$plugin->setDataObject($stored)) {
    echo "Could not save the plugin data; the stored value was not changed." . PHP_EOL;
    exit(2);
}
ObjectYPT::deleteALLCache();
echo "Stored the default profile: " . getFFmpegConversionDefaultParameters() . PHP_EOL;
_error_log('install/checkFFmpegParameters.php: CustomizeAdvanced ffmpegParameters replaced with the default profile, backup ' . $backupFile, AVideoLog::$SECURITY);
exit(0);
