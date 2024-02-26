<?php

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * The <b>ReflectionFunction</b> class reports
 * information about a function.
 *
 * @link https://php.net/manual/en/class.reflectionfunction.php
 */
class ReflectionFunction extends ReflectionFunctionAbstract
{
    /**
     * @var string Function name, same as calling the {@see ReflectionFunction::getName()} method
     */
    #[Immutable]
    public $name;

    /**
     * Indicates deprecated functions.
     *
     * @link https://www.php.net/manual/en/class.reflectionfunction.php#reflectionfunction.constants.is-deprecated
     */
    const IS_DEPRECATED = 2048;

    /**
     * Constructs a ReflectionFunction object
     *
     * @link https://php.net/manual/en/reflectionfunction.construct.php
     * @param string|Closure $function The name of the function to reflect or a closure.
     * @throws ReflectionException if the function does not exist.
     */
    public function __construct($function)
    {
    }

    /**
     * Returns the string representation of the ReflectionFunction object.
     *
     * @link https://php.net/manual/en/reflectionfunction.tostring.php
     */
    public function __toString()
    {
    }

    /**
     * Exports function
     *
     * @link https://php.net/manual/en/reflectionfunction.export.php
     * @param string $name The reflection to export.
     * @param bool $return Setting to {@see true} will return the
     * export, as opposed to emitting it. Setting to {@see false} (the default)
     * will do the opposite.
     * @return string|null If the $return parameter is set to {@see true}, then
     * the export is returned as a string, otherwise {@see null} is returned.
     * @removed 8.0
     */
    #[Deprecated(since: '7.4')]
    public static function export($name, $return = false)
    {
    }

    /**
     * Checks if function is disabled
     *
     * @link https://php.net/manual/en/reflectionfunction.isdisabled.php
     * @return bool {@see true} if it's disable, otherwise {@see false}
     */
    #[Deprecated(since: '8.0')]
    #[Pure]
	public function isDisabled()
    {
    }

    /**
     * Invokes function
     *
     * @link https://www.php.net/manual/en/reflectionfunction.invoke.php
     * @param mixed ...$args The passed in argument list. It accepts a
     * variable number of arguments which are passed to the function much
     * like {@see call_user_func} is.
     * @return mixed Returns the result of the invoked function call.
     */
    public function invoke(...$args)
    {
    }

    /**
     * Invokes function args
     *
     * @link https://php.net/manual/en/reflectionfunction.invokeargs.php
     * @param array $args The passed arguments to the function as an array, much
     * like {@see call_user_func_array} works.
     * </p>
     * @return mixed the result of the invoked function
     */
    public function invokeArgs(array $args)
    {
    }

    /**
     * Returns a dynamically created closure for the function
     *
     * @link https://php.net/manual/en/reflectionfunction.getclosure.php
     * @return Closure Returns {@see Closure} or {@see null} in case of an error.
     */
    #[Pure]
	public function getClosure()
    {
    }
}
