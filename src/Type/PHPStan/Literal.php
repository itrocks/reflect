<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\PHP\Allows_Null;

trait Literal
{
	use Allows_Null;
	use Common;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return (string)$this->value;
	}

}
