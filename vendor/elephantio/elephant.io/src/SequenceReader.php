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

namespace ElephantIO;

/**
 * A sequence data reader.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class SequenceReader
{
    /**
     * @var ?string
     */
    protected $data = null;

    /**
     * Constructor.
     *
     * @param string $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Read a fixed size data or remaining data if size is null.
     *
     * @param ?int $size
     * @return string|null
     */
    public function read($size = 1)
    {
        if (!$this->isEof()) {
            $result = null;
            if (null === $size) {
                $result = $this->data;
                $this->data = '';
            } elseif ($this->data) {
                $result = mb_substr($this->data, 0, $size);
                $this->data = mb_substr($this->data, $size);
            }

            return $result;
        }

        return null;
    }

    /**
     * Read data up to delimiter.
     *
     * @param string $delimiter
     * @param string[] $noskips
     * @return null|string
     */
    public function readUntil($delimiter = ',', $noskips = [])
    {
        if (!$this->isEof() && $this->data) {
            list($p, $d) = $this->getPos($this->data, $delimiter);
            if (is_int($p) && is_string($d)) {
                $result = mb_substr($this->data, 0, $p);
                // skip delimiter
                if (!in_array($d, $noskips)) {
                    $p += mb_strlen($d);
                }
                $this->data = mb_substr($this->data, $p);

                return $result;
            }
        }

        return null;
    }

    /**
     * Read data up to delimiter within boundaries.
     *
     * @param string $delimiter
     * @param string[] $boundaries
     * @return null|string
     */
    public function readWithin($delimiter = ',', $boundaries = [])
    {
        if (!$this->isEof() && $this->data) {
            list($p, $d) = $this->getPos($this->data, implode(array_merge([$delimiter], $boundaries)));
            if (is_int($p) && $d === $delimiter) {
                $result = mb_substr($this->data, 0, $p);
                $this->data = mb_substr($this->data, $p + mb_strlen($d));

                return $result;
            }
        }

        return null;
    }

    /**
     * Get first position of delimiter.
     *
     * @param string $delimiter
     * @return false|int False if delimiter is not found otherwize the position found
     */
    public function getDelimited($delimiter)
    {
        if ($this->data) {
            list($pos, ) = $this->getPos($this->data, $delimiter);
        } else {
            $pos = null;
        }

        return is_int($pos) ? $pos : false;
    }

    /**
     * Get first position of delimiters.
     *
     * @param ?string $data
     * @param ?string $delimiter
     * @return array<int, int|string|null> Index 0 indicate position found or false and index 1 indicate matched delimiter
     */
    protected function getPos($data, $delimiter)
    {
        $pos = null;
        $delim = null;
        if ($data && $delimiter) {
            for ($i = 0; $i < mb_strlen($delimiter); $i++) {
                $d = mb_substr($delimiter, $i, 1);
                $p = mb_strpos($data, $d);
                if (is_int($p)) {
                    if (null === $pos || $p < $pos) {
                        $pos = (int) $p;
                        $delim = $d;
                    }
                }
            }
        }

        return [$pos, $delim];
    }

    /**
     * Read unprocessed data without increase sequence position.
     *
     * @param int $size
     * @return string
     */
    public function readData($size = 1)
    {
        return $this->data ? mb_substr($this->data, 0, $size) : '';
    }

    /**
     * Get unprocessed data.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data ?? '';
    }

    /**
     * Is EOF.
     *
     * @return bool
     */
    public function isEof()
    {
        if (null === $this->data) {
            return true;
        } else {
            return mb_strlen($this->data) > 0 ? false : true;
        }
    }
}
