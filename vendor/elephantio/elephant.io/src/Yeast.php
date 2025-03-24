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
 * Port of Yeast.
 *
 * @author Toha <tohenk@yahoo.com>
 * @see https://github.com/unshiftio/yeast
 */
class Yeast
{
    /**
     * @var string
     */
    protected $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';

    /**
     * @var integer
     */
    protected $length = 64;

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @var integer
     */
    protected $seed = 0;

    /**
     * @var string
     */
    protected $prev = null;

    /**
     * @var Yeast
     */
    protected static $instance = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        for ($i = 0; $i < $this->length; $i++) {
            $this->map[substr($this->alphabet, $i, 1)] = $i;
        }
    }

    /**
     * Return a string representing the specified number.
     *
     * @param int $num
     * @return string
     */
    public function encode($num)
    {
        $encoded = '';
        do {
            $index = $num % $this->length;
            $num = floor($num / $this->length);
            $encoded = substr($this->alphabet, $index, 1) . $encoded;
        } while ($num > 0);

        return $encoded;
    }

    /**
     * Return the integer value specified by the given string.
     *
     * @param string $str
     * @return int
     */
    public function decode($str)
    {
        $decoded = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $decoded = $decoded * $this->length + $this->map[substr($str, $i, 1)];
        }

        return $decoded;
    }

    /**
     * Yeast: A tiny growing id generator.
     *
     * @return string
     */
    public function generate()
    {
        $now = new \DateTime();
        $now = $now->getTimestamp() * 1000;
        $generated = $this->encode($now);

        if ($this->prev !== $generated) {
            $this->seed = 0;
            $this->prev = $generated;

            return $generated;
        }

        return $generated . '.' . $this->encode($this->seed++);
    }

    /**
     * @see Yeast::generate()
     * @return string
     */
    public static function yeast()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance->generate();
    }
}
