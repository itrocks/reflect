<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Allows_Null;

class Int_Mask_Of implements Single
{
	use Allows_Null;
	use Common;

	//--------------------------------------------------------------------------------------- $values
	/** @var list<Int_Literal|Class_Constant> */
	public array $values;

	//----------------------------------------------------------------------------------- __construct
	/** @param list<Int_Literal|Class_Constant> $values */
	public function __construct(array $values, Reflection $reflection, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->reflection  = $reflection;
		$this->values      = $values;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return join('|', $this->values);
	}

}
