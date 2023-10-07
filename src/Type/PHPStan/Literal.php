<?php
namespace ITRocks\Reflect\Type\PHPStan;

trait Literal
{

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return (string)$this->value;
	}

}
