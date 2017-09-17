<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LiveChat/ratchet/autoload.php';
require_once $global['systemRootPath'] . 'plugin/LiveChat/Chat.php';


$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8888
);

$server->run();