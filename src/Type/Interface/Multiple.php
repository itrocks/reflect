<?php
namespace ITRocks\Reflect\Type\Interface;

interface Multiple extends Reflection_Type
{

	//----------------------------------------------------------------------------------- getAllTypes
	/** @return non-empty-list<Single> */
	public function getAllTypes() : array;

	//-------------------------------------------------------------------------------- getElementType
	public function getElementType() : Single;

	//-------------------------------------------------------------------------------------- getTypes
	/** @return non-empty-list<Reflection_Type> */
	public function getTypes() : array;

}
