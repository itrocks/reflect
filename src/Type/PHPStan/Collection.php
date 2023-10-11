<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Multiple;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\Undefined;

class Collection extends Named
{

	//----------------------------------------------------------------------------------- $dimensions
	/** @var non-negative-int */
	protected int $dimensions = 0;

	//------------------------------------------------------------------------------------------ $key
	public ?Reflection_Type $key = null;

	//----------------------------------------------------------------------------------------- $type
	public Reflection_Type $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		string $name, Reflection_Type $type, Reflection $reflection, bool $allows_null
	) {
		parent::__construct($name, $reflection, $allows_null);
		$this->type = $type;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		if (($this->name === '') && ($this->dimensions > 0)) {
			return $this->type . str_repeat('[]', $this->dimensions);
		}
		return $this->name . '<' . $this->type . '>';
	}

	//--------------------------------------------------------------------------------- getDimensions
	/** @return non-negative-int */
	public function getDimensions() : int
	{
		return $this->dimensions;
	}

	//-------------------------------------------------------------------------------- getElementType
	public function getElementType() : Single
	{
		if ($this->type instanceof Multiple) {
			return $this->type->getElementType();
		}
		elseif ($this->type instanceof Single) {
			return $this->type;
		}
		else {
			return new Undefined($this->reflection);
		}
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
