<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\Undefined;

class Call extends Named
{

	//----------------------------------------------------------------------------------- $parameters
	/** @var list<Parameter> */
	public array $parameters;

	//--------------------------------------------------------------------------------------- $return
	public Reflection_Type $return;

	//----------------------------------------------------------------------------------- __construct
	/** @param list<Parameter> $parameters */
	public function __construct(
		string $name, array $parameters, Reflection $reflection, bool $allows_null
	) {
		parent::__construct($name, $reflection, $allows_null);
		$this->parameters = $parameters;
		$this->return = new Undefined($reflection);
	}

	//--------------------------------------------------------------------------------- getParameters
	/** @return list<Parameter> */
	public function getParameters() : array
	{
		return $this->parameters;
	}

	//------------------------------------------------------------------------------------- getReturn
	public function getReturn() : Reflection_Type
	{
		return $this->return;
	}

}
