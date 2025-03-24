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

$namespace = 'polling';
$event = 'message';

// only enable polling transport
$client = setup_client($namespace, null, ['transports' => 'polling']);

$client->emit($event, ['message' => 'This is first message']);
if ($retval = $client->wait($event)) {
    echo sprintf("Got a reply for first message: %s\n", $retval->inspect());
}
$client->emit($event, ['message' => 'Das höchste Gut und Uebel']);
if ($retval = $client->wait($event)) {
    echo sprintf("Got a reply for second message: %s\n", $retval->inspect());
}
$client->disconnect();
