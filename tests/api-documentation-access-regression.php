<?php
// Exercise the production access method and IP helpers without a database.
class User {
    public static $admin = false;
    public static function isAdmin() { return self::$admin; }
}
class AVideoPlugin {
    public static $data;
    public static function getObjectDataIfEnabled($name) { return self::$data; }
}
function isCommandLineInterface() { return false; }
$source = file_get_contents(dirname(__DIR__) . '/objects/functions.php');
$start = strpos($source, 'function getRemoteAddrFromServerArray(');
$end = strpos($source, 'function cleanString(', $start);
eval(substr($source, $start, $end - $start));
$source = file_get_contents(dirname(__DIR__) . '/plugin/API/API.php');
$start = strpos($source, '    public static function canAccessDocumentation()');
$end = strpos($source, '    public function getPluginMenu()', $start);
eval('class API {' . substr($source, $start, $end - $start) . '}');

function checkDocumentationAccess($expected, $label) {
    if (API::canAccessDocumentation() !== $expected) {
        throw new RuntimeException($label);
    }
}
$global = [];
$_SERVER = ['REMOTE_ADDR' => '192.0.2.10'];
AVideoPlugin::$data = (object) ['documentationAllowedIPs' => ''];
checkDocumentationAccess(false, 'Empty list must deny guests');
User::$admin = true;
checkDocumentationAccess(true, 'Admin must retain access');
User::$admin = false;
AVideoPlugin::$data->documentationAllowedIPs = "198.51.100.1, 192.0.2.10\n2001:db8::1";
checkDocumentationAccess(true, 'Listed IPv4 must have access');
$_SERVER['REMOTE_ADDR'] = '192.0.2.11';
checkDocumentationAccess(false, 'Unlisted IPv4 must be denied');
$_SERVER['REMOTE_ADDR'] = '2001:0db8:0000:0000:0000:0000:0000:0001';
checkDocumentationAccess(true, 'Equivalent IPv6 representations must match');
AVideoPlugin::$data->documentationAllowedIPs = '198.51.100.1';
checkDocumentationAccess(false, 'Removing an IP must revoke subsequent access');
$_SERVER = ['REMOTE_ADDR' => '192.0.2.10', 'HTTP_X_FORWARDED_FOR' => '198.51.100.1'];
checkDocumentationAccess(false, 'Untrusted forwarded header must not grant access');
$_SERVER['HTTP_X_REAL_IP'] = '198.51.100.1';
checkDocumentationAccess(false, 'Untrusted real-IP header must not grant access');
$global['trustedProxies'] = ['192.0.2.10'];
checkDocumentationAccess(true, 'Configured trusted proxy must resolve client IP');
unset($_SERVER['HTTP_X_REAL_IP']);
checkDocumentationAccess(true, 'Trusted forwarded-for header must work');
$_SERVER['HTTP_X_FORWARDED_FOR'] = 'invalid';
checkDocumentationAccess(false, 'Invalid forwarded IP must not grant access');
$global = [];
$_SERVER = ['REMOTE_ADDR' => '192.0.2.10'];
foreach (['*', '192.0.2.0/24', '192.0.2.10.evil', ['192.0.2.10']] as $invalid) {
    AVideoPlugin::$data->documentationAllowedIPs = $invalid;
    checkDocumentationAccess(false, 'Invalid entries and ranges must not authorize');
}
AVideoPlugin::$data->documentationAllowedIPs = '127.0.0.1';
$_SERVER = [];
checkDocumentationAccess(false, 'Missing peer must not authorize loopback fallback');
$_SERVER = ['REMOTE_ADDR' => 'invalid'];
checkDocumentationAccess(false, 'Invalid peer must not authorize loopback fallback');
AVideoPlugin::$data = false;
checkDocumentationAccess(false, 'Disabled plugin must deny guests');

foreach (['index.php', 'swagger.json.php'] as $file) {
    $entry = file_get_contents(dirname(__DIR__) . '/plugin/API/' . $file);
    if (strpos($entry, 'if (!API::canAccessDocumentation())') === false ||
        strpos($entry, "forbiddenPage('API documentation access denied. Your IP: ' . getRealIpAddr() . '. Ask the administrator to add this IP to the API documentation allowed IPs.', true)") === false ||
        strpos($entry, "header('Cache-Control: private, no-store')") === false) {
        throw new RuntimeException('Documentation entry point missing access/cache guard: ' . $file);
    }
}
echo "API documentation access regression: passed\n";
