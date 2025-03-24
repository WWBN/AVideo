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
    lq p    |     /         .|   Requires PHP 7.2 and openssl, licensed under
 _   \. .-, l    /          |j   the MIT License
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
[2024-12-03T10:30:23.411250+07:00] elephant.io.INFO: Connecting to server [] []
[2024-12-03T10:30:23.415186+07:00] elephant.io.INFO: Starting handshake [] []
[2024-12-03T10:30:23.422134+07:00] elephant.io.INFO: Stream connect: tcp://localhost:14000 [] []
[2024-12-03T10:30:23.446182+07:00] elephant.io.DEBUG: Stream write: GET /socket.io/?EIO=4&transport=polling&t=PEAaQoO HTTP/1.1 [] []
[2024-12-03T10:30:23.446295+07:00] elephant.io.DEBUG: Stream write: Host: localhost:14000 [] []
[2024-12-03T10:30:23.446364+07:00] elephant.io.DEBUG: Stream write: Accept: */* [] []
[2024-12-03T10:30:23.446427+07:00] elephant.io.DEBUG: Stream write: Origin: http://localhost:14000 [] []
[2024-12-03T10:30:23.446490+07:00] elephant.io.DEBUG: Stream write: Referer: http://localhost:14000 [] []
[2024-12-03T10:30:23.447176+07:00] elephant.io.DEBUG: Stream write: User-Agent: Elephant.io/* [] []
[2024-12-03T10:30:23.447222+07:00] elephant.io.DEBUG: Stream write: Connection: keep-alive [] []
[2024-12-03T10:30:23.447258+07:00] elephant.io.DEBUG: Stream write:  [] []
[2024-12-03T10:30:23.447293+07:00] elephant.io.DEBUG: Stream write:  [] []
[2024-12-03T10:30:23.447412+07:00] elephant.io.DEBUG: Stream receive: HTTP/1.1 200 OK [] []
[2024-12-03T10:30:23.449274+07:00] elephant.io.DEBUG: Stream receive: Content-Type: text/plain; charset=UTF-8 [] []
[2024-12-03T10:30:23.465707+07:00] elephant.io.DEBUG: Stream receive: Content-Length: 118 [] []
[2024-12-03T10:30:23.483395+07:00] elephant.io.DEBUG: Stream receive: cache-control: no-store [] []
[2024-12-03T10:30:23.496174+07:00] elephant.io.DEBUG: Stream receive: Date: Tue, 03 Dec 2024 03:30:23 GMT [] []
[2024-12-03T10:30:23.512515+07:00] elephant.io.DEBUG: Stream receive: Connection: keep-alive [] []
[2024-12-03T10:30:23.526952+07:00] elephant.io.DEBUG: Stream receive: Keep-Alive: timeout=5 [] []
[2024-12-03T10:30:23.543116+07:00] elephant.io.DEBUG: Stream receive:  [] []
[2024-12-03T10:30:23.557217+07:00] elephant.io.DEBUG: Stream receive: 0{"sid":"8Nm1b2IA5gY5rQc8AAA3","upgrades":["websocket"],"pingInterval":25000,"pingTimeout":20000,"ma... 18 more [] []
[2024-12-03T10:30:23.559910+07:00] elephant.io.INFO: Got packet: OPEN{"data":{"sid":"8Nm1b2IA5gY5rQc8AAA3","upgrades":["websocket"],"pingInterval":25000,"pingTimeout... 30 more [] []
[2024-12-03T10:30:23.560596+07:00] elephant.io.INFO: Handshake finished with SESSION{"id":"8Nm1b2IA5gY5rQc8AAA3","upgrades":["websocket"],"timeouts":{"interval":25,"timeout":20},"max_payload":1000000} [] []
[2024-12-03T10:30:23.560667+07:00] elephant.io.INFO: Starting namespace connect [] []
[2024-12-03T10:30:23.560742+07:00] elephant.io.DEBUG: Send data: 40 [] []
[2024-12-03T10:30:23.563214+07:00] elephant.io.DEBUG: Stream write: POST /socket.io/?EIO=4&transport=polling&t=PEAaQoO.0&sid=8Nm1b2IA5gY5rQc8AAA3 HTTP/1.1 [] []
[2024-12-03T10:30:23.563278+07:00] elephant.io.DEBUG: Stream write: Host: localhost:14000 [] []
[2024-12-03T10:30:23.563337+07:00] elephant.io.DEBUG: Stream write: Content-Type: text/plain; charset=UTF-8 [] []
[2024-12-03T10:30:23.563375+07:00] elephant.io.DEBUG: Stream write: Content-Length: 2 [] []
[2024-12-03T10:30:23.563411+07:00] elephant.io.DEBUG: Stream write: Accept: */* [] []
[2024-12-03T10:30:23.563447+07:00] elephant.io.DEBUG: Stream write: Origin: http://localhost:14000 [] []
[2024-12-03T10:30:23.563483+07:00] elephant.io.DEBUG: Stream write: Referer: http://localhost:14000 [] []
[2024-12-03T10:30:23.563518+07:00] elephant.io.DEBUG: Stream write: User-Agent: Elephant.io/* [] []
[2024-12-03T10:30:23.563554+07:00] elephant.io.DEBUG: Stream write: Connection: keep-alive [] []
[2024-12-03T10:30:23.563590+07:00] elephant.io.DEBUG: Stream write:  [] []
[2024-12-03T10:30:23.563625+07:00] elephant.io.DEBUG: Stream write: 40 [] []
[2024-12-03T10:30:23.572832+07:00] elephant.io.DEBUG: Stream receive: HTTP/1.1 200 OK [] []
[2024-12-03T10:30:23.589823+07:00] elephant.io.DEBUG: Stream receive: Content-Type: text/html [] []
[2024-12-03T10:30:23.607117+07:00] elephant.io.DEBUG: Stream receive: Content-Length: 2 [] []
[2024-12-03T10:30:23.624568+07:00] elephant.io.DEBUG: Stream receive: cache-control: no-store [] []
[2024-12-03T10:30:23.641448+07:00] elephant.io.DEBUG: Stream receive: Date: Tue, 03 Dec 2024 03:30:23 GMT [] []
[2024-12-03T10:30:23.654475+07:00] elephant.io.DEBUG: Stream receive: Connection: keep-alive [] []
[2024-12-03T10:30:23.671094+07:00] elephant.io.DEBUG: Stream receive: Keep-Alive: timeout=5 [] []
[2024-12-03T10:30:23.687724+07:00] elephant.io.DEBUG: Stream receive:  [] []
[2024-12-03T10:30:23.701835+07:00] elephant.io.DEBUG: Stream receive: ok [] []
[2024-12-03T10:30:23.707538+07:00] elephant.io.DEBUG: Stream write: GET /socket.io/?EIO=4&transport=polling&t=PEAaQoO.1&sid=8Nm1b2IA5gY5rQc8AAA3 HTTP/1.1 [] []
[2024-12-03T10:30:23.708175+07:00] elephant.io.DEBUG: Stream write: Host: localhost:14000 [] []
[2024-12-03T10:30:23.708227+07:00] elephant.io.DEBUG: Stream write: Accept: */* [] []
[2024-12-03T10:30:23.708271+07:00] elephant.io.DEBUG: Stream write: Origin: http://localhost:14000 [] []
[2024-12-03T10:30:23.708314+07:00] elephant.io.DEBUG: Stream write: Referer: http://localhost:14000 [] []
[2024-12-03T10:30:23.708356+07:00] elephant.io.DEBUG: Stream write: User-Agent: Elephant.io/* [] []
[2024-12-03T10:30:23.708397+07:00] elephant.io.DEBUG: Stream write: Connection: keep-alive [] []
[2024-12-03T10:30:23.708438+07:00] elephant.io.DEBUG: Stream write:  [] []
[2024-12-03T10:30:23.708480+07:00] elephant.io.DEBUG: Stream write:  [] []
[2024-12-03T10:30:23.708556+07:00] elephant.io.DEBUG: Stream receive: HTTP/1.1 200 OK [] []
[2024-12-03T10:30:23.716208+07:00] elephant.io.DEBUG: Stream receive: Content-Type: text/plain; charset=UTF-8 [] []
[2024-12-03T10:30:23.734031+07:00] elephant.io.DEBUG: Stream receive: Content-Length: 32 [] []
[2024-12-03T10:30:23.750497+07:00] elephant.io.DEBUG: Stream receive: cache-control: no-store [] []
[2024-12-03T10:30:23.763387+07:00] elephant.io.DEBUG: Stream receive: Date: Tue, 03 Dec 2024 03:30:23 GMT [] []
[2024-12-03T10:30:23.778788+07:00] elephant.io.DEBUG: Stream receive: Connection: keep-alive [] []
[2024-12-03T10:30:23.794481+07:00] elephant.io.DEBUG: Stream receive: Keep-Alive: timeout=5 [] []
[2024-12-03T10:30:23.812968+07:00] elephant.io.DEBUG: Stream receive:  [] []
[2024-12-03T10:30:23.826331+07:00] elephant.io.DEBUG: Stream receive: 40{"sid":"_aFhqr1LHU2MRdKDAAA4"} [] []
[2024-12-03T10:30:23.826532+07:00] elephant.io.DEBUG: Got data: 40{"sid":"_aFhqr1LHU2MRdKDAAA4"} [] []
[2024-12-03T10:30:23.826885+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"connect","data":{"sid":"_aFhqr1LHU2MRdKDAAA4"}} [] []
[2024-12-03T10:30:23.828350+07:00] elephant.io.INFO: Namespace connect completed [] []
[2024-12-03T10:30:23.828425+07:00] elephant.io.INFO: Starting websocket upgrade [] []
[2024-12-03T10:30:23.831412+07:00] elephant.io.DEBUG: Stream write: GET /socket.io/?EIO=4&transport=websocket&t=PEAaQoO.2&sid=8Nm1b2IA5gY5rQc8AAA3 HTTP/1.1 [] []
[2024-12-03T10:30:23.831642+07:00] elephant.io.DEBUG: Stream write: Host: localhost:14000 [] []
[2024-12-03T10:30:23.831702+07:00] elephant.io.DEBUG: Stream write: Accept: */* [] []
[2024-12-03T10:30:23.831752+07:00] elephant.io.DEBUG: Stream write: Origin: http://localhost:14000 [] []
[2024-12-03T10:30:23.831816+07:00] elephant.io.DEBUG: Stream write: Referer: http://localhost:14000 [] []
[2024-12-03T10:30:23.831863+07:00] elephant.io.DEBUG: Stream write: User-Agent: Elephant.io/* [] []
[2024-12-03T10:30:23.831910+07:00] elephant.io.DEBUG: Stream write: Connection: Upgrade [] []
[2024-12-03T10:30:23.831957+07:00] elephant.io.DEBUG: Stream write: Upgrade: websocket [] []
[2024-12-03T10:30:23.832003+07:00] elephant.io.DEBUG: Stream write: Sec-WebSocket-Key: tvJrt14ZlOUsUmZkVtfbwQ== [] []
[2024-12-03T10:30:23.832049+07:00] elephant.io.DEBUG: Stream write: Sec-WebSocket-Version: 13 [] []
[2024-12-03T10:30:23.832096+07:00] elephant.io.DEBUG: Stream write:  [] []
[2024-12-03T10:30:23.832169+07:00] elephant.io.DEBUG: Stream write:  [] []
[2024-12-03T10:30:23.832488+07:00] elephant.io.DEBUG: Stream receive: HTTP/1.1 101 Switching Protocols [] []
[2024-12-03T10:30:23.840403+07:00] elephant.io.DEBUG: Stream receive: Upgrade: websocket [] []
[2024-12-03T10:30:23.856259+07:00] elephant.io.DEBUG: Stream receive: Connection: Upgrade [] []
[2024-12-03T10:30:23.872012+07:00] elephant.io.DEBUG: Stream receive: Sec-WebSocket-Accept: 8T8DIpjXSya4+fdMK79B5lTFVRc= [] []
[2024-12-03T10:30:23.887705+07:00] elephant.io.DEBUG: Stream receive:  [] []
[2024-12-03T10:30:23.888097+07:00] elephant.io.DEBUG: Send data: 5 [] []
[2024-12-03T10:30:23.895113+07:00] elephant.io.DEBUG: Stream write: ��b��}W [] []
[2024-12-03T10:30:23.905939+07:00] elephant.io.INFO: Websocket upgrade completed [] []
[2024-12-03T10:30:23.906141+07:00] elephant.io.INFO: Connected to server [] []
[2024-12-03T10:30:23.906191+07:00] elephant.io.INFO: Setting namespace {"namespace":"/keep-alive"} []
[2024-12-03T10:30:23.906418+07:00] elephant.io.DEBUG: Send data: 40/keep-alive [] []
[2024-12-03T10:30:23.907493+07:00] elephant.io.DEBUG: Stream write: ��s�G� x�>�fe [] []
[2024-12-03T10:30:23.921051+07:00] elephant.io.DEBUG: Stream receive: �, [] []
[2024-12-03T10:30:23.921206+07:00] elephant.io.DEBUG: Stream receive: 40/keep-alive,{"sid":"LxfT7oR0lfSW84n2AAA5"} [] []
[2024-12-03T10:30:23.925691+07:00] elephant.io.DEBUG: Got data: 40/keep-alive,{"sid":"LxfT7oR0lfSW84n2AAA5"} [] []
[2024-12-03T10:30:23.925953+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"connect","nsp":"/keep-alive","data":{"sid":"LxfT7oR0lfSW84n2AAA5"}} [] []
[2024-12-03T10:30:23.926031+07:00] elephant.io.INFO: Emitting a new event {"event":"message","args":"{\"message\":\"A message\"}"} []
[2024-12-03T10:30:23.926088+07:00] elephant.io.DEBUG: Send data: 42/keep-alive,["message",{"message":"A message"}] [] []
[2024-12-03T10:30:24.037538+07:00] elephant.io.DEBUG: Stream write: ��sU�Gg?�0`�9y�yK�0c�2u�_.2�&c�02�Q0�&c�02�. [] []
[2024-12-03T10:30:24.065934+07:00] elephant.io.INFO: Waiting for event {"event":"message"} []
[2024-12-03T10:30:24.066080+07:00] elephant.io.DEBUG: Stream receive: �* [] []
[2024-12-03T10:30:24.066153+07:00] elephant.io.DEBUG: Stream receive: 42/keep-alive,["message",{"success":true}] [] []
[2024-12-03T10:30:24.066227+07:00] elephant.io.DEBUG: Got data: 42/keep-alive,["message",{"success":true}] [] []
[2024-12-03T10:30:24.066436+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"event","nsp":"/keep-alive","event":"message","args":[{"success":true}]} [] []
[2024-12-03T10:30:24.197804+07:00] elephant.io.DEBUG: Stream receive: � [] []
[2024-12-03T10:30:24.198260+07:00] elephant.io.DEBUG: Stream receive: 42["hello"] [] []
[2024-12-03T10:30:24.198358+07:00] elephant.io.DEBUG: Got data: 42["hello"] [] []
[2024-12-03T10:30:24.198463+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"event","event":"hello","args":{}} [] []
[2024-12-03T10:30:44.198371+07:00] elephant.io.DEBUG: Sending ping to server [] []
[2024-12-03T10:30:48.489374+07:00] elephant.io.DEBUG: Stream receive: � [] []
[2024-12-03T10:30:48.489543+07:00] elephant.io.DEBUG: Stream receive: 2 [] []
[2024-12-03T10:30:48.489628+07:00] elephant.io.DEBUG: Got data: 2 [] []
[2024-12-03T10:30:48.489715+07:00] elephant.io.INFO: Got packet: PING{} [] []
[2024-12-03T10:30:48.489779+07:00] elephant.io.DEBUG: Got PING, sending PONG [] []
[2024-12-03T10:30:48.489822+07:00] elephant.io.DEBUG: Send data: 3 [] []
[2024-12-03T10:30:48.490681+07:00] elephant.io.DEBUG: Stream write: ���?��� [] []
[2024-12-03T10:30:54.512668+07:00] elephant.io.INFO: Emitting a new event {"event":"message","args":"{\"message\":\"Last message\"}"} []
[2024-12-03T10:30:54.513095+07:00] elephant.io.DEBUG: Send data: 42/keep-alive,["message",{"message":"Last message"}] [] []
[2024-12-03T10:30:54.513442+07:00] elephant.io.DEBUG: Stream write: ��Y�u�m�Z�<��8��<�.�4��8��u�W�<��>�W�{��-��*��<�� [] []
[2024-12-03T10:30:54.525615+07:00] elephant.io.INFO: Waiting for event {"event":"message"} []
[2024-12-03T10:30:54.525732+07:00] elephant.io.DEBUG: Stream receive: �* [] []
[2024-12-03T10:30:54.525804+07:00] elephant.io.DEBUG: Stream receive: 42/keep-alive,["message",{"success":true}] [] []
[2024-12-03T10:30:54.525890+07:00] elephant.io.DEBUG: Got data: 42/keep-alive,["message",{"success":true}] [] []
[2024-12-03T10:30:54.526005+07:00] elephant.io.INFO: Got packet: MESSAGE{"type":"event","nsp":"/keep-alive","event":"message","args":[{"success":true}]} [] []
[2024-12-03T10:30:54.526635+07:00] elephant.io.INFO: Closing connection to server [] []
[2024-12-03T10:30:54.526791+07:00] elephant.io.DEBUG: Send data: 1 [] []
[2024-12-03T10:30:54.527160+07:00] elephant.io.DEBUG: Stream write: ��H�~�y [] []
```

## Examples

The [the example directory](/example) shows how to get a basic knowledge of library usage.

## Special Thanks

Special thanks goes to Mark Karpeles who helped the project founder to understand the way websockets works.
