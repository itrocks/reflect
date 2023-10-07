<?php
namespace ITRocks\Reflect\Type\Interface;

interface Literal extends Single
{

	//-------------------------------------------------------------------------------------- getValue
	public function getValue() : float|int|string;

}
