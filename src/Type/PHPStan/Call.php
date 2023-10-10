<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\PHP\Named;

class Call extends Named
{

	//----------------------------------------------------------------------------------- $parameters
	/** @var list<Parameter> */
	public array $parameters;

	//----------------------------------------------------------------------------------- __construct
	/** @param list<Parameter> $parameters */
	public function __construct(
		string $name, array $parameters, Reflection $reflection, bool $allows_null
	) {
		parent::__construct($name, $reflection, $allows_null);
		$this->parameters = $parameters;
	}

	//--------------------------------------------------------------------------------- getParameters
	/** @return list<Parameter> */
	public function getParameters() : array
	{
		return $this->parameters;
	}

}
