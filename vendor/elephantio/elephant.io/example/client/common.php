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

use ElephantIO\Client;
use ElephantIO\Util;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LogLevel;

require __DIR__ . '/../../vendor/autoload.php';

error_reporting(E_ALL);
set_error_handler('error_to_exception');

/**
 * Throw exceptions for all unhandled errors, deprecations and warnings while running the examples.
 *
 * @param int $code
 * @param string $message
 * @param string $filename
 * @param int $line
 * @return void
 */
function error_to_exception($code, $message, $filename, $line) {
    if (error_reporting() & $code) {
        throw new ErrorException($message, 0, $code, $filename, $line);
    }
}

/**
 * Get or set client version to use.
 *
 * @param int $version Version to set
 * @return int
 */
function client_version($version = null)
{
    static $client = null;
    if (null === $client && is_readable($package = __DIR__ . '/../server/package.json')) {
        $info = json_decode(file_get_contents($package), true);
        if (isset($info['dependencies']) && isset($info['dependencies']['socket.io'])) {
            if (preg_match('/(?P<MAJOR>(\d+))\.(?P<MINOR>(\d+))\.(?P<PATCH>(\d+))/', $info['dependencies']['socket.io'], $matches)) {
                $client = (int) $matches['MAJOR'];
                echo sprintf("Server version detected: %s\n", $client);
            }
        }
    }
    if (null === $client) {
        $client = Client::CLIENT_4X;
    }
    if (null !== $version) {
        $client = $version;
    }

    return $client;
}

/**
 * Create a logger channel.
 *
 * @return \Monolog\Logger
 */
function setup_logger()
{
    $logfile = __DIR__ . '/socket.log';
    if (is_readable($logfile)) {
        @unlink($logfile);
    }
    $logger = new Logger('elephant.io');
    $logger->pushHandler(new StreamHandler($logfile, LogLevel::DEBUG));

    return $logger;
}

/**
 * Create a socket client.
 *
 * @param string $namespace
 * @param \Monolog\Logger $logger
 * @param array $options
 * @return \ElephantIO\Client
 */
function setup_client($namespace, $logger = null, $options = [])
{
    $url = 'http://localhost:14000';
    if (isset($options['url'])) {
        $url = $options['url'];
        unset($options['url']);
    }
    if (isset($options['path'])) {
        $url .= '/' . $options['path'];
        unset($options['path']);
    }

    $logger = $logger ?? setup_logger();
    $client = Client::create($url, array_merge(['client' => client_version(), 'logger' => $logger], $options));
    $client->connect();
    if ($namespace) {
        $client->of(sprintf('/%s', $namespace));
    }

    return $client;
}

/**
 * Truncate a long string from array value.
 *
 * @param array $data
 * @param integer $len
 * @return void
 */
function truncate_data(&$data, $len = 100)
{
    if (is_array($data)) {
        foreach ($data as $k => &$v) {
            if (is_array($v)) {
                truncate_data($v, $len);
            } elseif (is_string($v)) {
                if (($n = strlen($v)) > $len) {
                    $n -= $len;
                    if ($len > 3) {
                        $v = Util::truncate($v);
                    }
                }
            }
        }
    }
}

/**
 * Inspect data.
 *
 * @param array $data
 * @return string
 */
function inspect($data)
{
    return Util::toStr($data);
}

/**
 * Create a resource from string.
 *
 * @param string $data
 * @return resource
 */
function create_resource($data)
{
    return Util::toResource($data);
}
