<?php
namespace ITRocks\Reflect\Type\Interface;

use Stringable;

interface Reflection_Type extends Stringable
{

	//------------------------------------------------------------------------------------ allowsNull
	public function allowsNull() : bool;

}
