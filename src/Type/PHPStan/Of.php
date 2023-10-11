<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Multiple;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\Undefined;

class Of extends Named
{

	//----------------------------------------------------------------------------------------- $type
	public Reflection_Type $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		string $name, Reflection_Type $type, Reflection $reflection, bool $allows_null
	) {
		parent::__construct($name, $reflection, $allows_null);
		$this->type = $type;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->name . '<' . $this->type . '>';
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

}
