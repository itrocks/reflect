<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Type\Interface;
use ITRocks\Reflect\Type\PHP\Allows_Null;

class Float_Literal implements Interface\Literal
{
	use Allows_Null;
	use Literal;

	//---------------------------------------------------------------------------------------- $value
	public float $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(float $value, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->value       = $value;
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue() : float
	{
		return $this->value;
	}

}
