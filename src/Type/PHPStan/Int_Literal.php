<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Type\Interface;
use ITRocks\Reflect\Type\PHP\Allows_Null;

class Int_Literal implements Interface\Literal
{
	use Allows_Null;
	use Literal;

	//---------------------------------------------------------------------------------------- $value
	public int $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(int $value, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->value       = $value;
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue() : int
	{
		return $this->value;
	}

}
