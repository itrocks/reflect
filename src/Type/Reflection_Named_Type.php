<?php
namespace ITRocks\Reflect\Type;

use ITRocks\Reflect\Interfaces\Reflection;
use ReflectionNamedType;

class Reflection_Named_Type implements Reflection_Type
{
	use Reflection_Defined_Type_Common;

	//----------------------------------------------------------------------------------------- $type
	protected ReflectionNamedType $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(ReflectionNamedType $type, Reflection $reflection)
	{
		$this->reflection = $reflection;
		$this->type       = $type;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->type->getName();
	}

	//------------------------------------------------------------------------------------- isBuiltin
	public function isBuiltin() : bool
	{
		return $this->type->isBuiltin();
	}

}
