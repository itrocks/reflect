<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Allows_Null;

class Class_Constant implements Single
{
	use Allows_Null;
	use Common;

	//---------------------------------------------------------------------------------------- $class
	/** @var class-string */
	public string $class;

	//------------------------------------------------------------------------------------- $constant
	public string $constant;

	//----------------------------------------------------------------------------------- __construct
	/** @param class-string $class */
	public function __construct(
		string $class, string $constant, Reflection $reflection, bool $allows_null
	) {
		$this->allows_null = $allows_null;
		$this->class       = $class;
		$this->constant    = $constant;
		$this->reflection  = $reflection;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->class . '::' . $this->constant;
	}

}
