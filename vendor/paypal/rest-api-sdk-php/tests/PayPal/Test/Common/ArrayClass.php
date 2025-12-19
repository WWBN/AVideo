<?php
namespace PayPal\Test\Common;

use PayPal\Common\PayPalModel;

class ArrayClass extends PayPalModel
{

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setTags($tags)
    {
        if (!is_array($tags)) {
            $tags = array($tags);
        }
        $this->tags = $tags;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }
}
