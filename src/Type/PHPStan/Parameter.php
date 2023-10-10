<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Type\Interface\Reflection_Type;

class Parameter implements Reflection_Type
{

	//----------------------------------------------------------------------------------------- $name
	public string $name;

	//------------------------------------------------------------------------------------- $optional
	public bool $optional;

	//------------------------------------------------------------------------------------ $reference
	public bool $reference;

	//----------------------------------------------------------------------------------------- $type
	public Reflection_Type $type;

	//------------------------------------------------------------------------------------- $variadic
	public bool $variadic;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		Reflection_Type $type, bool $variadic, bool $reference, string $name, bool $optional
	) {
		$this->name      = $name;
		$this->optional  = $optional;
		$this->reference = $reference;
		$this->type      = $type;
		$this->variadic  = $variadic;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$string = (string)$this->type;
		if ($this->variadic) {
			if ($this->reference || ($this->name !== '')) {
				$string .= ' ';
			}
			$string .= '...';
		}
		if ($this->reference) {
			if (!$this->variadic) {
				$string .= ' ';
			}
			$string .= '&';
		}
		if ($this->name !== '') {
			if (!($this->reference || $this->variadic)) {
				$string .= ' ';
			}
			$string .= '$' . $this->name;
		}
		if ($this->optional) {
			$string .= '=';
		}
		return $string;
	}

	//------------------------------------------------------------------------------------ allowsNull
	public function allowsNull() : bool
	{
		return $this->type->allowsNull();
	}

}
