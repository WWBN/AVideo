<?php

/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

require __DIR__ . '/common.php';

$namespace = 'header-auth';
$token = 'this_is_peter_token';
$event = 'message';

$logger = setup_logger();

// create first instance
$client = setup_client($namespace, $logger, [
    'headers' => [
        'X-My-Header' => 'websocket rocks',
        'Authorization' => 'Bearer ' . $token,
        'User' => 'peter',
    ]
]);

$data = [
    'message' => 'How are you?',
    'token' => $token,
];
echo sprintf("Sending message: %s\n", inspect($data));
$client->emit($event, $data);
if ($retval = $client->wait($event)) {
    echo sprintf("Got a reply: %s\n", $retval->inspect());
}
$client->disconnect();

// create second instance
$client = setup_client($namespace, $logger, [
    'headers' => [
        'X-My-Header' => 'websocket rocks',
        'Authorization' => 'Bearer ' . $token,
        'User' => 'peter',
    ]
]);

$data = [
    'message' => 'Do you remember me?',
    'token' => $token,
];
echo sprintf("Sending message: %s\n", inspect($data));
$client->emit($event, $data);
if ($retval = $client->wait($event)) {
    echo sprintf("Got a reply: %s\n", $retval->inspect());
}

// send message with invalid token
$invalidToken = 'this_is_invalid_peter_token';
$data = [
    'message' => 'Do you remember me?',
    'token' => $invalidToken,
];
echo sprintf("Sending message: %s\n", inspect($data));
$client->emit($event, $data);
if ($retval = $client->wait($event)) {
    echo sprintf("Got a reply: %s\n", $retval->inspect());
}
$client->disconnect();
