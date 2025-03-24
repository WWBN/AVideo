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

namespace ElephantIO\Engine;

use ElephantIO\Util;
use InvalidArgumentException;

/**
 * A key-value store used to store key-value data such as
 * session or packet.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Store
{
    public const IDENTITY = '+';
    public const PRIV = '_';
    public const EXCLUSIVE = '!';

    /**
     * Store keys, a key can be prefixed with flag:
     * - `+` to indicate an identifier,
     * - `_` to indicate a private key which will not be included when cast to string, or
     * - '!' to indicate mutually exclusive key (won't included if other was included)
     *
     * @var string[]
     */
    protected $keys = [];

    /**
     * Normalized keys and flags.
     *
     * @var mixed[]
     */
    protected $nkeys = [];

    /**
     * Store values.
     *
     * @var mixed[]
     */
    protected $values = [];

    /**
     * Key flags.
     *
     * @var string[]
     */
    protected $flags = [self::IDENTITY, self::PRIV, self::EXCLUSIVE];

    /**
     * Values mapping.
     *
     * @var string[]
     */
    protected $maps = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initialize();
        foreach ($this->keys as $k) {
            $key = $this->getNormalizedKey($k);
            $flag = in_array($f = substr($k, 0, 1), $this->flags) ? $f : null;
            $this->nkeys[$key] = $flag; 
        }
    }

    protected function initialize()
    {
    }

    /**
     * Set values mapping.
     *
     * @param array $maps
     * @return \ElephantIO\Engine\Store
     */
    public function setMaps($maps)
    {
        $this->maps = $maps;

        return $this;
    }

    /**
     * Get key and check its validity.
     *
     * @param string $key
     * @throws \InvalidArgumentException
     */
    protected function getKey($key)
    {
        if (in_array($key, array_keys($this->nkeys))) {
            return $key;
        }

        throw new InvalidArgumentException(sprintf(
            'Unexpected key %s, they are %s!',
            $key,
            implode(', ', array_keys($this->nkeys))
        ));
    }

    /**
     * Get normalized key without flag.
     *
     * @param string $key
     * @return string
     */
    protected function getNormalizedKey($key)
    {
        return in_array(substr($key, 0, 1), $this->flags) ? substr($key, 1) : $key;
    }

    /**
     * Get mapped value.
     *
     * @param string $key
     * @param mixed $value
     * @return string
     */
    protected function getMappedValue($key, $value)
    {
        return isset($this->maps[$key]) ? $this->maps[$key][$value] : $value;
    }

    /**
     * Inspect data.
     *
     * @param array $data
     * @return string
     */
    public function inspect($data = null)
    {
        $data = null === $data && isset($this->data) ? $this->data : $data;

        return Util::toStr($data);
    }

    /**
     * Export key-value as array.
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach (array_keys($this->nkeys) as $key) {
            if (isset($this->values[$key])) {
                $result[$key] = $this->$key;
            }
        }

        return $result;
    }

    /**
     * Set key-value from array.
     *
     * @param array $array
     * @return \ElephantIO\Engine\Store
     */
    public function fromArray($array)
    {
        foreach (array_keys($this->nkeys) as $key) {
            if (isset($array[$key])) {
                $this->$key = $array[$key];
            }
        }

        return $this;
    }

    /**
     * Get value.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $key = $this->getKey($key);

        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    /**
     * Set value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $key = $this->getKey($key);
        $this->values[$key] = $value;
    }

    /**
     * Check if key exists.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        $key = $this->getKey($key);

        return isset($this->values[$key]);
    }

    public function __toString()
    {
        $title = null;
        $items = [];
        $xclusive = null;
        foreach ($this->nkeys as $key => $flag) {
            switch ($flag) {
                case static::PRIV:
                    break;
                case static::IDENTITY:
                    $title = $this->getMappedValue($key, $this->$key);
                    break;
                default:
                    if (isset($this->values[$key])) {
                        $value = $this->getMappedValue($key, $this->$key);
                        if (null !== $value && ($flag !== static::EXCLUSIVE || null === $xclusive)) {
                            $items[$key] = $value;
                            if ($flag === static::EXCLUSIVE) {
                                $xclusive = true;
                            }
                        }
                    }
                    break;
            }
        }
        if (null === $title) {
            $clazz = get_class($this);
            $title = substr($clazz, strrpos($clazz, '\\') + 1);
        }

        return sprintf('%s%s', strtoupper($title), Util::toStr($items));
    }

    /**
     * Create a key value store.
     *
     * @param array $keyValuePair
     * @return \ElephantIO\Engine\Store
     */
    public static function create($keyValuePair)
    {
        $store = new static();
        $store->fromArray($keyValuePair);

        return $store;
    }
}
