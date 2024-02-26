<?php

namespace Fhaculty\Graph\Attribute;

/**
 * An attribute bag that automatically prefixes a given namespace.
 *
 * For example, you can use this class to prefix the attributes using a vendor
 * name, like "myvendor.item.".  If another vendor shares the base attribute
 * bag, it can use a different prefix, like "otherProduct.item.".  This allows
 * both libraries to have attributes with the same name without having them
 * conflict.  For example, the attribute "id" would be stored separately as
 * "myvendor.item.id" and "otherProduct.item.id".
 */
class AttributeBagNamespaced implements AttributeBag
{
    /**
     * @var AttributeBag
     */
    private $bag;

    /**
     * @var string
     */
    private $prefix;

    /**
     * Initialize the attribute bag with a prefix to use as a namespace for the attributes.
     *
     * @param AttributeAware $bag    The bag to store the prefixed attributes in.
     * @param string         $prefix The prefix to prepend to all attributes before
     *                               storage.  This prefix acts as a namespace to separate attributes.
     */
    public function __construct(AttributeAware $bag, $prefix)
    {
        if (!($bag instanceof AttributeBag)) {
            $bag = $bag->getAttributeBag();
        }
        $this->bag = $bag;
        $this->prefix = $prefix;
    }

    /**
     * get a single attribute with the given $name (or return $default if attribute was not found)
     *
     * This prefixes the attribute name before requesting from the base bag.
     *
     * @param string $name
     * @param mixed  $default to return if attribute was not found
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->bag->getAttribute($this->prefix . $name, $default);
    }

    /**
     * set a single attribute with the given $name to given $value
     *
     * This prefixes the attribute name before setting in the base bag.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function setAttribute($name, $value)
    {
        $this->bag->setAttribute($this->prefix . $name, $value);
    }

    /**
     * get an array of all attributes
     *
     * The prefix will not be included in the returned attribute keys.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = array();
        $len = strlen($this->prefix);

        foreach ($this->bag->getAttributes() as $name => $value) {
            if (strpos($name, $this->prefix) === 0) {
                $attributes[substr($name, $len)] = $value;
            }
        }

        return $attributes;
    }

    /**
     * set an array of additional attributes
     *
     * Each attribute is prefixed before setting in the base bag.
     *
     * @param  array $attributes
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->bag->setAttribute($this->prefix . $name, $value);
        }
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
