<?php
namespace ITRocks\Reflect\Type;

use ITRocks\Reflect\Interface\Reflection;
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

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$types = [];
		foreach ($this->getTypes() as $type) {
			$types[] = ($type instanceof Reflection_Multiple_Type)
				? ('(' . $type . ')')
				: $type;
		}
		return join('|', $types);
	}

}
