<?php
namespace ITRocks\Reflect\Type;

use Stringable;

interface Reflection_Type extends Stringable
{

	//------------------------------------------------------------------------------------ allowsNull
	public function allowsNull() : bool;

}
