<?php

namespace React\Socket;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Stream\DuplexResourceStream;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;
use React\Stream\WritableResourceStream;

/**
 * The actual connection implementation for ConnectionInterface
 *
 * This class should only be used internally, see ConnectionInterface instead.
 *
 * @see ConnectionInterface
 * @internal
 */
class Connection extends EventEmitter implements ConnectionInterface
{
    /**
     * Internal flag whether this is a Unix domain socket (UDS) connection
     *
     * @internal
     */
    public $unix = false;

    /**
     * Internal flag whether encryption has been enabled on this connection
     *
     * Mostly used by internal StreamEncryption so that connection returns
     * `tls://` scheme for encrypted connections instead of `tcp://`.
     *
     * @internal
     */
    public $encryptionEnabled = false;

    /** @internal */
    public $stream;

    private $input;

    public function __construct($resource, LoopInterface $loop)
    {
        // PHP < 5.6.8 suffers from a buffer indicator bug on secure TLS connections
        // as a work-around we always read the complete buffer until its end.
        // The buffer size is limited due to TCP/IP buffers anyway, so this
        // should not affect usage otherwise.
        // See https://bugs.php.net/bug.php?id=65137
        // https://bugs.php.net/bug.php?id=41631
        // https://github.com/reactphp/socket-client/issues/24
        $clearCompleteBuffer = PHP_VERSION_ID < 50608;

        // PHP < 7.1.4 (and PHP < 7.0.18) suffers from a bug when writing big
        // chunks of data over TLS streams at once.
        // We try to work around this by limiting the write chunk size to 8192
        // bytes for older PHP versions only.
        // This is only a work-around and has a noticable performance penalty on
        // affected versions. Please update your PHP version.
        // This applies to all streams because TLS may be enabled later on.
        // See https://github.com/reactphp/socket/issues/105
        $limitWriteChunks = (PHP_VERSION_ID < 70018 || (PHP_VERSION_ID >= 70100 && PHP_VERSION_ID < 70104));

        $this->input = new DuplexResourceStream(
            $resource,
            $loop,
            $clearCompleteBuffer ? -1 : null,
            new WritableResourceStream($resource, $loop, null, $limitWriteChunks ? 8192 : null)
        );

        $this->stream = $resource;

        Util::forwardEvents($this->input, $this, array('data', 'end', 'error', 'close', 'pipe', 'drain'));

        $this->input->on('close', array($this, 'close'));
    }

    public function isReadable()
    {
        return $this->input->isReadable();
    }

    public function isWritable()
    {
        return $this->input->isWritable();
    }

    public function pause()
    {
        $this->input->pause();
    }

    public function resume()
    {
        $this->input->resume();
    }

    public function pipe(WritableStreamInterface $dest, array $options = array())
    {
        return $this->input->pipe($dest, $options);
    }

    public function write($data)
    {
        return $this->input->write($data);
    }

    public function end($data = null)
    {
        $this->input->end($data);
    }

    public function close()
    {
        $this->input->close();
        $this->handleClose();
        $this->removeAllListeners();
    }

    public function handleClose()
    {
        if (!is_resource($this->stream)) {
            return;
        }

        // Try to cleanly shut down socket and ignore any errors in case other
        // side already closed. Shutting down may return to blocking mode on
        // some legacy versions, so reset to non-blocking just in case before
        // continuing to close the socket resource.
        @stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
        stream_set_blocking($this->stream, false);
        fclose($this->stream);
    }

    public function getRemoteAddress()
    {
        return $this->parseAddress(@stream_socket_get_name($this->stream, true));
    }

    public function getLocalAddress()
    {
        return $this->parseAddress(@stream_socket_get_name($this->stream, false));
    }

    private function parseAddress($address)
    {
        if ($address === false) {
            return null;
        }

        if ($this->unix) {
            // remove trailing colon from address for HHVM < 3.19: https://3v4l.org/5C1lo
            // note that technically ":" is a valid address, so keep this in place otherwise
            if (substr($address, -1) === ':' && defined('HHVM_VERSION_ID') && HHVM_VERSION_ID < 31900) {
                $address = (string)substr($address, 0, -1);
            }

            // work around unknown addresses should return null value: https://3v4l.org/5C1lo
            // PHP uses "\0" string and HHVM uses empty string (colon removed above)
            if ($address === "\x00" || $address === '') {
                return null;
            }

            return 'unix://' . $address;
        }

        // check if this is an IPv6 address which includes multiple colons but no square brackets
        $pos = strrpos($address, ':');
        if ($pos !== false && strpos($address, ':') < $pos && substr($address, 0, 1) !== '[') {
            $port = substr($address, $pos + 1);
            $address = '[' . substr($address, 0, $pos) . ']:' . $port;
        }

        return ($this->encryptionEnabled ? 'tls' : 'tcp') . '://' . $address;
    }
}
