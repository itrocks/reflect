<?php
namespace ITRocks\Reflect\Type;

use ITRocks\Reflect\Interfaces\Reflection;
use ReflectionUnionType;

class Reflection_Union_Type implements Reflection_Multiple_Type
{
	use Reflection_Multiple_Type_Common;

	//----------------------------------------------------------------------------------------- $type
	protected ReflectionUnionType $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(ReflectionUnionType $type, Reflection $reflection)
	{
		$this->reflection = $reflection;
		$this->type       = $type;
	}

}
