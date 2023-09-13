<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Type\Reflection_Type;
use ReflectionParameter;
use ReturnTypeWillChange;

class Reflection_Parameter extends ReflectionParameter implements Interfaces\Reflection_Parameter
{
	use Instantiates;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$type      = strval($this->getType());
		$reference = $this->isPassedByReference();
		$optional  = $this->isOptional();
		if ($optional) {
			/** @noinspection PhpUnhandledExceptionInspection isOptional */
			$default = $this->getDefaultValueConstantName() ?? $this->getDefaultValue();
		}
		return (($type === '') ? '' : ($type . ' '))
			. ($reference ? '&' : '')
			. '$' . $this->getName()
			. ($optional ? (' = ' . $default) : '');
	}

	//--------------------------------------------------------------------------------- getDocComment
	public function getDocComment(int $filter = 0, bool $cache = true, bool $locate = false
	) : string|false
	{
		return false;
	}

	//--------------------------------------------------------------------------------------- getType
	/** @phpstan-ignore-next-line getType returns a proxy which is compatible with ReflectionType */
	#[ReturnTypeWillChange]
	public function getType() : Reflection_Type
	{
		return Type::of(parent::getType(), $this);
	}

}
