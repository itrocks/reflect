<?php
namespace ITRocks\Reflect\Type;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Interface\Reflection_Method;
use ITRocks\Reflect\Interface\Reflection_Parameter;
use ITRocks\Reflect\Interface\Reflection_Property;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\PHPStan\Parser;

trait Common
{

	//----------------------------------------------------------------------------------- $reflection
	protected Reflection $reflection;

	//----------------------------------------------------------------------------- getDocCommentType
	public function getDocCommentType() : Reflection_Type
	{
		return (new Parser($this->reflection, $this->getDocCommentTypeString()))->parse();
	}

	//----------------------------------------------------------------------- getDocCommentTypeString
	protected function getDocCommentTypeString() : string
	{
		$doc_comment = ($this->reflection instanceof Reflection_Parameter)
			? $this->reflection->getDeclaringFunction()->getDocComment(Reflection::T_INHERIT)
			: $this->reflection->getDocComment(Reflection::T_INHERIT);
		$expression = match(true) {
			$this->reflection instanceof Reflection_Method
				=> '@return\s+(^\s*)',
			$this->reflection instanceof Reflection_Parameter
				=> '@param\s+([^\s]*)\s+\$' . $this->reflection->getName() . '(?:\s|$)',
			$this->reflection instanceof Reflection_Property
				=> '@var\s+(^\s*)',
			default => '(@undef)' // should never occur
		};
		$expression = '%^\s*(?:/\*)?\*\s*' . $expression . '%';

		return (bool)preg_match($expression, (string)$doc_comment, $matches)
			? $matches[1]
			: '';
	}

	//--------------------------------------------------------------------------------- getReflection
	public function getReflection() : Reflection
	{
		return $this->reflection;
	}

}
