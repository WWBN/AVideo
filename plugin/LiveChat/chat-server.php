<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LiveChat/ratchet/autoload.php';
require_once $global['systemRootPath'] . 'plugin/LiveChat/Chat.php';
require_once $global['systemRootPath'] . 'plugin/LiveChat/LiveChat.php';
$lc = new LiveChat();
$obj = $lc->getDataObject();

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    $obj->port
);

$server->run();