<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Type\Reflection_Type;
use ReflectionException;
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
			$default = $this->getDefaultValueConstantName();
			if (!isset($default)) {
				/** @noinspection PhpUnhandledExceptionInspection isOptional */
				$default = $this->getDefaultValue();
				if (is_string($default)) {
					$default = "'" . str_replace("'", "\\'", $default) . "'";
				}
			}
		}
		return (($type === '') ? '' : ($type . ' '))
			. ($reference ? '&' : '')
			. '$' . $this->getName()
			. ($optional ? (' = ' . $default) : '');
	}

	//--------------------------------------------------------------------------------- getDocComment
	/** @throws ReflectionException */
	public function getDocComment(
		int $filter = self::T_LOCAL, bool $cache = true, bool $locate = false
	) : string|false
	{
		throw new ReflectionException('This feature has not been implemented yet');
	}

	//--------------------------------------------------------------------------------------- getType
	/** @phpstan-ignore-next-line getType returns a proxy which is compatible with ReflectionType */
	#[ReturnTypeWillChange]
	public function getType() : Reflection_Type
	{
		return Type::of(parent::getType(), $this);
	}

}
