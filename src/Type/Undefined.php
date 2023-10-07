<?php
namespace ITRocks\Reflect\Type;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Single;

class Undefined implements Single
{
	use Common;

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
