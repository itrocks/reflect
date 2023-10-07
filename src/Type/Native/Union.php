<?php
namespace ITRocks\Reflect\Type\Native;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface;
use ReflectionUnionType;

class Union implements Interface\Union
{
	use Multiple;

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
			$types[] = ($type instanceof Interface\Multiple)
				? ('(' . $type . ')')
				: $type;
		}
		return join('|', $types);
	}

}
