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

$namespace = 'keep-alive';
$event = 'message';

$client = setup_client($namespace);

$timeout = 30; // in seconds
$start = microtime(true);
$sent = null;
while (true) {
    $now = microtime(true);
    if (null === $sent) {
        $sent = $now;
        $client->emit($event, ['message' => 'A message']);
        if ($retval = $client->wait($event)) {
            echo sprintf("Got a reply for first message: %s\n", $retval->inspect());
        }
        continue;
    }
    if ($now - $start >= $timeout) {
        $client->emit($event, ['message' => 'Last message']);
        if ($retval = $client->wait($event)) {
            echo sprintf("\nGot a reply for last message: %s\n", $retval->inspect());
        }
        break;
    }
    $client->drain(1);
    echo '.';
}
$client->disconnect();
