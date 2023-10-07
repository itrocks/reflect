<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\Interface\Multiple;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Allows_Null;
use ITRocks\Reflect\Type\Undefined;

class Collection implements Single
{
	use Allows_Null;
	use Common;

	//----------------------------------------------------------------------------------- $dimensions
	/** @var non-negative-int */
	protected int $dimensions = 0;

	//----------------------------------------------------------------------------------------- $type
	protected Reflection_Type $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Reflection_Type $type, Reflection $reflection, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->reflection  = $reflection;
		$this->type        = $type;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		if ($this->dimensions > 0) {
			return $this->type . str_repeat('[]', $this->dimensions);
		}
		return 'array<' . $this->type . '>';
	}

	//--------------------------------------------------------------------------------- getDimensions
	/** @return non-negative-int */
	public function getDimensions() : int
	{
		return $this->dimensions;
	}

	//-------------------------------------------------------------------------------- getElementType
	public function getElementType() : Single
	{
		if ($this->type instanceof Multiple) {
			return $this->type->getElementType();
		}
		elseif ($this->type instanceof Single) {
			return $this->type;
		}
		else {
			return new Undefined($this->reflection);
		}
	}

	//--------------------------------------------------------------------------------------- getType
	public function getType() : Reflection_Type
	{
		return $this->type;
	}

	//---------------------------------------------------------------------------------- ofDimensions
	/** @param positive-int $dimensions */
	public static function ofDimensions(
		Single $type, int $dimensions, Reflection $reflection, bool $allows_null
	) : static
	{
		/** @phpstan-ignore-next-line Will keep this form TODO ensure this by contract */
		$collection = new static($type, $reflection, $allows_null);
		$collection->dimensions = $dimensions;
		return $collection;
	}

}
