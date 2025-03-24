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

$namespace = 'handshake-auth';
$event = 'echo';

$logger = setup_logger();

// create first instance
$client = setup_client($namespace, $logger, [
    'auth' => [
        'user' => 'random@example.com',
        'token' => 'my-secret-token',
    ]
]);

$data = [
    'message' => 'Hello!'
];
echo sprintf("Sending message: %s\n", inspect($data));
$client->emit($event, $data);
if ($retval = $client->wait($event)) {
    echo sprintf("Got a reply: %s\n", $retval->inspect());
}
$client->disconnect();

try {
    // create second instance
    $client = setup_client($namespace, $logger, [
        'auth' => [
            'user' => 'random@example.com',
            'password' => 'my-wrong-secret-password',
        ]
    ]);
} catch (\ElephantIO\Exception\UnsuccessfulOperationException $e) {
    echo sprintf("Got expected authentication failure: %s\n", $e->getMessage());
}
