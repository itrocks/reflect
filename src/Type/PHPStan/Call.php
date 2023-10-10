<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\PHP\Named;

class Call extends Named
{

	//---------------------------------------------------------------------------------------- $types
	/** @var list<Reflection_Type> */
	public array $types;

	//----------------------------------------------------------------------------------- __construct
	/** @param list<Reflection_Type> $types */
	public function __construct(string $name, array $types, Reflection $reflection, bool $allows_null)
	{
		parent::__construct($name, $reflection, $allows_null);
		$this->types = $types;
	}

	//-------------------------------------------------------------------------------------- getTypes
	/** @return list<Reflection_Type> */
	public function getTypes() : array
	{
		return $this->types;
	}

}
