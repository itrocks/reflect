<?php
namespace ITRocks\Reflect\Type\PHP;

trait Allows_Null
{

	//---------------------------------------------------------------------------------- $allows_null
	protected bool $allows_null;

	//------------------------------------------------------------------------------------ allowsNull
	public function allowsNull() : bool
	{
		return $this->allows_null;
	}

}
