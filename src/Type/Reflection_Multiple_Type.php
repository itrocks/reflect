<?php
namespace ITRocks\Reflect\Type;

interface Reflection_Multiple_Type extends Reflection_Type
{

	//----------------------------------------------------------------------------------- getAllTypes
	/** @return list<Reflection_Named_Type> */
	public function getAllTypes() : array;

	//-------------------------------------------------------------------------------------- getTypes
	/** @return list<Reflection_Intersection_Type|Reflection_Named_Type|Reflection_Union_Type> */
	public function getTypes() : array;

}
