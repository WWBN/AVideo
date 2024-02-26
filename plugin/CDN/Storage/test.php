<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once '../CDN.php';

if (!User::isAdmin()) {
    die("Must be admin for testing");
}

if (!function_exists('ftp_put')) {
    die("You MUST install the PHP FTP functions");
}

$obj = AVideoPlugin::getDataObject('CDN');
_error_log("CDNStorage: test start");
$tmp_name = "{$global['systemRootPath']}plugin/CDN/Storage/test.txt";
$filename = "test.txt";
$remote_file = "{$filename}";

echo '<h2>Transferring...</h2>' . PHP_EOL;

echo '<h4>Test 1 Default configuration</h4>' . PHP_EOL;
echo '<h4>Test 1 Default configuration</h4>' . PHP_EOL;
$CDNstorage = new \FtpClient\FtpClient();
$CDNstorage->connect($obj->storage_hostname);
$CDNstorage->login($obj->storage_username, $obj->storage_password);
$CDNstorage->pasv(true);
if ($CDNstorage->modifiedTime($remote_file) > 0) {
    $CDNstorage->delete($filename);
    if ($CDNstorage->modifiedTime($remote_file) > 0) {
        die("Please first delete the file {$remote_file} from the FTP dir");
    }
}
$response = $CDNstorage->put($remote_file, $tmp_name);
if ($CDNstorage->modifiedTime($remote_file) > 0) {
    echo "The default configuration works";
} else {
    echo '<h4>Default configuration fail</h4>' . PHP_EOL;
}

$pz = CDNStorage::getPZ();

echo "<br><a href='https://{$pz}{$filename}' target='_blank'>{$filename}</a><br>";
