<?php
require __DIR__ . '/../../vendor/autoload.php';

use React\EventLoop\Loop;
use React\Socket\SecureServer;
use React\Socket\Server as ReactServer;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Socket\Message;
use React\Stream\ReadableStreamInterface;

$global['debugMemmory'] = 1;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

require_once dirname(__FILE__) . '/../../videos/configuration.php';
_ob_end_clean();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

function riseSQLiteError()
{
    _error_log("Socket server For better performance install PDO SQLite in your PHP", AVideoLog::$ERROR);
    echo ("sudo apt-get install php-sqlite3") . PHP_EOL;
    echo ("after that in your php.ini (" . php_ini_loaded_file() . ") file to uncomment this line:") . PHP_EOL;
    echo (";extension=pdo_sqlite.so") . PHP_EOL;
}

$loop = Loop::get();
if (function_exists('pdo_drivers') && in_array("sqlite", pdo_drivers())) {
    _error_log("Socket server SQLite loading");
    require_once $global['systemRootPath'] . 'plugin/YPTSocket/db.php';
    require_once $global['systemRootPath'] . 'plugin/YPTSocket/MessageSQLiteV2.php';
} else {
    riseSQLiteError();
    require_once $global['systemRootPath'] . 'plugin/YPTSocket/Message.php';
}

require_once $global['systemRootPath'] . 'objects/autoload.php';

if (!isCommandLineInterface()) {
    die("Command line only");
}

function findValidCertificate($url)
{
    $letsencryptDir = '/etc/letsencrypt/live/';
    $domain = parse_url($url, PHP_URL_HOST);
    $certPath = '';
    $keyPath = '';

    if (is_dir($letsencryptDir . $domain)) {
        $certPath = $letsencryptDir . $domain . '/fullchain.pem';
        $keyPath = $letsencryptDir . $domain . '/privkey.pem';
        if (isCertificateValid($certPath)) {
            return ['crt' => $certPath, 'key' => $keyPath];
        }
    }

    $directories = glob($letsencryptDir . $domain . '-*');
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $certPath = $dir . '/fullchain.pem';
            $keyPath = $dir . '/privkey.pem';
            if (isCertificateValid($certPath)) {
                return ['crt' => $certPath, 'key' => $keyPath];
            }
        }
    }

    return [];
}

function isCertificateValid($certPath)
{
    if (!file_exists($certPath)) {
        return false;
    }

    $currentTimestamp = time();
    $opensslCommand = 'openssl';
    $output = [];
    exec($opensslCommand . ' x509 -noout -dates -in ' . escapeshellarg($certPath), $output, $returnValue);

    if ($returnValue === 0 && preg_match('/notBefore=(.*?)\nnotAfter=(.*)/i', implode("\n", $output), $matches)) {
        $validFrom = strtotime($matches[1]);
        $validTo = strtotime($matches[2]);

        if ($validFrom <= $currentTimestamp && $currentTimestamp <= $validTo) {
            return true;
        }
    }

    return false;
}

$SocketDataObj = AVideoPlugin::getDataObject("YPTSocket");
$SocketDataObj->serverVersion = YPTSocket::getServerVersion();

@ob_end_flush();
_mysql_close();
_session_write_close();
exec('ulimit -n 20480');
$SocketDataObj->port = intval($SocketDataObj->port);
_error_log("Starting Socket server at port {$SocketDataObj->port}");

$scheme = parse_url($global['webSiteRootURL'], PHP_URL_SCHEME);
echo "Starting AVideo Socket server version {$SocketDataObj->serverVersion} on port {$SocketDataObj->port}" . PHP_EOL;

if (!isCertificateValid($SocketDataObj->server_crt_file)) {
    echo "Certificate is invalid {$SocketDataObj->server_crt_file}" . PHP_EOL;
    $validCertPath = findValidCertificate($global['webSiteRootURL']);
    if (!empty($validCertPath['crt'])) {
        $SocketDataObj->server_crt_file = $validCertPath['crt'];
        $SocketDataObj->server_key_file = $validCertPath['key'];
        echo "Certificate found {$SocketDataObj->server_crt_file}" . PHP_EOL;
    }
}

$sslFound = file_exists($SocketDataObj->server_crt_file) && is_readable($SocketDataObj->server_crt_file) && file_exists($SocketDataObj->server_key_file) && is_readable($SocketDataObj->server_key_file);

if ((strtolower($scheme) !== 'https' || !empty($SocketDataObj->forceNonSecure)) && !$sslFound) {
    echo "Your socket server does NOT use a secure connection" . PHP_EOL;
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Message()
            )
        ),
        $SocketDataObj->port
    );

    $server->run();
} else {
    if (!file_exists($SocketDataObj->server_crt_file) || !is_readable($SocketDataObj->server_crt_file)) {
        echo "SSL ERROR, we could not access the CRT file {$SocketDataObj->server_crt_file}, try to run this command as root or use sudo " . PHP_EOL;
    }
    if (!file_exists($SocketDataObj->server_key_file) || !is_readable($SocketDataObj->server_key_file)) {
        echo "SSL ERROR, we could not access the KEY file {$SocketDataObj->server_key_file}, try to run this command as root or use sudo " . PHP_EOL;
    }

    echo "Your socket server uses a secure connection" . PHP_EOL;
    $parameters = [
        'local_cert' => $SocketDataObj->server_crt_file,
        'local_pk' => $SocketDataObj->server_key_file,
        'allow_self_signed' => $SocketDataObj->allow_self_signed, // Allow self signed certs (should be false in production)
        'verify_peer' => false,
        'verify_peer_name' => false,
        'security_level' => 0
    ];

    foreach ($parameters as $key => $value) {
        echo "Parameter [{$key}]: $value " . PHP_EOL;
    }
    echo "DO NOT CLOSE THIS TERMINAL " . PHP_EOL;

    // Create WebSocket Server
    $webSock = new ReactServer($SocketDataObj->uri . ':' . $SocketDataObj->port, $loop);
    $webSock = new SecureServer($webSock, $loop, $parameters);

    // Handle HTTP requests using guzzlehttp/psr7
    $httpServer = function (ServerRequestInterface $request) {
        return new Response(
            200,
            ['Content-Type' => 'text/plain'],
            "Socket server is running. SSL is valid."
        );
    };

    // Handle incoming connections and handle HTTP requests
    $webSock->on('connection', function ($conn) use ($httpServer) {
        $conn->on('data', function ($data) use ($conn, $httpServer) {
            // Simulate an HTTP request and return a response indicating the server status
            $response = $httpServer(new ServerRequest('GET', '/'));

            // Send an HTTP-compliant response back to the client
            $headers = "HTTP/1.1 200 OK\r\n";
            $headers .= "Content-Type: text/plain\r\n";
            $headers .= "Content-Length: " . strlen((string) $response->getBody()) . "\r\n";
            $headers .= "Connection: close\r\n\r\n";

            $conn->write($headers . $response->getBody());
            $conn->end();
        });
    });

    // Create WebSocket server instance
    $webServer = new IoServer(
        new HttpServer(
            new WsServer(
                new Message()
            )
        ),
        $webSock
    );

    // Run the loop
    $loop->run();
}
