# Elephant.io

![Build Status](https://github.com/ElephantIO/elephant.io/actions/workflows/continuous-integration.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/elephantio/elephant.io/v/stable.svg)](https://packagist.org/packages/elephantio/elephant.io)
[![Total Downloads](https://poser.pugx.org/elephantio/elephant.io/downloads.svg)](https://packagist.org/packages/elephantio/elephant.io) 
[![License](https://poser.pugx.org/elephantio/elephant.io/license.svg)](https://packagist.org/packages/elephantio/elephant.io)

```
        ___     _,.--.,_         Elephant.io is a socket.io client written in PHP.
      .-~   ~--"~-.   ._ "-.     Its goal is to ease the communications between your
     /      ./_    Y    "-. \    PHP application and a socket.io server.
    Y       :~     !         Y
    lq p    |     /         .|   Requires PHP 7.4+ and mbstring extension,
 _   \. .-, l    /          |j   licensed under the MIT License
()\___) |/   \_/";          !
 \._____.-~\  .  ~\.      ./
            Y_ Y_. "vr"~  T      Built-in engines:
            (  (    |L    j      - Socket.io 4.x, 3.x, 2.x, 1.x
            [nn[nn..][nn..]      - Socket.io 0.x (courtesy of @kbu1564)
          ~~~~~~~~~~~~~~~~~~~
```

## Installation

We are suggesting you to use composer, using `composer require elephantio/elephant.io`. For other ways, you can check the release page, or the git clone urls.

## Usage

To use Elephant.io to communicate with socket.io server is described as follows.

```php
<?php

use ElephantIO\Client;

$url = 'http://localhost:8080';
// if socket server is served in non root path, adjust the url to include those path, e.g:
// $url = 'http://localhost:8080/my'

// if client option is omitted then it will use latest client available,
// aka. version 4.x
$options = ['client' => Client::CLIENT_4X];

$client = Client::create($url, $options);
$client->connect();
$client->of('/'); // can be omitted if connecting to default namespace

// emit an event to the server
$data = ['username' => 'my-user'];
$client->emit('get-user-info', $data);

// wait an event to arrive
// beware when waiting for response from server, the script may be killed if
// PHP max_execution_time is reached
if ($packet = $client->wait('user-info')) {
    // an event has been received, the result will be a \ElephantIO\Engine\Packet class
    // data property contains the first argument
    // args property contains array of arguments, [$data, ...]
    $data = $packet->data;
    $args = $packet->args;
    // access data
    $email = $data['email'];
}

// end session
$client->disconnect();
```

## Options

Elephant.io accepts options to configure the internal engine such as passing headers, providing additional
authentication token, or providing stream context.

* `auth` _(socket.io 3+)_

  Specify an array to be passed as handshake. The data to be passed depends on the server implementation.

  ```php
  <?php

  $options = [
      'auth' => [
          'user' => 'user@example.com',
          'token' => 'my-secret-token',
      ]
  ];
  $client = Client::create($url, $options);
  ```

  On the server side, those data can be accessed using:

  ```js
  io.use((socket, next) => {
      const user = socket.handshake.auth.user;
      const token = socket.handshake.auth.token;
      // do something with data
  });
  ```

* `binary_as_resource`

  When client receives a binary data, by default it will be presented as `resource`.
  Set to `false` to retain it as string instead. Be careful, when you read the resource
  content, it is necessary to seek the stream to the begining using `fseek($handle, 0)`
  first. 

* `binary_chunk_size` _(socket.io 1+)_

  Set the maximum chunk size when sending binary data to the server. Default to `8192`.

* `context`

  A [stream context](https://www.php.net/manual/en/context.php) options for the socket stream
  for http or ssl.

  ```php
  <?php

  $options = [
      'context' => [
          'http' => [],
          'ssl' => [],
      ]
  ];
  $client = Client::create($url, $options);
  ```

* `cors`

  Adds `Origin` and `Referer` header automatically using provided socket url. Both `Origin` and `Referer`
  headers still can be overridden using `context` headers or `headers` option. To disable this behavior
  set `cors` to `false`.

* `headers` _(socket.io 1+)_

  An array of key-value pair to be sent as request headers. For example, pass a bearer token to the server.

  ```php
  <?php

  $options = [
      'headers' => [
          'Authorization' => 'Bearer MYTOKEN',
      ]
  ];
  $client = Client::create($url, $options);
  ```

* `persistent`

  The socket connection by default will be using a persistent connection. If you prefer for some
  reasons to disable it, set `persistent` to `false`.

* `reuse_connection`

  Enable or disable existing connection reuse, by default the engine will reuse existing
  connection. This option determines the `Connection` header to be sent to the server, if enabled
  then the connection will be `keep-alive` otherwise `close`.
  
  To disable to reuse existing connection set `reuse_connection` to `false`.

* `sio_path`

  Used to customize socket.io path instead of `socket.io`.

* `transport`

  Initial socket transport used to connect to server, either `polling` or `websocket` is supported.
  The default transport used is `polling` and it will be upgraded to `websocket` if the server offers
  to upgrade and `transports` option does not exclude `websocket`.

  To connect to server with `polling` only transport:

  ```php
  <?php

  $options = [
      'transport' => 'polling',     // can be omitted as polling is default transport
      'transports' => ['polling'],
  ];
  $client = Client::create($url, $options);
  ```

  To connect to server with `websocket` only transport:

  ```php
  <?php

  $options = [
      'transport' => 'websocket',
  ];
  $client = Client::create($url, $options);
  ```

* `transports`

  An array of enabled transport. Set to `null` to enable all transports or combination of
  `polling` and `websocket` to enable specific transport.

* `ua`

  Adds `Elephant.io/VERSION` as `User-Agent` header automatically. `User-Agent` header still
  can be overridden by setting `ua` as user agent string or using `headers` option. To disable
  this behavior set `ua` to `false`.

## Methods

Elephant.io client (`ElephantIO\Client`) provides the following api methods:

* `connect()`

  Connect to socket.io server. In case of server connection is unsuccessful, an exception
  `ElephantIO\Exception\SocketException` will be thrown. It also connects to default
  `/` namespace and will trigger `ElephantIO\Exception\UnsuccessfulOperationException`
  upon unsuccessful attempts.

* `disconnect()`

  Disconnect from server and free some resources.

* `isConnected()`

  Check server connection state, return `true` if connected, `false` otherwise.

* `of($namespace)`

  Connect to a namespace, see `connect()` above for possible errors.

* `emit($event, $args, $ack = null)`

  Send an event to server. To request an acknowledgement from server, set `$ack` to `true`.
  When an acknowledgement is requested, a packet will be returned on successful operation.

  The `args` can be an array which would be passed as the first argument to the server, if
  you need to pass any arbitrary arguments to the server, use `\ElephantIO\Engine\Argument`
  object instead. e.g. `new \ElephantIO\Engine\Argument(1, 'two')`.

* `ack($packet, $args)`

  Acknowledge a received event. The `args` also behave as `emit()` above.

* `wait($event, $timeout = 0)`

  Wait an event to be received from server. To wait any event, pass `null` as event name.

* `drain($timeout = 0)`

  Drain and get returned packet from server, used to receive data from server
  when we are not expecting an event to arrive.

* `getEngine()`

  Get the underlying socket engine.

## Debugging

It's sometime necessary to get the verbose output for debugging. Elephant.io utilizes `Psr\Log\LoggerInterface`
for this purpose.

```php
<?php

use ElephantIO\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LogLevel;

$url = 'http://localhost:8080';
$logfile = __DIR__ . '/socket.log';

$logger = new Logger('elephant.io');
$logger->pushHandler(new StreamHandler($logfile, LogLevel::DEBUG)); // set LogLevel::INFO for brief logging

$options = ['logger' => $logger];

$client = Client::create($url, $options);
```

Here is an example of debug logging:

```log
[2026-02-17T16:24:59.055720+07:00] elephant.io.INFO: Connecting to server [] []
[2026-02-17T16:24:59.058090+07:00] elephant.io.INFO: Starting handshake [] []
[2026-02-17T16:24:59.059597+07:00] elephant.io.INFO: Stream connect: tcp://localhost:14000 [] []
[2026-02-17T16:24:59.071585+07:00] elephant.io.DEBUG: Stream write: GET /socket.io/?EIO=4&transport=polling&t=Pngwitu HTTP/1.1 [] []
[2026-02-17T16:24:59.072212+07:00] elephant.io.DEBUG: Stream write: Host: localhost:14000 [] []
[2026-02-17T16:24:59.103811+07:00] elephant.io.DEBUG: Stream write: Accept: */* [] []
[2026-02-17T16:24:59.104156+07:00] elephant.io.DEBUG: Stream write: Origin: http://localhost:14000 [] []
[2026-02-17T16:24:59.104390+07:00] elephant.io.DEBUG: Stream write: Referer: http://localhost:14000 [] []
[2026-02-17T16:24:59.108800+07:00] elephant.io.DEBUG: Stream write: User-Agent: Elephant.io/* [] []
[2026-02-17T16:24:59.109315+07:00] elephant.io.DEBUG: Stream write: Connection: keep-alive [] []
[2026-02-17T16:24:59.109633+07:00] elephant.io.DEBUG: Stream write:  [] []
[2026-02-17T16:24:59.109929+07:00] elephant.io.DEBUG: Stream write:  [] []
[2026-02-17T16:24:59.110260+07:00] elephant.io.DEBUG: Stream receive: HTTP/1.1 200 OK [] []
[2026-02-17T16:24:59.113710+07:00] elephant.io.DEBUG: Stream receive: Content-Type: text/plain; charset=UTF-8 [] []
[2026-02-17T16:24:59.129783+07:00] elephant.io.DEBUG: Stream receive: Content-Length: 118 [] []
[2026-02-17T16:24:59.144835+07:00] elephant.io.DEBUG: Stream receive: cache-control: no-store [] []
[2026-02-17T16:24:59.159958+07:00] elephant.io.DEBUG: Stream receive: Date: Tue, 17 Feb 2026 09:24:59 GMT [] []
[2026-02-17T16:24:59.175201+07:00] elephant.io.DEBUG: Stream receive: Connection: keep-alive [] []
[2026-02-17T16:24:59.189980+07:00] elephant.io.DEBUG: Stream receive: Keep-Alive: timeout=5 [] []
[2026-02-17T16:24:59.205458+07:00] elephant.io.DEBUG: Stream receive:  [] []
[2026-02-17T16:24:59.220025+07:00] elephant.io.DEBUG: Stream receive: 0{"sid":"pAVq_mgNxE_stKEZAAAo","upgrades":["websocket"],"pingInterval":25000,"pingTimeout":20000,"ma... 18 more [] []
[2026-02-17T16:24:59.221745+07:00] elephant.io.INFO: Got packet: OPEN{"data":{"sid":"pAVq_mgNxE_stKEZAAAo","upgrades":["websocket"],"pingInterval":25000,"pingTimeout... 30 more [] []
[2026-02-17T16:24:59.222229+07:00] elephant.io.INFO: Handshake finished with SESSION{"id":"pAVq_mgNxE_stKEZAAAo","upgrades":["websocket"],"timeouts":{"interval":25,"timeout":20},"max_payload":1000000} [] []
[2026-02-17T16:24:59.222516+07:00] elephant.io.INFO: Starting namespace connect [] []
[2026-02-17T16:24:59.222761+07:00] elephant.io.DEBUG: Send data: 40 [] []
[2026-02-17T16:24:59.224671+07:00] elephant.io.DEBUG: Stream write: POST /socket.io/?EIO=4&transport=polling&t=Pngwitu.0&sid=pAVq_mgNxE_stKEZAAAo HTTP/1.1 [] []
[2026-02-17T16:24:59.224942+07:00] elephant.io.DEBUG: Stream write: Host: localhost:14000 [] []
[2026-02-17T16:24:59.225190+07:00] elephant.io.DEBUG: Stream write: Content-Type: text/plain; charset=UTF-8 [] []
[2026-02-17T16:24:59.225300+07:00] elephant.io.DEBUG: Stream write: Content-Length: 2 [] []
[2026-02-17T16:24:59.225431+07:00] elephant.io.DEBUG: Stream write: Accept: */* [] []
[2026-02-17T16:24:59.225733+07:00] elephant.io.DEBUG: Stream write: Origin: http://localhost:14000 [] []
[2026-02-17T16:24:59.225874+07:00] elephant.io.DEBUG: Stream write: Referer: http://localhost:14000 [] []
[2026-02-17T16:24:59.226168+07:00] elephant.io.DEBUG: Stream write: User-Agent: Elephant.io/* [] []
[2026-02-17T16:24:59.226436+07:00] elephant.io.DEBUG: Stream write: Connection: keep-alive [] []
[2026-02-17T16:24:59.226606+07:00] elephant.io.DEBUG: Stream write:  [] []
[2026-02-17T16:24:59.226901+07:00] elephant.io.DEBUG: Stream write: 40 [] []
[2026-02-17T16:24:59.236039+07:00] elephant.io.DEBUG: Stream receive: HTTP/1.1 200 OK [] []
[2026-02-17T16:24:59.252071+07:00] elephant.io.DEBUG: Stream receive: Content-Type: text/html [] []
[2026-02-17T16:24:59.267444+07:00] elephant.io.DEBUG: Stream receive: Content-Length: 2 [] []
[2026-02-17T16:24:59.283220+07:00] elephant.io.DEBUG: Stream receive: cache-control: no-store [] []
[2026-02-17T16:24:59.298189+07:00] elephant.io.DEBUG: Stream receive: Date: Tue, 17 Feb 2026 09:24:59 GMT [] []
[2026-02-17T16:24:59.313030+07:00] elephant.io.DEBUG: Stream receive: Connection: keep-alive [] []
[2026-02-17T16:24:59.328057+07:00] elephant.io.DEBUG: Stream receive: Keep-Alive: timeout=5 [] []
[2026-02-17T16:24:59.343057+07:00] elephant.io.DEBUG: Stream receive:  [] []
[2026-02-17T16:24:59.358039+07:00] elephant.io.DEBUG: Stream receive: ok [] []
[2026-02-17T16:24:59.359774+07:00] elephant.io.DEBUG: Stream write: GET /socket.io/?EIO=4&transport=polling&t=Pngwitu.1&sid=pAVq_mgNxE_stKEZAAAo HTTP/1.1 [] []
[2026-02-17T16:24:59.359984+07:00] elephant.io.DEBUG: Stream write: Host: localhost:14000 [] []
[2026-02-17T16:24:59.360150+07:00] elephant.io.DEBUG: Stream write: Accept: */* [] []
[2026-02-17T16:24:59.360304+07:00] elephant.io.DEBUG: Stream write: Origin: http://localhost:14000 [] []
[2026-02-17T16:24:59.360489+07:00] elephant.io.DEBUG: Stream write: Referer: http://localhost:14000 [] []
[2026-02-17T16:24:59.360608+07:00] elephant.io.DEBUG: Stream write: User-Agent: Elephant.io/* [] []
[2026-02-17T16:24:59.360721+07:00] elephant.io.DEBUG: Stream write: Connection: keep-alive [] []
[2026-02-17T16:24:59.360853+07:00] elephant.io.DEBUG: Stream write:  [] []
[2026-02-17T16:24:59.360942+07:00] elephant.io.DEBUG: Stream write:  [] []
[2026-02-17T16:24:59.373300+07:00] elephant.io.DEBUG: Stream receive: HTTP/1.1 200 OK [] []
[2026-02-17T16:24:59.388119+07:00] elephant.io.DEBUG: Stream receive: Content-Type: text/plain; charset=UTF-8 [] []
[2026-02-17T16:24:59.403471+07:00] elephant.io.DEBUG: Stream receive: Content-Length: 32 [] []
[2026-02-17T16:24:59.418185+07:00] elephant.io.DEBUG: Stream receive: cache-control: no-store [] []
[2026-02-17T16:24:59.433110+07:00] elephant.io.DEBUG: Stream receive: Date: Tue, 17 Feb 2026 09:24:59 GMT [] []
[2026-02-17T16:24:59.448199+07:00] elephant.io.DEBUG: Stream receive: Connection: keep-alive [] []
[2026-02-17T16:24:59.463049+07:00] elephant.io.DEBUG: Stream receive: Keep-Alive: timeout=5 [] []
[2026-02-17T16:24:59.478167+07:00] elephant.io.DEBUG: Stream receive:  [] []
[2026-02-17T16:24:59.492953+07:00] elephant.io.DEBUG: Stream receive: 40{"sid":"nqB7NFvxROKAAWphAAAp"} [] []
[2026-02-17T16:24:59.493259+07:00] elephant.io.DEBUG: Got data: 40{"sid":"nqB7NFvxROKAAWphAAAp"} [] []
[2026-02-17T16:24:59.493477+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"connect","data":{"sid":"nqB7NFvxROKAAWphAAAp"}} [] []
[2026-02-17T16:24:59.493622+07:00] elephant.io.INFO: Namespace connect completed [] []
[2026-02-17T16:24:59.493760+07:00] elephant.io.INFO: Starting websocket upgrade [] []
[2026-02-17T16:24:59.495574+07:00] elephant.io.DEBUG: Stream write: GET /socket.io/?EIO=4&transport=websocket&t=Pngwitu.2&sid=pAVq_mgNxE_stKEZAAAo HTTP/1.1 [] []
[2026-02-17T16:24:59.495811+07:00] elephant.io.DEBUG: Stream write: Host: localhost:14000 [] []
[2026-02-17T16:24:59.495891+07:00] elephant.io.DEBUG: Stream write: Accept: */* [] []
[2026-02-17T16:24:59.495955+07:00] elephant.io.DEBUG: Stream write: Origin: http://localhost:14000 [] []
[2026-02-17T16:24:59.496019+07:00] elephant.io.DEBUG: Stream write: Referer: http://localhost:14000 [] []
[2026-02-17T16:24:59.496080+07:00] elephant.io.DEBUG: Stream write: User-Agent: Elephant.io/* [] []
[2026-02-17T16:24:59.496176+07:00] elephant.io.DEBUG: Stream write: Connection: Upgrade [] []
[2026-02-17T16:24:59.496331+07:00] elephant.io.DEBUG: Stream write: Upgrade: websocket [] []
[2026-02-17T16:24:59.496400+07:00] elephant.io.DEBUG: Stream write: Sec-WebSocket-Key: rlz2rvgLBdRstODVSbyiRw== [] []
[2026-02-17T16:24:59.496492+07:00] elephant.io.DEBUG: Stream write: Sec-WebSocket-Version: 13 [] []
[2026-02-17T16:24:59.496585+07:00] elephant.io.DEBUG: Stream write:  [] []
[2026-02-17T16:24:59.496657+07:00] elephant.io.DEBUG: Stream write:  [] []
[2026-02-17T16:24:59.508501+07:00] elephant.io.DEBUG: Stream receive: HTTP/1.1 101 Switching Protocols [] []
[2026-02-17T16:24:59.524335+07:00] elephant.io.DEBUG: Stream receive: Upgrade: websocket [] []
[2026-02-17T16:24:59.539651+07:00] elephant.io.DEBUG: Stream receive: Connection: Upgrade [] []
[2026-02-17T16:24:59.555340+07:00] elephant.io.DEBUG: Stream receive: Sec-WebSocket-Accept: uFR0rd2EMc06ivDpg+oYRb6z9sc= [] []
[2026-02-17T16:24:59.570882+07:00] elephant.io.DEBUG: Stream receive:  [] []
[2026-02-17T16:24:59.571618+07:00] elephant.io.DEBUG: Send data: 2probe [] []
[2026-02-17T16:24:59.572498+07:00] elephant.io.DEBUG: Stream write: ���� x��Rܦ [] []
[2026-02-17T16:24:59.585428+07:00] elephant.io.DEBUG: Stream receive: � [] []
[2026-02-17T16:24:59.585688+07:00] elephant.io.DEBUG: Stream receive: 3probe [] []
[2026-02-17T16:24:59.586200+07:00] elephant.io.DEBUG: Got data: 3probe [] []
[2026-02-17T16:24:59.586451+07:00] elephant.io.INFO: Got packet: PONG{"data":"probe"} [] []
[2026-02-17T16:24:59.586589+07:00] elephant.io.DEBUG: Got PONG [] []
[2026-02-17T16:24:59.586731+07:00] elephant.io.DEBUG: Send data: 5 [] []
[2026-02-17T16:24:59.587077+07:00] elephant.io.DEBUG: Stream write: ���P�#� [] []
[2026-02-17T16:24:59.601466+07:00] elephant.io.INFO: Websocket upgrade is completed [] []
[2026-02-17T16:24:59.601839+07:00] elephant.io.INFO: Connected to server [] []
[2026-02-17T16:24:59.601980+07:00] elephant.io.INFO: Setting namespace {"namespace":"/keep-alive"} []
[2026-02-17T16:24:59.602115+07:00] elephant.io.DEBUG: Send data: 40/keep-alive [] []
[2026-02-17T16:24:59.602342+07:00] elephant.io.DEBUG: Stream write: ����[f��t ��+K��2� [] []
[2026-02-17T16:24:59.617021+07:00] elephant.io.DEBUG: Stream receive: �, [] []
[2026-02-17T16:24:59.617259+07:00] elephant.io.DEBUG: Stream receive: 40/keep-alive,{"sid":"hxrdjP9DhimdOHsSAAAq"} [] []
[2026-02-17T16:24:59.617376+07:00] elephant.io.DEBUG: Got data: 40/keep-alive,{"sid":"hxrdjP9DhimdOHsSAAAq"} [] []
[2026-02-17T16:24:59.617501+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"connect","nsp":"/keep-alive","data":{"sid":"hxrdjP9DhimdOHsSAAAq"}} [] []
[2026-02-17T16:24:59.617588+07:00] elephant.io.INFO: Emitting a new event {"event":"message","args":"{\"message\":\"A message\"}"} []
[2026-02-17T16:24:59.617945+07:00] elephant.io.DEBUG: Send data: 42/keep-alive,["message",{"message":"A message"}] [] []
[2026-02-17T16:24:59.618131+07:00] elephant.io.DEBUG: Stream write: ��O8��{ ��*]��.T��*��"]��._��cC��*K��(]�my��*K��(]�� [] []
[2026-02-17T16:24:59.632553+07:00] elephant.io.INFO: Waiting for event {"event":"message"} []
[2026-02-17T16:24:59.632859+07:00] elephant.io.DEBUG: Stream receive: �* [] []
[2026-02-17T16:24:59.633047+07:00] elephant.io.DEBUG: Stream receive: 42/keep-alive,["message",{"success":true}] [] []
[2026-02-17T16:24:59.633649+07:00] elephant.io.DEBUG: Got data: 42/keep-alive,["message",{"success":true}] [] []
[2026-02-17T16:24:59.633843+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"event","nsp":"/keep-alive","event":"message","args":[{"success":true}]} [] []
[2026-02-17T16:24:59.757252+07:00] elephant.io.DEBUG: Stream receive: � [] []
[2026-02-17T16:24:59.757652+07:00] elephant.io.DEBUG: Stream receive: 42["hello"] [] []
[2026-02-17T16:24:59.757837+07:00] elephant.io.DEBUG: Got data: 42["hello"] [] []
[2026-02-17T16:24:59.758000+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"event","event":"hello","args":{}} [] []
[2026-02-17T16:25:24.105792+07:00] elephant.io.DEBUG: Stream receive: � [] []
[2026-02-17T16:25:24.106239+07:00] elephant.io.DEBUG: Sending ping to server [] []
[2026-02-17T16:25:24.123743+07:00] elephant.io.DEBUG: Stream receive: 2 [] []
[2026-02-17T16:25:24.124279+07:00] elephant.io.DEBUG: Got data: 2 [] []
[2026-02-17T16:25:24.125289+07:00] elephant.io.INFO: Got packet: PING{} [] []
[2026-02-17T16:25:24.126232+07:00] elephant.io.DEBUG: Got PING, sending PONG [] []
[2026-02-17T16:25:24.126704+07:00] elephant.io.DEBUG: Send data: 3 [] []
[2026-02-17T16:25:24.127734+07:00] elephant.io.DEBUG: Stream write: ��]���n [] []
[2026-02-17T16:25:30.140550+07:00] elephant.io.INFO: Emitting a new event {"event":"message","args":"{\"message\":\"Last message\"}"} []
[2026-02-17T16:25:30.140923+07:00] elephant.io.DEBUG: Send data: 42/keep-alive,["message",{"message":"Last message"}] [] []
[2026-02-17T16:25:30.141760+07:00] elephant.io.DEBUG: Stream write: ��@m]�t_r�%-�!4�%A�-.�! 8�l�%.�'�b!<�4M0�3<�%O � [] []
[2026-02-17T16:25:30.151231+07:00] elephant.io.INFO: Waiting for event {"event":"message"} []
[2026-02-17T16:25:30.151839+07:00] elephant.io.DEBUG: Stream receive: �* [] []
[2026-02-17T16:25:30.152179+07:00] elephant.io.DEBUG: Stream receive: 42/keep-alive,["message",{"success":true}] [] []
[2026-02-17T16:25:30.152601+07:00] elephant.io.DEBUG: Got data: 42/keep-alive,["message",{"success":true}] [] []
[2026-02-17T16:25:30.152979+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"event","nsp":"/keep-alive","event":"message","args":[{"success":true}]} [] []
[2026-02-17T16:25:30.178157+07:00] elephant.io.INFO: Closing connection to server [] []
[2026-02-17T16:25:30.178585+07:00] elephant.io.DEBUG: Send data: 1 [] []
[2026-02-17T16:25:30.179237+07:00] elephant.io.DEBUG: Stream write: ��V��g [] []
```

## Examples

The [the example directory](/example) shows how to get a basic knowledge of library usage.

## Special Thanks

Special thanks goes to Mark Karpeles who helped the project founder to understand the way websockets works.
