<?php
namespace PayPal\Test\Common;

use PayPal\Common\PayPalModel;

class NestedClass extends PayPalModel
{

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param \PayPal\Test\Common\ArrayClass $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     *
     * @return \PayPal\Test\Common\ArrayClass
     */
    public function getInfo()
    {
        return $this->info;
    }
}
