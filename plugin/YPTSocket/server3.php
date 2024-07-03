<?php

use Swoole\WebSocket\Server;

$server = new Server("0.0.0.0", 9502);

$clients = [];
$messageQueue = [];

// Function to broadcast messages to all connected clients
function broadcastMessages($server, $clients, $messages) {
    $data = json_encode($messages);
    foreach ($clients as $fd) {
        if ($server->isEstablished($fd)) {
            $server->push($fd, $data);
        }
    }
}

// Set a timer to broadcast messages every 10 seconds
$server->tick(10000, function() use ($server, &$clients, &$messageQueue) {
    if (!empty($messageQueue)) {
        broadcastMessages($server, $clients, $messageQueue);
        $messageQueue = [];
    }
});

// Handle new WebSocket connections
$server->on('open', function ($server, $request) use (&$clients) {
    echo "Connection open: {$request->fd}\n";
    $clients[] = $request->fd;
});

// Handle incoming messages
$server->on('message', function ($server, $frame) use (&$messageQueue) {
    echo "Received message: {$frame->data}\n";
    $messageQueue[] = $frame->data;
});

// Handle closed connections
$server->on('close', function ($server, $fd) use (&$clients) {
    echo "Connection close: {$fd}\n";
    $clients = array_filter($clients, function($clientFd) use ($fd) {
        return $clientFd !== $fd;
    });
});

$server->start();
