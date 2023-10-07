<?php
namespace ITRocks\Reflect\Type\Interface;

interface Named extends Single
{

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string;

	//------------------------------------------------------------------------------------- isBuiltin
	public function isBuiltin() : bool;

}
