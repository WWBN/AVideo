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

use ElephantIO\Engine\Argument;

require __DIR__ . '/common.php';

$logger = setup_logger();

foreach ([
    'basic event' => [],
    'event without reuse connection' => ['reuse_connection' => false],
] as $type => $options) {
    echo sprintf("Listening %s...\n", $type);
    $client = setup_client(null, $logger, $options);
    while (true) {
        if ($packet = $client->wait(null, 1)) {
            echo sprintf("Got event %s\n", $packet->event);
            $client->emit('test', new Argument(1, 2, 'test'));
            if ($packet = $client->wait('test')) {
                echo sprintf("Got test: %s\n", inspect($packet->args));
                break;
            }
        }
    }
    $client->disconnect();
}
