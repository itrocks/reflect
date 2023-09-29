<?php
namespace ITRocks\Reflect;

/**
 * @extends Reflection_Property<Class>
 * @template Class of object
 * @todo Implement
 */
class Reflection_Property_Value extends Reflection_Property
{

	//--------------------------------------------------------------------------------------- $object
	public ?object $object = null;

	//------------------------------------------------------------------------------------- getObject
	public function getObject() : ?object
	{
		return $this->object;
	}

}
