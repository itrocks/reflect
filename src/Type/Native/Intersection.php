<?php
namespace ITRocks\Reflect\Type\Native;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface;
use ReflectionIntersectionType;

class Intersection implements Interface\Intersection
{
	use Multiple;

	//----------------------------------------------------------------------------------------- $type
	protected ReflectionIntersectionType $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(ReflectionIntersectionType $type, Reflection $reflection)
	{
		$this->reflection = $reflection;
		$this->type       = $type;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return join('&', $this->getTypes());
	}

}
