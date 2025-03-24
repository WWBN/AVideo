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

namespace ElephantIO\Parser\Polling;

use ArrayObject;
use ElephantIO\Engine\SocketIO;
use ElephantIO\SequenceReader;
use RuntimeException;

/**
 * Decode the payload from HTTP response.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Decoder extends ArrayObject
{
    public const EIO_V1_SEPARATOR = "\u{fffd}";
    public const EIO_V4_SEPARATOR = "\x1e";

    /**
     * Constructor.
     *
     * @param string $data Payload data
     * @param int $eio Engine IO version
     * @param bool $binary True if data is using binary encoding
     */
    public function __construct($data, $eio, $binary = null)
    {
        $lines = [];
        $checksum = null;
        $seq = new SequenceReader($data);
        while (!$seq->isEof()) {
            $len = null;
            $skip = null;
            switch ($eio) {
                case SocketIO::EIO_V4:
                    if (false !== ($len = $seq->getDelimited(static::EIO_V4_SEPARATOR))) {
                        $skip = mb_strlen(static::EIO_V4_SEPARATOR);
                    } else {
                        $len = mb_strlen($seq->getData());
                    }
                    break;
                case SocketIO::EIO_V3:
                case SocketIO::EIO_V2:
                    if ($binary) {
                        $signature = $seq->read();
                        if (in_array($signature, ["\x00", "\x01"])) {
                            $len = 0;
                            $sizes = $seq->readUntil("\xff");
                            $n = mb_strlen($sizes) - 1;
                            for ($i = 0; $i <= $n; $i++) {
                                $len += ord($sizes[$i]) * pow(10, $n - $i);
                            }
                        } else {
                            throw new RuntimeException('Unsupported encoding detected!');
                        }
                    } else {
                        $len = (int) $seq->readUntil(':');
                    }
                    break;
                case SocketIO::EIO_V1:
                    if (false !== ($len = $seq->getDelimited(static::EIO_V1_SEPARATOR))) {
                        $skip = mb_strlen(static::EIO_V1_SEPARATOR);
                    } else {
                        $len = mb_strlen($seq->getData());
                    }
                    break;
            }
            if (null === $len) {
                throw new RuntimeException('Data delimiter not found!');
            }
            if ($line = $seq->read($len)) {
                if ($eio === SocketIO::EIO_V1 && $skip) {
                    if ((string) (int) $line === $line) {
                        $checksum = (int) $line;
                        $line = null;
                    } else {
                        if ($len !== $checksum) {
                            throw new RuntimeException(sprintf('Invalid size checksum, got %d while expecting %d!', $len, $checksum));
                        }
                        $checksum = null;
                    }
                }
                if ($line) {
                    $lines[] = $line;
                }
            }
            if ($skip) {
                $seq->read($skip);
            }
        }
        parent::__construct($lines);
    }

    public function __toString()
    {
        return implode('', (array) $this);
    }
}
