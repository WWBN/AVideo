<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Socket\Message2;

//use React\Socket\Server as Reactor;

require_once dirname(__FILE__) . '/../../videos/configuration.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

require_once $global['systemRootPath'] . 'plugin/YPTSocket/Message2.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';

if (!isCommandLineInterface()) {
    die("Command line only");
}

$SocketDataObj = AVideoPlugin::getDataObject("YPTSocket");
$SocketDataObj->serverVersion = YPTSocket::getServerVersion();

ob_end_flush();
_mysql_close();
_session_write_close();
exec('ulimit -n 20480'); // to handle over 1 k connections
$SocketDataObj->port = intval($SocketDataObj->port);
_error_log("Starting Socket server at port {$SocketDataObj->port}");
//killProcessOnPort();
$scheme = parse_url($global['webSiteRootURL'], PHP_URL_SCHEME);
echo "Starting AVideo Socket server version {$SocketDataObj->serverVersion} on port {$SocketDataObj->port}" . PHP_EOL;

if (strtolower($scheme) !== 'https' || !empty($SocketDataObj->forceNonSecure)) {
    $context = array('tls' => array(
            'local_cert' => $global['systemRootPath'] . 'plugin/YPTSocket/localhost.pem',
            'allow_self_signed' => true, // Allow self signed certs (should be false in production)
            'verify_peer' => false,
            'verify_peer_name' => false,
            'security_level' => 0
    ));
} else {
    $context = array('tls' => array(
            'local_cert' => $SocketDataObj->server_crt_file,
            'local_pk' => $SocketDataObj->server_key_file,
            'allow_self_signed' => $SocketDataObj->allow_self_signed, // Allow self signed certs (should be false in production)
            'verify_peer' => false,
            'verify_peer_name' => false,
            'security_level' => 0
    ));
}

$message = new Message2();

$socket = new React\Socket\SocketServer($SocketDataObj->uri . ':' . $SocketDataObj->port, $context);

$socket = new React\Socket\LimitingServer($socket, null);

$socket->on('connection', function (React\Socket\ConnectionInterface $connection) use ($socket) {
    echo '[' . $connection->getRemoteAddress() . ' connected] ' . PHP_EOL;
    $connection->write('hello there!' . PHP_EOL);
    // whenever a new message comes in
    $connection->on('data', function ($data) use ($connection, $socket) {
        global $global;
        // remove any non-word characters (just for the demo)
        //$data = trim(preg_replace('/[^\w\d \.\,\-\!\?]/u', '', $data));

        $parts = explode('?', $data);

        // ignore empty messages
        if (empty($parts[1])) {
            echo ("Empty parts " . json_encode($data));
            return;
        }

        parse_str($parts[1], $wsocketGetVars);
        foreach ($wsocketGetVars as $key => $value) {
            $wsocketGetVars[$key] = urldecode($value);
        }
        if (empty($wsocketGetVars['webSocketToken'])) {
            echo ("Empty websocket token " . json_encode($wsocketGetVars));
            return false;
        }
        $json = getDecryptedInfo($wsocketGetVars['webSocketToken']);
        if (empty($json)) {
            echo ("Invalid websocket token [{$global['webSiteRootURL']}]  [{$wsocketGetVars['webSocketToken']}]");
            return false;
        }
        echo 'websocket token [' .
        json_encode($connection->getLocalAddress()) .
        ', ' .
        json_encode($connection->getRemoteAddress()) . ']' . PHP_EOL;
        //echo ("websocket token ". json_encode($json));
        // prefix with client IP and broadcast to all connected clients
        $data = trim(parse_url($connection->getRemoteAddress(), PHP_URL_HOST), '[]') . ': ' . $data . PHP_EOL;
        foreach ($socket->getConnections() as $connection) {
            $connection->write($data);
        }
    });

    $connection->on('close', function () use ($connection) {
        echo '[' . $connection->getRemoteAddress() . ' disconnected]' . PHP_EOL;
    });

    $connection->on('end', function () {
        echo 'ended'. PHP_EOL;
    });

    $connection->on('error', function (Exception $e) {
        echo 'error: ' . $e->getMessage(). PHP_EOL;
    });
});

$socket->on('error', function (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
});

echo 'Listening on ' . $socket->getAddress() . PHP_EOL;
