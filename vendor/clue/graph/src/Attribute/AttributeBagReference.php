<?php

namespace Fhaculty\Graph\Attribute;

/**
 * The basic attribute bag, but using a reference to the base attribute array.
 *
 * This container passes and returns attributes by value, but stores them in a
 * pass-by-reference array.  It is mutable, however, so multiple references to
 * the container will update in kind.
 */
class AttributeBagReference implements AttributeBag
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * Initialize the attribute bag with the base attribute array.
     *
     * The given array is pass-by-reference, so updates to the array here or in
     * calling code will be reflected everywhere.
     *
     * @param array $attributes The pass-by-reference attributes.
     */
    public function __construct(array &$attributes)
    {
        $this->attributes =& $attributes;
    }

    /**
     * get a single attribute with the given $name (or return $default if attribute was not found)
     *
     * @param  string $name
     * @param  mixed  $default to return if attribute was not found
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * set a single attribute with the given $name to given $value
     *
     * @param  string $name
     * @param  mixed  $value
     * @return self   For a fluid interface.
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * get an array of all attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * set an array of additional attributes
     *
     * @param  array $attributes
     * @return self  For a fluid interface.
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes + $this->attributes;

        return $this;
    }

    /**
     * get a container for all attributes
     *
     * @return AttributeBag
     */
    public function getAttributeBag()
    {
        return $this;
    }
}
