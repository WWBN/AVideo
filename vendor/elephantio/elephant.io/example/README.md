# Elephant.io Example

This examples bellow shows typical Elephant.io usage to connect to socket.io server.

## Available examples

| Description                                 | Server                                                      | Client                                            | 0X | 1X | 2X | 3X | 4X |
|---------------------------------------------|-------------------------------------------------------------|---------------------------------------------------|----|----|----|----|----|
| Acknowledgement                             | [serve-ack.js](./server/serve-ack.js)                       | [ack.php](./client/ack.php)                       | ✅ | ✅ | ✅ | ✅ | ✅ |
| Basic (listening event)                     | [serve-basic.js](./server/serve-basic.js)                   | [basic.php](./client/basic.php)                   | ✅ | ✅ | ✅ | ✅ | ✅ |
| Sending and receiving binary data           | [serve-binary-event.js](./server/serve-binary-event.js)     | [binary-event.php](./client/binary-event.php)     | ✅ | ✅ | ✅ | ✅ | ✅ |
| Error handling                              | [serve-error-handling.js](./server/serve-error-handling.js) | [error-handling.php](./client/error-handling.php) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Authentication using handshake              | [serve-handshake-auth.js](./server/serve-handshake-auth.js) | [handshake-auth.php](./client/handshake-auth.php) | ⛔️ | ⛔️ | ⛔️ | ✅ | ✅ |
| Authentication using `Authorization` header | [serve-header-auth.js](./server/serve-header-auth.js)       | [header-auth.php](./client/header-auth.php)       | ⛔️ | ✅ | ✅ | ✅ | ✅ |
| Keep alive                                  | [serve-keep-alive.js](./server/serve-keep-alive.js)         | [keep-alive.php](./client/keep-alive.php)         | ✅ | ✅ | ✅ | ✅ | ✅ |
| Polling                                     | [serve-polling.js](./server/serve-polling.js)               | [polling.php](./client/polling.php)               | ✅ | ✅ | ✅ | ✅ | ✅ |
| Custom path                                 | [serve2-custom-path.js](./server/serve2-custom-path.js)     | [custom-path.php](./client/custom-path.php)       | ✅ | ✅ | ✅ | ✅ | ✅ |

## Run server part first

Ensure Nodejs already installed on your system, then issue:

```sh
cd server
npm install
npm start
```

## Run actual example

On another terminal, issue:

```sh
cd client
php binary-event.php
```

A log file named `socket.log` will be created upon running the example which
contains the log when connecting to socket server.

## Run example to target specific socket.io version

Install specific version of `socket.io` package, e.g. for socket.io 0x:

```sh
cd server
npm install socket.io@0
```

Run the example as shown above.
