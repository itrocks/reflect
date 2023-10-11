<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\PHP\Named;

class Shape extends Named
{

	//---------------------------------------------------------------------------------------- $types
	/** @var array<Reflection_Type> */
	public array $types;

	//----------------------------------------------------------------------------------- __construct
	/** @param array<Reflection_Type> $types */
	public function __construct(string $name, array $types, Reflection $reflection, bool $allows_null)
	{
		parent::__construct($name, $reflection, $allows_null);
		$this->types = $types;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return join(',', $this->types);
	}

}
