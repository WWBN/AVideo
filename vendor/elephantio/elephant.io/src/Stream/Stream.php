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

namespace ElephantIO\Stream;

use ElephantIO\SocketUrl;
use InvalidArgumentException;
use Psr\Log\NullLogger;

/**
 * Stream provides abstraction for socket client stream.
 *
 * @author Toha <tohenk@yahoo.com>
 */
abstract class Stream implements StreamInterface
{
    /**
     * @var \ElephantIO\SocketUrl
     */
    protected $url = null;

    /**
     * @var array
     */
    protected $context = null;

    /**
     * @var array
     */
    protected $options = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;

    /**
     * Constructor.
     *
     * @param string $url
     * @param array $context
     * @param array $options
     */
    public function __construct($url, $context = [], $options = [])
    {
        $this->context = $context;
        $this->options = $options;
        $this->logger = isset($options['logger']) && $options['logger'] ? $options['logger'] : new NullLogger();
        $this->url = new SocketUrl($url);
        if (isset($options['sio_path'])) {
            $this->url->setSioPath($options['sio_path']);
        }
        $this->initialize();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Initialize.
     */
    protected function initialize()
    {
    }

    /**
     * Create socket stream.
     *
     * @return \ElephantIO\Stream\StreamInterface
     */
    public static function create($url, $context = [], $options = [])
    {
        $class = SocketStream::class;
        if (isset($options['stream_factory'])) {
            $class = $options['stream_factory'];
            unset($options['stream_factory']);
        }
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Socket stream class %s not found!', $class));
        }
        $clazz = new $class($url, $context, $options);
        if (!$clazz instanceof StreamInterface) {
            throw new InvalidArgumentException(sprintf('Class %s must implmenet StreamInterface!', $class));
        }

        return $clazz;
    }
}
