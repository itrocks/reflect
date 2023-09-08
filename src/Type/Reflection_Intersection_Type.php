<?php
namespace ITRocks\Reflect\Type;

use ITRocks\Reflect\Interfaces\Reflection;
use ReflectionIntersectionType;

class Reflection_Intersection_Type implements Reflection_Multiple_Type
{
	use Reflection_Multiple_Type_Common;

	//----------------------------------------------------------------------------------------- $type
	protected ReflectionIntersectionType $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(ReflectionIntersectionType $type, Reflection $reflection)
	{
		$this->reflection = $reflection;
		$this->type       = $type;
	}

}
