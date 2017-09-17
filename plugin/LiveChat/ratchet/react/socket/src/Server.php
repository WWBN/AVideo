<?php

namespace React\Socket;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;

final class Server extends EventEmitter implements ServerInterface
{
    private $server;

    public function __construct($uri, LoopInterface $loop, array $context = array())
    {
        // sanitize TCP context options if not properly wrapped
        if ($context && (!isset($context['tcp']) && !isset($context['tls']))) {
            $context = array('tcp' => $context);
        }

        // apply default options if not explicitly given
        $context += array(
            'tcp' => array(),
            'tls' => array(),
        );

        $scheme = 'tcp';
        $pos = strpos($uri, '://');
        if ($pos !== false) {
            $scheme = substr($uri, 0, $pos);
        }

        $server = new TcpServer(str_replace('tls://', '', $uri), $loop, $context['tcp']);

        if ($scheme === 'tls') {
            $server = new SecureServer($server, $loop, $context['tls']);
        }

        $this->server = $server;

        $that = $this;
        $server->on('connection', function (ConnectionInterface $conn) use ($that) {
            $that->emit('connection', array($conn));
        });
        $server->on('error', function (\Exception $error) use ($that) {
            $that->emit('error', array($error));
        });
    }

    public function getAddress()
    {
        return $this->server->getAddress();
    }

    public function pause()
    {
        $this->server->pause();
    }

    public function resume()
    {
        $this->server->resume();
    }

    public function close()
    {
        $this->server->close();
    }
}
