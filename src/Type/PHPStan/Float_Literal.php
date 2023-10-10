<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface;

class Float_Literal implements Interface\Literal
{
	use Literal;

	//---------------------------------------------------------------------------------------- $value
	public float $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(float $value, Reflection $reflection, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->reflection  = $reflection;
		$this->value       = $value;
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue() : float
	{
		return $this->value;
	}

}
