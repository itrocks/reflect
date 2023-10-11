<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\PHP\Union;

class Int_Mask extends Named
{

	//--------------------------------------------------------------------------------------- $values
	/** @var list<Int_Literal|Class_Constant|Union> */
	public array $values;

	//----------------------------------------------------------------------------------- __construct
	/** @param list<Int_Literal|Class_Constant|Union> $values */
	public function __construct(
		string $name, array $values, Reflection $reflection, bool $allows_null
	) {
		parent::__construct($name, $reflection, $allows_null);
		$this->values = $values;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return join(($this->name === 'int-mask-of') ? '|' : ',', $this->values);
	}

}
