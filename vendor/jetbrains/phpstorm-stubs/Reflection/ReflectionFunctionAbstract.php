<?php

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * A parent class to <b>ReflectionFunction</b>, read its
 * description for details.
 *
 * @link https://php.net/manual/en/class.reflectionfunctionabstract.php
 */
abstract class ReflectionFunctionAbstract implements Reflector
{
    /**
     * @var string Name of the function, same as calling the {@see ReflectionFunctionAbstract::getName()} method
     */
    #[Immutable]
    public $name;

    /**
     * Clones function
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.clone.php
     * @return void
     */
    final private function __clone()
    {
    }

    /**
     * Checks if function in namespace
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.innamespace.php
     * @return bool {@see true} if it's in a namespace, otherwise {@see false}
     */
    public function inNamespace()
    {
    }

    /**
     * Checks if closure
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.isclosure.php
     * @return bool {@see true} if it's a closure, otherwise {@see false}
     */
    #[Pure]
	public function isClosure()
    {
    }

    /**
     * Checks if deprecated
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.isdeprecated.php
     * @return bool {@see true} if it's deprecated, otherwise {@see false}
     */
    #[Pure]
	public function isDeprecated()
    {
    }

    /**
     * Checks if is internal
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.isinternal.php
     * @return bool {@see true} if it's internal, otherwise {@see false}
     */
    #[Pure]
	public function isInternal()
    {
    }

    /**
     * Checks if user defined
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.isuserdefined.php
     * @return bool {@see true} if it's user-defined, otherwise {@see false}
     */
    #[Pure]
	public function isUserDefined()
    {
    }

    /**
     * Returns whether this function is a generator
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.isgenerator.php
     * @return bool {@see true} if the function is generator, otherwise {@see false}
     * @since 5.5
     */
    #[Pure]
	public function isGenerator()
    {
    }

    /**
     * Returns whether this function is variadic
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.isvariadic.php
     * @return bool {@see true} if the function is variadic, otherwise {@see false}
     * @since 5.6
     */
    #[Pure]
	public function isVariadic()
    {
    }

    /**
     * Returns this pointer bound to closure
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getclosurethis.php
     * @return object|null Returns $this pointer or {@see null} in case of an error.
     */
    #[Pure]
	public function getClosureThis()
    {
    }

    /**
     * Returns the scope associated to the closure
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getclosurescopeclass.php
     * @return ReflectionClass|null Returns the class on success or {@see null}
     * on failure.
     * @since 5.4
     */
    #[Pure]
	public function getClosureScopeClass()
    {
    }

    /**
     * Gets doc comment
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getdoccomment.php
     * @return string|false The doc comment if it exists, otherwise {@see false}
     */
    #[Pure]
	public function getDocComment()
    {
    }

    /**
     * Gets end line number
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getendline.php
     * @return int|false The ending line number of the user defined function,
     * or {@see false} if unknown.
     */
    #[Pure]
	public function getEndLine()
    {
    }

    /**
     * Gets extension info
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getextension.php
     * @return ReflectionExtension|null The extension information, as a
     * {@see ReflectionExtension} object or {@see null} instead.
     */
    #[Pure]
	public function getExtension()
    {
    }

    /**
     * Gets extension name
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getextensionname.php
     * @return string|null The extension's name or {@see null} instead.
     */
    #[Pure]
	public function getExtensionName()
    {
    }

    /**
     * Gets file name
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getfilename.php
     * @return string|false The file name or {@see false} in case of error.
     */
    #[Pure]
	public function getFileName()
    {
    }

    /**
     * Gets function name
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getname.php
     * @return string The name of the function.
     */
    #[Pure]
	public function getName()
    {
    }

    /**
     * Gets namespace name
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getnamespacename.php
     * @return string The namespace name.
     */
    #[Pure]
	public function getNamespaceName()
    {
    }

    /**
     * Gets number of parameters
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getnumberofparameters.php
     * @return int The number of parameters.
     * @since 5.0.3
     */
    #[Pure]
	public function getNumberOfParameters()
    {
    }

    /**
     * Gets number of required parameters
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getnumberofrequiredparameters.php
     * @return int The number of required parameters.
     * @since 5.0.3
     */
    #[Pure]
	public function getNumberOfRequiredParameters()
    {
    }

    /**
     * Gets parameters
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getparameters.php
     * @return ReflectionParameter[] The parameters, as a ReflectionParameter objects.
     */
    #[Pure]
	public function getParameters()
    {
    }

    /**
     * Gets the specified return type of a function
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getreturntype.php
     * @return ReflectionType|null Returns a {@see ReflectionType} object if a
     * return type is specified, {@see null} otherwise.
     * @since 7.0
     */
    #[Pure]
	public function getReturnType()
    {
    }

    /**
     * Gets function short name
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getshortname.php
     * @return string The short name of the function.
     */
    #[Pure]
	public function getShortName()
    {
    }

    /**
     * Gets starting line number
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getstartline.php
     * @return int The starting line number.
     */
    #[Pure]
	public function getStartLine()
    {
    }

    /**
     * Gets static variables
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.getstaticvariables.php
     * @return array An array of static variables.
     */
    #[Pure]
	public function getStaticVariables()
    {
    }

    /**
     * Checks if returns reference
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.returnsreference.php
     * @return bool {@see true} if it returns a reference, otherwise {@see false}
     */
    public function returnsReference()
    {
    }

    /**
     * Checks if the function has a specified return type
     *
     * @link https://php.net/manual/en/reflectionfunctionabstract.hasreturntype.php
     * @return bool Returns {@see true} if the function is a specified return
     * type, otherwise {@see false}.
     * @since 7.0
     */
    public function hasReturnType()
    {
    }

    /**
     * Returns an array of function attributes.
     *
     * @param string|null $name Name of an attribute class
     * @param int $flags Сriteria by which the attribute is searched.
     * @return ReflectionAttribute[]
     * @since 8.0
     */
    #[Pure]
	public function getAttributes($name = null, $flags = 0)
    {
    }
}
