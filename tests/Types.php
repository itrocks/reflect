<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Interfaces\Reflection_Property;
use ITRocks\Reflect\Type\Reflection_Multiple_Type;
use ITRocks\Reflect\Type\Reflection_Type;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionUnionType;

abstract class Types extends ReflectionUnionType
{

	//------------------------------------------------------------------------------------------ $int
	public int $int;

	//------------------------------------------------------------------------------------ $int_types
	public int|Types $int_types;

	//--------------------------------------------------------------------------------------- $or_and
	public int|Types|(Reflection_Type&Reflection_Multiple_Type)|Reflection_Property|null $or_and;

	//---------------------------------------------------------------------------------------- $types
	public Types $types;

	//------------------------------------------------------------------------------------ $types_int
	public Types|int $types_int;

	//----------------------------------------------------------------------------------- $types_null
	public Types|null $types_null;

	//--------------------------------------------------------------------------------- $types_null_2
	public ?Types $types_null_2;

	//-------------------------------------------------------------------------------------- $without
	/**
	 * @noinspection PhpMissingFieldTypeInspection
	 * @phpstan-ignore-next-line For testing purpose
	 */
	public $without;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		$this->int          = 0;
		$this->int_types    = 0;
		$this->types        = $this;
		$this->types_int    = 0;
		$this->or_and       = null;
		$this->types_null   = null;
		$this->types_null_2 = null;
	}

	//-------------------------------------------------------------------------------------- exhaustA
	/** @phpstan-ignore-next-line iterable is here for testing purpose */
	abstract public function exhaustA()
		: bool|callable|float|int|null|parent|string|iterable|(ReflectionClass&ReflectionMethod)
			|ReflectionProperty|self|static|Types;

	//-------------------------------------------------------------------------------------- exhaustB
	/** @phpstan-ignore-next-line iterable is here for testing purpose */
	abstract public function exhaustB()
	: Types|static|self|ReflectionProperty|(ReflectionMethod&ReflectionClass)|iterable|string|parent|null|int|float|callable|true;

}
