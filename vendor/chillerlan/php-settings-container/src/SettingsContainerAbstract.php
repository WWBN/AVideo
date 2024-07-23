<?php
/**
 * Class SettingsContainerAbstract
 *
 * @created      28.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\Settings;

use JsonException, ReflectionClass, ReflectionProperty;
use function array_keys, get_object_vars, json_decode, json_encode, json_last_error_msg, method_exists, property_exists;
use const JSON_THROW_ON_ERROR;

abstract class SettingsContainerAbstract implements SettingsContainerInterface{

	/**
	 * SettingsContainerAbstract constructor.
	 *
	 * @phpstan-param array<string, mixed> $properties
	 */
	public function __construct(?iterable $properties = null){

		if(!empty($properties)){
			$this->fromIterable($properties);
		}

		$this->construct();
	}

	/**
	 * calls a method with trait name as replacement constructor for each used trait
	 * (remember pre-php5 classname constructors? yeah, basically this.)
	 */
	protected function construct():void{
		$traits = (new ReflectionClass($this))->getTraits();

		foreach($traits as $trait){
			$method = $trait->getShortName();

			if(method_exists($this, $method)){
				$this->{$method}();
			}
		}

	}

	/**
	 * @inheritdoc
	 */
	public function __get(string $property){

		if(!property_exists($this, $property) || $this->isPrivate($property)){
			return null;
		}

		$method = 'get_'.$property;

		if(method_exists($this, $method)){
			return $this->{$method}();
		}

		return $this->{$property};
	}

	/**
	 * @inheritdoc
	 */
	public function __set(string $property, $value):void{

		if(!property_exists($this, $property) || $this->isPrivate($property)){
			return;
		}

		$method = 'set_'.$property;

		if(method_exists($this, $method)){
			$this->{$method}($value);

			return;
		}

		$this->{$property} = $value;
	}

	/**
	 * @inheritdoc
	 */
	public function __isset(string $property):bool{
		return isset($this->{$property}) && !$this->isPrivate($property);
	}

	/**
	 * @internal Checks if a property is private
	 */
	protected function isPrivate(string $property):bool{
		return (new ReflectionProperty($this, $property))->isPrivate();
	}

	/**
	 * @inheritdoc
	 */
	public function __unset(string $property):void{

		if($this->__isset($property)){
			unset($this->{$property});
		}

	}

	/**
	 * @inheritdoc
	 */
	public function __toString():string{
		return $this->toJSON();
	}

	/**
	 * @inheritdoc
	 */
	public function toArray():array{
		$properties = [];

		foreach(array_keys(get_object_vars($this)) as $key){
			$properties[$key] = $this->__get($key);
		}

		return $properties;
	}

	/**
	 * @inheritdoc
	 */
	public function fromIterable(iterable $properties):SettingsContainerInterface{

		foreach($properties as $key => $value){
			$this->__set($key, $value);
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function toJSON(?int $jsonOptions = null):string{
		$json = json_encode($this, ($jsonOptions ?? 0));

		if($json === false){
			throw new JsonException(json_last_error_msg());
		}

		return $json;
	}

	/**
	 * @inheritdoc
	 */
	public function fromJSON(string $json):SettingsContainerInterface{
		/** @phpstan-var array<string, mixed> $data */
		$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

		return $this->fromIterable($data);
	}

	/**
	 * @inheritdoc
	 * @return array<string, mixed>
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize():array{
		return $this->toArray();
	}

}
