<?php

namespace Pecee\Http\Input;

class InputItem implements IInputItem
{
    public $index;
    public $name;
    public $value;

    public function __construct($index, $value = null)
    {
        $this->index = $index;
        $this->value = $value;

        // Make the name human friendly, by replace _ with space
        $this->name = ucfirst(str_replace('_', ' ', strtolower($this->index)));
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set input name
     * @param string $name
     * @return static $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set input value
     * @param string $value
     * @return static $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function __toString()
    {
        return (string)$this->value;
    }

}