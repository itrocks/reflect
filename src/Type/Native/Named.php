<?php
namespace ITRocks\Reflect\Type\Native;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\Interface;
use ReflectionNamedType;

class Named implements Interface\Named
{
	use Allows_Null;
	use Common;

	//----------------------------------------------------------------------------------------- $type
	protected ReflectionNamedType $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(ReflectionNamedType $type, Reflection $reflection)
	{
		$this->reflection = $reflection;
		$this->type       = $type;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->type->getName();
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
