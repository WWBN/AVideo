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

$namespace = 'ack';
$event = 'test-recv-ack';

$client = setup_client($namespace);

echo "Emiting an event with acknowledgement...\n";
if ($retval = $client->emit('test-send-ack', ['message' => 'An ack send test'], true)) {
    echo sprintf("Got ack: %s\n", $retval->inspect());
}

echo "Acknowledge an event...\n";
$client->emit($event, ['message' => 'An ack recv test']);
if ($retval = $client->wait($event)) {
    echo sprintf("Got a reply: %s\n", $retval->inspect());
    if (null !== $retval->ack) {
        echo "Send acknowledgement...\n";
        $client->ack($retval, ["Okay"]);
    }
}

$client->disconnect();
