<?php
namespace ITRocks\Reflect\Type;

use ITRocks\Reflect\Interfaces\Reflection;

class Reflection_Undefined_Type implements Reflection_Type
{
	use Reflection_Type_Common;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Reflection $reflection)
	{
		$this->reflection = $reflection;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return '';
	}

	//------------------------------------------------------------------------------------ allowsNull
	public function allowsNull() : bool
	{
		return true;
	}

}
