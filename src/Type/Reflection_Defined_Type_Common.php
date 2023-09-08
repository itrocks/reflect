<?php
namespace ITRocks\Reflect\Type;

trait Reflection_Defined_Type_Common
{
	use Reflection_Type_Common;

	//------------------------------------------------------------------------------------ allowsNull
	public function allowsNull() : bool
	{
		return $this->type->allowsNull();
	}

}
