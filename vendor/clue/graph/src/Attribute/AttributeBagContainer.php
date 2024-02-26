<?php

namespace Fhaculty\Graph\Attribute;

/**
 * A fairly standard AttributeBag container.
 *
 * This container passes and returns attributes by value.  It is mutable,
 * however, so multiple references to the container will update in kind.
 */
class AttributeBagContainer implements AttributeBag
{
    /**
     * @var array
     */
    private $attributes = array();

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
