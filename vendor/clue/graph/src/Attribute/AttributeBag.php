<?php

namespace Fhaculty\Graph\Attribute;

/**
 * Interface to container that represents multiple attributes
 */
interface AttributeBag extends AttributeAware
{
    // public function getAttribute($name, $default);
    // public function setAttribute($name, $value);
    // public function getAttributeBag();

    /**
     * set an array of additional attributes
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes);

    /**
     * get an array of all attributes
     *
     * @return array
     */
    public function getAttributes();
}
