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

$url = 'http://localhost:14001';
$namespace = 'error-handling';
$namespace_not_exist = 'non-existent-namespace';

$logger = setup_logger();

try {
    echo sprintf("Try connecting to %s...\n", $url);
    $client = setup_client($namespace, $logger, ['url' => $url]);
} catch (\ElephantIO\Exception\SocketException $e) {
    echo sprintf("> Expected connection failure:\n%s\n\n", $e->getMessage());
}

try {
    echo "Try connecting to non existent namespace...\n";
    $client = setup_client($namespace_not_exist, $logger);
} catch (\ElephantIO\Exception\UnsuccessfulOperationException $e) {
    echo sprintf("> Expected operation failure:\n%s\n\n", $e->getMessage());
}

try {
    echo sprintf("Try connecting to %s...", $namespace);
    $client = setup_client($namespace, $logger);
    echo "connected\n";
    echo "Sending message...";
    $client->emit('message', ['message' => 'The message']);
    $timeout = 5;
    if (null === ($result = $client->wait('message-reply', $timeout))) {
        echo sprintf("no reply after %d seconds!\n\n", $timeout);
    }
    $client->disconnect();
} catch (\Exception $e) {
    echo sprintf("%s:\n", get_class($e));
    echo $e->getMessage()."\n\n";
}
