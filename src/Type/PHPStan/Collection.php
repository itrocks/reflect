<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Reflection_Type;

class Collection extends Of
{

	//----------------------------------------------------------------------------------- $dimensions
	/** @var non-negative-int */
	protected int $dimensions = 0;

	//------------------------------------------------------------------------------------------ $key
	public ?Reflection_Type $key = null;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		if (($this->name === '') && ($this->dimensions > 0)) {
			return $this->type . str_repeat('[]', $this->dimensions);
		}
		return parent::__toString();
	}

	//--------------------------------------------------------------------------------- getDimensions
	/** @return non-negative-int */
	public function getDimensions() : int
	{
		return $this->dimensions;
	}

	//---------------------------------------------------------------------------------- ofDimensions
	/** @param positive-int $dimensions */
	public static function ofDimensions(
		Reflection_Type $type, int $dimensions, Reflection $reflection, bool $allows_null
	) : self
	{
		$collection = new Collection('', $type, $reflection, $allows_null);
		$collection->dimensions = $dimensions;
		return $collection;
	}

	//---------------------------------------------------------------------------------------- ofName
	public static function ofName(
		string $name, Reflection_Type $type, Reflection $reflection, bool $allows_null,
		Reflection_Type $key = null
	) : self
	{
		$collection = new Collection($name, $type, $reflection, $allows_null);
		if (isset($key)) {
			$collection->key = $key;
		}
		return $collection;
	}

}
