<?php

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * @link https://secure.php.net/manual/en/class.reflectionzendextension.php
 * @since 5.4
 */
class ReflectionZendExtension implements Reflector
{
    /**
     * @var string Name of the extension, same as calling the {@see ReflectionZendExtension::getName()} method
     */
    #[Immutable]
    public $name;

    /**
     * Constructs a ReflectionZendExtension object
     *
     * @link https://php.net/manual/en/reflectionzendextension.construct.php
     * @param string $name
     * @throws \ReflectionException if the extension does not exist.
     * @since 5.4
     */
    public function __construct($name)
    {
    }

    /**
     * Exports a reflected zend extension.
     *
     * @link https://php.net/manual/en/reflectionzendextension.export.php
     * @param string $name The reflection to export.
     * @param bool $return Setting to {@see true} will return the
     * export, as opposed to emitting it. Setting to {@see false} (the default)
     * will do the opposite.
     * @return string|null If the $return parameter is set to {@see true}, then
     * the export is returned as a string, otherwise {@see null} is returned.
     */
    public static function export($name, $return = false)
    {
    }

    /**
     * To string handler
     *
     * @link https://php.net/manual/en/reflectionzendextension.tostring.php
     * @return string
     * @since 5.4
     */
    public function __toString()
    {
    }

    /**
     * Gets name
     *
     * @link https://php.net/manual/en/reflectionzendextension.getname.php
     * @return string
     * @since 5.4
     */
    #[Pure]
	public function getName()
    {
    }

    /**
     * Gets version
     *
     * @link https://php.net/manual/en/reflectionzendextension.getversion.php
     * @return string
     * @since 5.4
     */
    #[Pure]
	public function getVersion()
    {
    }

    /**
     * Gets author
     *
     * @link https://php.net/manual/en/reflectionzendextension.getauthor.php
     * @return string
     * @since 5.4
     */
    #[Pure]
	public function getAuthor()
    {
    }

    /**
     * Gets URL
     *
     * @link https://php.net/manual/en/reflectionzendextension.geturl.php
     * @return string
     * @since 5.4
     */
    #[Pure]
	public function getURL()
    {
    }

    /**
     * Gets copyright
     *
     * @link https://php.net/manual/en/reflectionzendextension.getcopyright.php
     * @return string
     * @since 5.4
     */
    #[Pure]
	public function getCopyright()
    {
    }

    /**
     * Clone handler
     *
     * @link https://php.net/manual/en/reflectionzendextension.clone.php
     * @return void
     * @since 5.4
     */
    final private function __clone()
    {
    }

}
