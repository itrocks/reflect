<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Type\Interface;
use ITRocks\Reflect\Type\PHP\Allows_Null;

class String_Literal implements Interface\Literal
{
	use Allows_Null;

	//---------------------------------------------------------------------------------------- $value
	protected string $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $value, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->value       = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		if (str_contains('"', $this->value)) {
			return "'" . str_replace("'", "\\'", $this->value) . "'";
		}
		return '"' . $this->value . '"';
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue() : string
	{
		return $this->value;
	}

}
