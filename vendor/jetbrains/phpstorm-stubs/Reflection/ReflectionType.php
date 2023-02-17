<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;

/**
 * The ReflectionType class reports information about a function's parameters.
 *
 * @link https://www.php.net/manual/en/class.reflectiontype.php
 * @since 7.0
 */
abstract class ReflectionType implements Stringable
{
    /**
     * Checks if null is allowed
     *
     * @link https://php.net/manual/en/reflectiontype.allowsnull.php
     * @return bool Returns {@see true} if {@see null} is allowed, otherwise {@see false}
     * @since 7.0
     */
    public function allowsNull()
    {
    }

    /**
     * Checks if it is a built-in type
     *
     * @link https://php.net/manual/en/reflectiontype.isbuiltin.php
     * @return bool Returns {@see true} if it's a built-in type, otherwise {@see false}
     * @since 7.0
     * @removed 8.0 this method has been removed from the {@see ReflectionType}
     * class and moved to the {@see ReflectionNamedType} child.
     */
    #[Pure]
	public function isBuiltin()
    {
    }

    /**
     * To string
     *
     * @link https://php.net/manual/en/reflectiontype.tostring.php
     * @return string Returns the type of the parameter.
     * @since 7.0
     * @see ReflectionType::getName()
     */
    #[Deprecated(replacement: "%class$->getName()", since: "7.1")]
    public function __toString()
    {
    }

    /**
     * Cloning of this class is prohibited
     *
     * @return void
     */
    final private function __clone()
    {
    }
}
