<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * The <b>ReflectionMethod</b> class reports
 * information about a method.
 *
 * @link https://php.net/manual/en/class.reflectionmethod.php
 */
class ReflectionMethod extends ReflectionFunctionAbstract
{
    /**
     * @var string Name of the method, same as calling the {@see ReflectionMethod::getName()} method
     */
    #[Immutable]
    public $name;

    /**
     * @var string Fully qualified class name where this method was defined
     */
    #[Immutable]
    public $class;

    /**
     * Indicates that the method is static.
     */
    const IS_STATIC = 16;

    /**
     * Indicates that the method is public.
     */
    const IS_PUBLIC = 1;

    /**
     * Indicates that the method is protected.
     */
    const IS_PROTECTED = 2;

    /**
     * Indicates that the method is private.
     */
    const IS_PRIVATE = 4;

    /**
     * Indicates that the method is abstract.
     */
    const IS_ABSTRACT = 64;

    /**
     * Indicates that the method is final.
     */
    const IS_FINAL = 32;

    /**
     * Constructs a ReflectionMethod
     *
     * <code>
     * $reflection = new ReflectionMethod(new Example(), 'method');
     * $reflection = new ReflectionMethod(Example::class, 'method');
     * $reflection = new ReflectionMethod('Example::method');
     * </code>
     *
     * @link https://php.net/manual/en/reflectionmethod.construct.php
     * @param string|object $objectOrMethod Classname, object
     * (instance of the class) that contains the method or class name and
     * method name delimited by ::.
     * @param string|null $method Name of the method if the first argument is a
     * classname or an object.
     * @throws \ReflectionException if the class or method does not exist.
     */
    public function __construct($objectOrMethod, $method = null)
    {
    }

    /**
     * Export a reflection method.
     *
     * @link https://php.net/manual/en/reflectionmethod.export.php
     * @param string $class The class name.
     * @param string $name The name of the method.
     * @param bool $return Setting to {@see true} will return the export,
     * as opposed to emitting it. Setting to {@see false} (the default) will do the
     * opposite.
     * @return string|null If the $return parameter is set to {@see true}, then
     * the export is returned as a string, otherwise {@see null} is returned.
     * @removed 8.0
     */
    #[Deprecated(since: '7.4')]
    public static function export($class, $name, $return = false)
    {
    }

    /**
     * Returns the string representation of the ReflectionMethod object.
     *
     * @link https://php.net/manual/en/reflectionmethod.tostring.php
     * @return string A string representation of this {@see ReflectionMethod} instance.
     */
    public function __toString()
    {
    }

    /**
     * Checks if method is public
     *
     * @link https://php.net/manual/en/reflectionmethod.ispublic.php
     * @return bool Returns {@see true} if the method is public, otherwise {@see false}
     */
    #[Pure]
	public function isPublic()
    {
    }

    /**
     * Checks if method is private
     *
     * @link https://php.net/manual/en/reflectionmethod.isprivate.php
     * @return bool Returns {@see true} if the method is private, otherwise {@see false}
     */
    #[Pure]
	public function isPrivate()
    {
    }

    /**
     * Checks if method is protected
     *
     * @link https://php.net/manual/en/reflectionmethod.isprotected.php
     * @return bool Returns {@see true} if the method is protected, otherwise {@see false}
     */
    #[Pure]
	public function isProtected()
    {
    }

    /**
     * Checks if method is abstract
     *
     * @link https://php.net/manual/en/reflectionmethod.isabstract.php
     * @return bool Returns {@see true} if the method is abstract, otherwise {@see false}
     */
    #[Pure]
	public function isAbstract()
    {
    }

    /**
     * Checks if method is final
     *
     * @link https://php.net/manual/en/reflectionmethod.isfinal.php
     * @return bool Returns {@see true} if the method is final, otherwise {@see false}
     */
    #[Pure]
	public function isFinal()
    {
    }

    /**
     * Checks if method is static
     *
     * @link https://php.net/manual/en/reflectionmethod.isstatic.php
     * @return bool Returns {@see true} if the method is static, otherwise {@see false}
     */
    #[Pure]
	public function isStatic()
    {
    }

