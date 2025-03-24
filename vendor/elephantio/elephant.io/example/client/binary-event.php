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

$namespace = 'binary-event';
$event = 'test-binary';

$logger = setup_logger();

// create binary payload
$filename = __DIR__ . '/../../test/Websocket/data/payload-7d.txt';
$payload = fopen($filename, 'rb');
$bindata = create_resource('1234567890');

foreach ([
    'websocket' => ['transport' => 'websocket'],
    'polling' => ['transports' => 'polling']
] as $transport => $options) {
    echo sprintf("Sending binary data using %s transport...\n", $transport);
    $client = setup_client($namespace, $logger, $options);
    $client->emit($event, ['data1' => ['test' => $payload], 'data2' => $bindata]);
    if ($retval = $client->wait($event)) {
        echo sprintf("Got a reply: %s\n", $retval->inspect());
    }
    $client->disconnect();
}
