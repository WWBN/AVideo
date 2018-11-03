# Changelog

## 0.8.3 (2017-09-08)

*   Feature: Reduce memory consumption for failed connections
    (#113 by @valga)

*   Fix: Work around write chunk size for TLS streams for PHP < 7.1.14
    (#114 by @clue)

## 0.8.2 (2017-08-25)

*   Feature: Update DNS dependency to support hosts file on all platforms
    (#112 by @clue)

    This means that connecting to hosts such as `localhost` will now work as
    expected across all platforms with no changes required:

    ```php
    $connector = new Connector($loop);
    $connector->connect('localhost:8080')->then(function ($connection) {
        // …
    });
    ```

## 0.8.1 (2017-08-15)

* Feature: Forward compatibility with upcoming EventLoop v1.0 and v0.5 and
  target evenement 3.0 a long side 2.0 and 1.0
  (#104 by @clue and #111 by @WyriHaximus)

* Improve test suite by locking Travis distro so new defaults will not break the build and
  fix HHVM build for now again and ignore future HHVM build errors
  (#109 and #110 by @clue)

* Minor documentation fixes
  (#103 by @christiaan and #108 by @hansott)

## 0.8.0 (2017-05-09)

* Feature: New `Server` class now acts as a facade for existing server classes
  and renamed old `Server` to `TcpServer` for advanced usage.
  (#96 and #97 by @clue)

  The `Server` class is now the main class in this package that implements the
  `ServerInterface` and allows you to accept incoming streaming connections,
  such as plaintext TCP/IP or secure TLS connection streams.

  > This is not a BC break and consumer code does not have to be updated.

* Feature / BC break: All addresses are now URIs that include the URI scheme
  (#98 by @clue)

  ```diff
  - $parts = parse_url('tcp://' . $conn->getRemoteAddress());
  + $parts = parse_url($conn->getRemoteAddress());
  ```

* Fix: Fix `unix://` addresses for Unix domain socket (UDS) paths
  (#100 by @clue)

* Feature: Forward compatibility with Stream v1.0 and v0.7
  (#99 by @clue)

## 0.7.2 (2017-04-24)

* Fix: Work around latest PHP 7.0.18 and 7.1.4 no longer accepting full URIs
  (#94 by @clue)

## 0.7.1 (2017-04-10)

* Fix: Ignore HHVM errors when closing connection that is already closing
  (#91 by @clue)

## 0.7.0 (2017-04-10)

* Feature: Merge SocketClient component into this component
  (#87 by @clue)

  This means that this package now provides async, streaming plaintext TCP/IP
  and secure TLS socket server and client connections for ReactPHP.

  ```
  $connector = new React\Socket\Connector($loop);
  $connector->connect('google.com:80')->then(function (ConnectionInterface $conn) {
      $connection->write('…');
  });
  ```

  Accordingly, the `ConnectionInterface` is now used to represent both incoming
  server side connections as well as outgoing client side connections.

  If you've previously used the SocketClient component to establish outgoing
  client connections, upgrading should take no longer than a few minutes.
  All classes have been merged as-is from the latest `v0.7.0` release with no
  other changes, so you can simply update your code to use the updated namespace
  like this:

  ```php
  // old from SocketClient component and namespace
  $connector = new React\SocketClient\Connector($loop);
  $connector->connect('google.com:80')->then(function (ConnectionInterface $conn) {
      $connection->write('…');
  });

  // new
  $connector = new React\Socket\Connector($loop);
  $connector->connect('google.com:80')->then(function (ConnectionInterface $conn) {
      $connection->write('…');
  });
  ```

## 0.6.0 (2017-04-04)

* Feature: Add `LimitingServer` to limit and keep track of open connections
  (#86 by @clue)

  ```php
  $server = new Server(0, $loop);
  $server = new LimitingServer($server, 100);

  $server->on('connection', function (ConnectionInterface $connection) {
      $connection->write('hello there!' . PHP_EOL);
      …
  });
  ```

* Feature / BC break: Add `pause()` and `resume()` methods to limit active
  connections
  (#84 by @clue)

  ```php
  $server = new Server(0, $loop);
  $server->pause();

  $loop->addTimer(1.0, function() use ($server) {
      $server->resume();
  });
  ```

## 0.5.1 (2017-03-09)

* Feature: Forward compatibility with Stream v0.5 and upcoming v0.6
  (#79 by @clue)

## 0.5.0 (2017-02-14)

* Feature / BC break: Replace `listen()` call with URIs passed to constructor
  and reject listening on hostnames with `InvalidArgumentException`
  and replace `ConnectionException` with `RuntimeException` for consistency
  (#61, #66 and #72 by @clue)

  ```php
  // old
  $server = new Server($loop);
  $server->listen(8080);

  // new
  $server = new Server(8080, $loop);
  ```

  Similarly, you can now pass a full listening URI to the constructor to change
  the listening host:

  ```php
  // old
  $server = new Server($loop);
  $server->listen(8080, '127.0.0.1');

  // new
  $server = new Server('127.0.0.1:8080', $loop);
  ```

  Trying to start listening on (DNS) host names will now throw an
  `InvalidArgumentException`, use IP addresses instead:

  ```php
  // old
  $server = new Server($loop);
  $server->listen(8080, 'localhost');

  // new
  $server = new Server('127.0.0.1:8080', $loop);
  ```

  If trying to listen fails (such as if port is already in use or port below
  1024 may require root access etc.), it will now throw a `RuntimeException`,
  the `ConnectionException` class has been removed:

  ```php
  // old: throws React\Socket\ConnectionException
  $server = new Server($loop);
  $server->listen(80);

  // new: throws RuntimeException
  $server = new Server(80, $loop);
  ```

* Feature / BC break: Rename `shutdown()` to `close()` for consistency throughout React
  (#62 by @clue)

  ```php
  // old
  $server->shutdown();

  // new
  $server->close();
  ```

* Feature / BC break: Replace `getPort()` with `getAddress()`
  (#67 by @clue)

  ```php
  // old
  echo $server->getPort(); // 8080

  // new
  echo $server->getAddress(); // 127.0.0.1:8080
  ```

* Feature / BC break: `getRemoteAddress()` returns full address instead of only IP
  (#65 by @clue)

  ```php
  // old
  echo $connection->getRemoteAddress(); // 192.168.0.1

  // new
  echo $connection->getRemoteAddress(); // 192.168.0.1:51743
  ```
  
* Feature / BC break: Add `getLocalAddress()` method
  (#68 by @clue)

  ```php
  echo $connection->getLocalAddress(); // 127.0.0.1:8080
  ```

* BC break: The `Server` and `SecureServer` class are now marked `final`
  and you can no longer `extend` them
  (which was never documented or recommended anyway).
  Public properties and event handlers are now internal only.
  Please use composition instead of extension.
  (#71, #70 and #69 by @clue)

## 0.4.6 (2017-01-26)

* Feature: Support socket context options passed to `Server`
  (#64 by @clue)

* Fix: Properly return `null` for unknown addresses
  (#63 by @clue)

* Improve documentation for `ServerInterface` and lock test suite requirements
  (#60 by @clue, #57 by @shaunbramley)

## 0.4.5 (2017-01-08)

* Feature: Add `SecureServer` for secure TLS connections
  (#55 by @clue)

* Add functional integration tests
  (#54 by @clue)

## 0.4.4 (2016-12-19)

* Feature / Fix: `ConnectionInterface` should extend `DuplexStreamInterface` + documentation
  (#50 by @clue)

* Feature / Fix: Improve test suite and switch to normal stream handler
  (#51 by @clue)

* Feature: Add examples
  (#49 by @clue)

## 0.4.3 (2016-03-01)

* Bug fix: Suppress errors on stream_socket_accept to prevent PHP from crashing
* Support for PHP7 and HHVM
* Support PHP 5.3 again

## 0.4.2 (2014-05-25)

* Verify stream is a valid resource in Connection

## 0.4.1 (2014-04-13)

* Bug fix: Check read buffer for data before shutdown signal and end emit (@ArtyDev)
* Bug fix: v0.3.4 changes merged for v0.4.1

## 0.3.4 (2014-03-30)

* Bug fix: Reset socket to non-blocking after shutting down (PHP bug)

## 0.4.0 (2014-02-02)

* BC break: Bump minimum PHP version to PHP 5.4, remove 5.3 specific hacks
* BC break: Update to React/Promise 2.0
* BC break: Update to Evenement 2.0
* Dependency: Autoloading and filesystem structure now PSR-4 instead of PSR-0
* Bump React dependencies to v0.4

## 0.3.3 (2013-07-08)

* Version bump

## 0.3.2 (2013-05-10)

* Version bump

## 0.3.1 (2013-04-21)

* Feature: Support binding to IPv6 addresses (@clue)

## 0.3.0 (2013-04-14)

* Bump React dependencies to v0.3

## 0.2.6 (2012-12-26)

* Version bump

## 0.2.3 (2012-11-14)

* Version bump

## 0.2.0 (2012-09-10)

* Bump React dependencies to v0.2

## 0.1.1 (2012-07-12)

* Version bump

## 0.1.0 (2012-07-11)

* First tagged release