    /**
     * Checks if method is a constructor
     *
     * @link https://php.net/manual/en/reflectionmethod.isconstructor.php
     * @return bool Returns {@see true} if the method is a constructor, otherwise {@see false}
     */
    #[Pure]
	public function isConstructor()
    {
    }

    /**
     * Checks if method is a destructor
     *
     * @link https://php.net/manual/en/reflectionmethod.isdestructor.php
     * @return bool Returns {@see true} if the method is a destructor, otherwise {@see false}
     */
    #[Pure]
	public function isDestructor()
    {
    }

    /**
     * Returns a dynamically created closure for the method
     *
     * @link https://php.net/manual/en/reflectionmethod.getclosure.php
     * @param object $object Forbidden for static methods, required for other methods or nothing.
     * @return Closure Retruns {@see Closure} or {@see null} in case of an error.
     * @since 5.4
     */
    #[Pure]
	public function getClosure($object = null)
    {
    }

    /**
     * Gets the method modifiers
     *
     * @link https://php.net/manual/en/reflectionmethod.getmodifiers.php
     * @return int A numeric representation of the modifiers. The modifiers are
     * listed below. The actual meanings of these modifiers are described in the
     * predefined constants.
     *
     * ReflectionMethod modifiers:
     *
     *  - {@see ReflectionMethod::IS_STATIC} - Indicates that the method is static.
     *  - {@see ReflectionMethod::IS_PUBLIC} - Indicates that the method is public.
     *  - {@see ReflectionMethod::IS_PROTECTED} - Indicates that the method is protected.
     *  - {@see ReflectionMethod::IS_PRIVATE} - Indicates that the method is private.
     *  - {@see ReflectionMethod::IS_ABSTRACT} - Indicates that the method is abstract.
     *  - {@see ReflectionMethod::IS_FINAL} - Indicates that the method is final.
     */
    #[Pure]
	public function getModifiers()
    {
    }

    /**
     * Invokes a reflected method.
     *
     * @link https://php.net/manual/en/reflectionmethod.invoke.php
     * @param object|null $object The object to invoke the method on. For static
     * methods, pass {@see null} to this parameter.
     * @param mixed ...$args Zero or more parameters to be passed to the
     * method. It accepts a variable number of parameters which are passed to
     * the method.
     * @return mixed Returns the method result.
     * @throws ReflectionException if the object parameter does not contain an
     * instance of the class that this method was declared in or the method
     * invocation failed.
     */
    public function invoke($object, ...$args)
    {
    }

    /**
     * Invokes the reflected method and pass its arguments as array.
     *
     * @link https://php.net/manual/en/reflectionmethod.invokeargs.php
     * @param object|null $object The object to invoke the method on. In case
     * of static methods, you can pass {@see null} to this parameter.
     * @param array $args The parameters to be passed to the function, as an {@see array}.
     * @return mixed the method result.
     * @throws ReflectionException if the object parameter does not contain an
     * instance of the class that this method was declared in or the method
     * invocation failed.
     */
    public function invokeArgs($object, array $args)
    {
    }

    /**
     * Gets declaring class for the reflected method.
     *
     * @link https://php.net/manual/en/reflectionmethod.getdeclaringclass.php
     * @return ReflectionClass A {@see ReflectionClass} object of the class that the
     * reflected method is part of.
     */
    #[Pure]
	public function getDeclaringClass()
    {
    }

    /**
     * Gets the method prototype (if there is one).
     *
     * @link https://php.net/manual/en/reflectionmethod.getprototype.php
     * @return ReflectionMethod A {@see ReflectionMethod} instance of the method prototype.
     * @throws ReflectionException if the method does not have a prototype
     */
    #[Pure]
	public function getPrototype()
    {
    }

    /**
     * Set method accessibility
     *
     * @link https://php.net/manual/en/reflectionmethod.setaccessible.php
     * @param bool $accessible {@see true} to allow accessibility, or {@see false}
     * @return void No value is returned.
     * @since 5.3.2
     */
    public function setAccessible($accessible)
    {
    }

}
