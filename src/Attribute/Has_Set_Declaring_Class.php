<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Interface\Reflection_Class;

interface Has_Set_Declaring_Class
{

	//----------------------------------------------------------------------------- setDeclaringClass
	/** @param Reflection_Class<object> $class */
	public function setDeclaringClass(Reflection_Class $class) : void;

}
