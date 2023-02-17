<?php

use JetBrains\PhpStorm\Pure;

/**
 * @since 8.0
 */
class ReflectionAttribute
{
    /**
     * Indicates that the search for a suitable attribute should not be by
     * strict comparison, but by the inheritance chain.
     *
     * Used for the argument of flags of the "getAttribute" method.
     *
     * @since 8.0
     */
    const IS_INSTANCEOF = 2;

    /**
     * ReflectionAttribute cannot be created explicitly.
     * @since 8.0
     */
    private function __construct()
    {
    }

    /**
     * Gets attribute name
     *
     * @return string The name of the attribute parameter.
     * @since 8.0
     */
    #[Pure]
	public function getName()
    {
    }

    /**
     * Returns the target of the attribute as a bit mask format.
     *
     * @return int
     * @since 8.0
     */
    #[Pure]
	public function getTarget()
    {
    }

    /**
     * Returns {@see true} if the attribute is repeated.
     *
     * @return bool
     * @since 8.0
     */
    #[Pure]
	public function isRepeated()
    {
    }

    /**
     * Gets list of passed attribute's arguments.
     *
     * @return array
     * @since 8.0
     */
    #[Pure]
	public function getArguments()
    {
    }

    /**
     * Creates a new instance of declarted attribute with passed arguments
     *
     * @return object
     * @since 8.0
     */
    public function newInstance()
    {
    }

    /**
     * ReflectionAttribute cannot be cloned
     *
     * @return void
     * @since 8.0
     */
    private function __clone()
    {
    }
}
