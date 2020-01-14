<?php

namespace Pecee\Http\Input;

interface IInputItem
{

    public function getIndex();

    public function setIndex($index);

    public function getName();

    public function setName($name);

    public function getValue();

    public function setValue($value);

    public function __toString();

}