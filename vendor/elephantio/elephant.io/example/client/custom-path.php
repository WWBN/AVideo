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

$options = ['path' => 'my', 'sio_path' => 'my.io'];
echo sprintf("Connecting to custom path /%s...\n", implode('/', [$options['path'], $options['sio_path']]));
$client = setup_client(null, null, $options);
while (true) {
    if ($packet = $client->wait(null, 1)) {
        echo sprintf("Got event %s\n", $packet->event);
        break;
    }
}
$client->disconnect();
