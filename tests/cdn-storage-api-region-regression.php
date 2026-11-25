<?php
// Standalone regression: php tests/cdn-storage-api-region-regression.php
// A storage hostname without a Bunny region prefix ("storage.bunnycdn.com", or a custom
// name such as "storage.ypt.me") produced the region "storage", so every API upload threw
// InvalidRegionException and each HLS segment fell back to a slow FTP upload.
namespace CdnStorageApiRegionRegression;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$source = file_get_contents(dirname(__DIR__) . '/plugin/CDN/Storage/CDNStorage.php');
$start = strpos($source, 'public static function getAPIRegion(');
$end = strpos($source, 'public static function putUsingAPI(', $start);
if ($start === false || $end === false) {
    throw new \RuntimeException('Could not locate CDNStorage::getAPIRegion');
}
class AVideoPlugin
{
    public static function getDataObjectIfEnabled($name)
    {
        return (object) ['storage_password' => 'zone-api-key-123'];
    }
}

eval('namespace ' . __NAMESPACE__ . '; class CDNStorage { ' . substr($source, $start, $end - $start) . ' }');

$cases = [
    'storage.bunnycdn.com' => 'de',
    'storage.ypt.me' => 'de',
    'ny.storage.bunnycdn.com' => 'ny',
    'LA.storage.bunnycdn.com' => 'la',
    ' syd.storage.bunnycdn.com ' => 'syd',
    '' => 'de',
];
$checks = 0;
foreach ($cases as $hostname => $expected) {
    $region = CDNStorage::getAPIRegion($hostname);
    if ($region !== $expected) {
        throw new \RuntimeException("Region for [$hostname] is [$region], expected [$expected]");
    }
    // The Bunny client must accept it: an invalid region throws before any upload starts.
    new \Bunny\Storage\Client('key', 'zone', $region);
    ++$checks;
}

// Bunny's AuthenticationException carries the API key in its message; the log text must not.
$logText = CDNStorage::getAPIErrorForLog(new \Bunny\Storage\AuthenticationException('zone', 'zone-api-key-123'));
if (strpos($logText, 'zone-api-key-123') !== false || strpos($logText, 'AuthenticationException') === false) {
    throw new \RuntimeException("API error log text leaks the access key: $logText");
}
++$checks;

echo "PASS: $checks storage API region checks\n";
