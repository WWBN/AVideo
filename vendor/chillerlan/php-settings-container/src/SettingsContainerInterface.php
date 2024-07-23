<?php
/**
 * Interface SettingsContainerInterface
 *
 * @created      28.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\Settings;

use JsonSerializable;

/**
 * a generic container with magic getter and setter
 */
interface SettingsContainerInterface extends JsonSerializable{

	/**
	 * Retrieve the value of $property
	 *
	 * @return mixed|null
	 */
	public function __get(string $property);

	/**
	 * Set $property to $value while avoiding private and non-existing properties
	 *
	 * @param string $property
	 * @param mixed  $value
	 */
	public function __set(string $property, $value):void;

	/**
	 * Checks if $property is set (aka. not null), excluding private properties
	 */
	public function __isset(string $property):bool;

	/**
	 * Unsets $property while avoiding private and non-existing properties
	 */
	public function __unset(string $property):void;

	/**
	 * @see \chillerlan\Settings\SettingsContainerInterface::toJSON()
	 */
	public function __toString():string;

	/**
	 * Returns an array representation of the settings object
	 *
	 * The values will be run through the magic __get(), which may also call custom getters.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray():array;

	/**
	 * Sets properties from a given iterable
	 *
	 * The values will be run through the magic __set(), which may also call custom setters.
	 *
	 *  @phpstan-param array<string, mixed> $properties
	 */
	public function fromIterable(iterable $properties):SettingsContainerInterface;

	/**
	 * Returns a JSON representation of the settings object
	 *
	 * @see \json_encode()
	 * @see \chillerlan\Settings\SettingsContainerInterface::toArray()
	 *
	 * @throws \JsonException
	 */
	public function toJSON(?int $jsonOptions = null):string;

	/**
	 * Sets properties from a given JSON string
	 *
	 * @see \chillerlan\Settings\SettingsContainerInterface::fromIterable()
	 *
	 * @throws \Exception
	 * @throws \JsonException
	 */
	public function fromJSON(string $json):SettingsContainerInterface;

}
