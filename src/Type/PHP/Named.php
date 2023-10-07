<?php
namespace ITRocks\Reflect\Type\PHP;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\Interface;

class Named implements Interface\Named
{
	use Allows_Null;
	use Common;

	//-------------------------------------------------------------------------------------- BUILT_IN
	/** @var non-empty-list<string> */
	public const BUILT_IN = [
		'array', 'bool', 'callable', 'false', 'float', 'int', 'iterable', 'mixed', 'never', 'null',
		'object', 'string', 'true', 'void'
	];

	//----------------------------------------------------------------------------------------- $name
	protected string $name;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $name, Reflection $reflection, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->name        = $name;
		$this->reflection  = $reflection;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->name;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------- isBuiltin
	public function isBuiltin() : bool
	{
		return in_array($this->name, static::BUILT_IN, true);
	}
	
}
