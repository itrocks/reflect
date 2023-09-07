<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Interfaces\Reflection;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

class Type
{

	//----------------------------------------------------------------------------------- $reflection
	protected Reflection|null $reflection = null;

	//----------------------------------------------------------------------------------------- $type
	protected ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType $type,
		Reflection $reflection = null
	) {
		$this->reflection = $reflection;
		$this->type       = $type;
	}

	//------------------------------------------------------------------------------------ allowsNull
	public function allowsNull() : bool
	{
		return $this->type->allowsNull();
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return ($this->type instanceof ReflectionNamedType) ? $this->type->getName() : '';
	}

	//-------------------------------------------------------------------------------------- getTypes
	/** @return list<Type> */
	public function getTypes() : array
	{
		$this_type = $this->type;
		if ($this_type instanceof ReflectionNamedType) {
			return [];
		}
		/** @var ReflectionIntersectionType|ReflectionUnionType $this_type */
		$types = [];
		foreach ($this_type->getTypes() as $type) {
			/** @var ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType $type */
			$types[] = new Type($type, $this->reflection);
		}
		return $types;
	}

	//------------------------------------------------------------------------------------- isBuiltin
	/** @todo areBuiltIn() (all named types must be built-in) and hasBuiltIn() (any named type) */
	public function isBuiltin() : bool
	{
		return ($this->type instanceof ReflectionNamedType) && $this->type->isBuiltin();
	}

}
