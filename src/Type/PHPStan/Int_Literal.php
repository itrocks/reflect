<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface;

class Int_Literal implements Interface\Literal
{
	use Literal;

	//---------------------------------------------------------------------------------------- $value
	public int $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(int $value, Reflection $reflection, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->reflection  = $reflection;
		$this->value       = $value;
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue() : int
	{
		return $this->value;
	}

}
