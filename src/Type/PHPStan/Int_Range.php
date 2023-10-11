<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Allows_Null;

class Int_Range implements Single
{
	use Allows_Null;
	use Common;

	//------------------------------------------------------------------------------------------ $max
	/** @var int|'max' */
	public int|string $max;

	//------------------------------------------------------------------------------------------ $min
	/** @var int|'min' */
	public int|string $min;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param int|'min'  $min
	 * @param int|'max'  $max
	 * @param Reflection $reflection
	 * @param bool       $allows_null
	 */
	public function __construct(
		int|string $min, int|string $max, Reflection $reflection, bool $allows_null
	) {
		$this->allows_null = $allows_null;
		$this->max         = $max;
		$this->min         = $min;
		$this->reflection  = $reflection;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return 'int<' . $this->min . ',' . $this->max . '>';
	}

}
