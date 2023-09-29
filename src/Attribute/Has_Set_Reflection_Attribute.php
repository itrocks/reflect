<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Reflection_Attribute;

interface Has_Set_Reflection_Attribute
{

	//------------------------------------------------------------------------ setReflectionAttribute
	/** @param Reflection_Attribute<Reflection,object> $attribute */
	public function setReflectionAttribute(Reflection_Attribute $attribute) : void;

}
