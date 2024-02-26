<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * The <b>ReflectionProperty</b> class reports information about a classes
 * properties.
 *
 * @link https://php.net/manual/en/class.reflectionproperty.php
 */
class ReflectionProperty implements Reflector
{

    /**
     * @var string Name of the property, same as calling the {@see ReflectionProperty::getName()} method
     */
    #[Immutable]
    public $name;

    /**
     * @var string Fully qualified class name where this property was defined
     */
    #[Immutable]
    public $class;

    /**
     * Indicates that the property is static.
     *
     * @link https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.is-static
     */
    const IS_STATIC = 16;

    /**
     * Indicates that the property is public.
     *
     * @link https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.is-public
     */
    const IS_PUBLIC = 1;

    /**
     * Indicates that the property is protected.
     *
     * @link https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.is-protected
     */
    const IS_PROTECTED = 2;

    /**
     * Indicates that the property is private.
     *
     * @link https://www.php.net/manual/en/class.reflectionproperty.php#reflectionproperty.constants.is-private
     */
    const IS_PRIVATE = 4;

    /**
     * Construct a ReflectionProperty object
     *
     * @link https://php.net/manual/en/reflectionproperty.construct.php
     * @param string|object $class The class name, that contains the property.
     * @param string $property The name of the property being reflected.
     * @throws \ReflectionException if the class or property does not exist.
     */
    public function __construct($class, $property)
    {
    }

    /**
     * Export
     *
     * @link https://php.net/manual/en/reflectionproperty.export.php
     * @param mixed $class The reflection to export.
     * @param string $name The property name.
     * @param bool $return Setting to {@see true} will return the export, as
     * opposed to emitting it. Setting to {@see false} (the default) will do the
     * opposite.
     * @return string|null
     * @removed 8.0
     */
    #[Deprecated(since: '7.4')]
    public static function export($class, $name, $return = false)
    {
    }

    /**
     * To string
     *
     * @link https://php.net/manual/en/reflectionproperty.tostring.php
     * @return string
     */
    public function __toString()
    {
    }

    /**
     * Gets property name
     *
     * @link https://php.net/manual/en/reflectionproperty.getname.php
     * @return string The name of the reflected property.
     */
    #[Pure]
	public function getName()
    {
    }

    /**
     * Gets value
     *
     * @link https://php.net/manual/en/reflectionproperty.getvalue.php
     * @param object|null $object If the property is non-static an object must be
     * provided to fetch the property from. If you want to fetch the default
     * property without providing an object use {@see ReflectionClass::getDefaultProperties}
     * instead.
     * </p>
     * @return mixed The current value of the property.
     */
    #[Pure]
	public function getValue($object = null)
    {
    }

    /**
     * Set property value
     *
     * @link https://php.net/manual/en/reflectionproperty.setvalue.php
     * @param mixed $objectOrValue If the property is non-static an object must
     * be provided to change the property on. If the property is static this
     * parameter is left out and only $value needs to be provided.
     * @param mixed $value The new value.
     * @return void No value is returned.
     */
    public function setValue($objectOrValue, $value = null)
    {
    }

    /**
     * Checks if property is public
     *
     * @link https://php.net/manual/en/reflectionproperty.ispublic.php
     * @return bool Return {@see true} if the property is public, {@see false} otherwise.
     */
    #[Pure]
	public function isPublic()
    {
    }

    /**
     * Checks if property is private
     *
     * @link https://php.net/manual/en/reflectionproperty.isprivate.php
     * @return bool Return {@see true} if the property is private, {@see false} otherwise.
     */
    #[Pure]
	public function isPrivate()
    {
    }

    /**
     * Checks if property is protected
     *
     * @link https://php.net/manual/en/reflectionproperty.isprotected.php
     * @return bool Returns {@see true} if the property is protected, {@see false} otherwise.
     */
    #[Pure]
	public function isProtected()
    {
    }

    /**
     * Checks if property is static
     *
     * @link https://php.net/manual/en/reflectionproperty.isstatic.php
     * @return bool Retruns {@see true} if the property is static, {@see false} otherwise.
     */
    #[Pure]
	public function isStatic()
    {
    }

    /**
     * Checks if default value
     *
     * @link https://php.net/manual/en/reflectionproperty.isdefault.php
     * @return bool Returns {@see true} if the property was declared at
     * compile-time, or {@see false} if it was created at run-time.
     */
    #[Pure]
	public function isDefault()
    {
    }

    /**
     * Gets modifiers
     *
     * @link https://php.net/manual/en/reflectionproperty.getmodifiers.php
     * @return int A numeric representation of the modifiers.
     */
    #[Pure]
	public function getModifiers()
    {
    }

    /**
     * Gets declaring class
     *
     * @link https://php.net/manual/en/reflectionproperty.getdeclaringclass.php
     * @return ReflectionClass A {@see ReflectionClass} object.
     */
    #[Pure]
	public function getDeclaringClass()
    {
    }

    /**
     * Gets doc comment
     *
     * @link https://php.net/manual/en/reflectionproperty.getdoccomment.php
     * @return string|false The doc comment if it exists, otherwise {@see false}
     */
    #[Pure]
	public function getDocComment()
    {
    }

    /**
     * Set property accessibility
     *
     * @link https://php.net/manual/en/reflectionproperty.setaccessible.php
     * @param bool $accessible A boolean {@see true} to allow accessibility, or {@see false}
     * @return void No value is returned.
     */
    public function setAccessible($accessible)
    {
    }

    /**
     * Gets property type
     *
     * @link https://php.net/manual/en/reflectionproperty.gettype.php
     * @return ReflectionType|null Returns a {@see ReflectionType} if the
     * property has a type, and {@see null} otherwise.
     * @since 7.4
     */
    #[Pure]
	public function getType()
    {
    }

    /**
     * Checks if property has type
     *
     * @link https://php.net/manual/en/reflectionproperty.hastype.php
     * @return bool Returns {@see true} if a type is specified, {@see false} otherwise.
     * @since 7.4
     */
    public function hasType()
    {
    }

    /**
     * Checks if property is initialized
     *
     * @link https://php.net/manual/en/reflectionproperty.isinitialized.php
     * @param object|null $object If the property is non-static an object must be provided to fetch the property from.
     * @return bool Returns {@see false} for typed properties prior to initialization, and for properties that have
     * been explicitly {@see unset()}. For all other properties {@see true} will be returned.
     * @since 7.4
     */
    #[Pure]
	public function isInitialized($object = null)
    {
    }

    /**
     * Returns information about whether the property was promoted.
     *
     * @return bool Returns {@see true} if the property was promoted or {@see false} instead.
     * @since 8.0
     */
    #[Pure]
	public function isPromoted()
    {
    }

    /**
     * Clone
     *
     * @link https://php.net/manual/en/reflectionproperty.clone.php
     * @return void
     */
    final private function __clone()
    {
    }

    /**
     * @return bool
     * @since 8.0
     */
    public function hasDefaultValue(){}

    /**
     * @return mixed
     * @since 8.0
     */
    #[Pure]
	public function getDefaultValue(){}

    /**
     * @return ReflectionAttribute[]
     * @since 8.0
     */
    #[Pure]
	public function getAttributes(?string $name = null, int $flags = 0): array {}
}
