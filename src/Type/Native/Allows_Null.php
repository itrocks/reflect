<?php
namespace ITRocks\Reflect\Type\Native;

trait Allows_Null
{

	//------------------------------------------------------------------------------------ allowsNull
	public function allowsNull() : bool
	{
		return $this->type->allowsNull();
	}

}
