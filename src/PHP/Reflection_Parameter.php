<?php
namespace ITRocks\Reflect\PHP;

use ITRocks\Reflect\Interface;
use ITRocks\Reflect\Interface\Reflection_Attribute;
use ITRocks\Reflect\Interface\Reflection_Method;
use ReflectionException;

/**
 * @implements Interface\Reflection_Parameter<Class>
 * @template Class of object
 */
class Reflection_Parameter implements Interface\Reflection_Parameter
{
	use Instantiate;

	//------------------------------------------------------------------------------------- $function
	/**
	 * @noinspection PhpDocFieldTypeMismatchInspection Argument type does not match the declared Class is object
	 * @var array{class-string<Class>|Class,string}|Class|string
	 */
	protected array|object|string $function;

	//---------------------------------------------------------------------------------------- $param
	/** @var non-negative-int|string */
	protected int|string $param;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection Argument type does not match the declared Class is object
	 * @param array{class-string<Class>|Class,string}|Class|string $function
	 * @param non-negative-int|string $param
	 */
	public function __construct(array|object|string $function, int|string $param)
	{
		$this->function = $function;
		$this->param    = $param;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		// TODO: Implement __toString() method.
		return '';
	}

	//---------------------------------------------------------------------------------- getAttribute
	public function getAttribute(string $name) : ?Reflection_Attribute
	{
		// TODO: Implement getAttribute() method.
		return null;
	}

	//------------------------------------------------------------------------- getAttributeInstances
	public function getAttributeInstances(
		string $name = null, int $flags = Interface\Reflection_Parameter::T_LOCAL
	) : array
	{
		// TODO: Implement getAttributeInstances() method.
		return [];
	}

	//--------------------------------------------------------------------------------- getAttributes
	public function getAttributes(
		string $name = null, int $flags = Interface\Reflection_Parameter::T_LOCAL
	) : array
	{
		// TODO: Implement getAttributes() method.
		return [];
	}

	//-------------------------------------------------------------------------- getDeclaringFunction
	/** @throws ReflectionException */
	public function getDeclaringFunction() : Reflection_Method
	{
		// TODO: Implement getDeclaringFunction() method.
		throw new ReflectionException('TODO: Implement getDeclaringFunction() method.');
	}

	//--------------------------------------------------------------------------------- getDocComment
	public function getDocComment(
		int $filter = Interface\Reflection_Parameter::T_LOCAL, bool $cache = true, bool $locate = false
	) : string|false
	{
		// TODO: Implement getDocComment() method.
		return false;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		// TODO: Implement getName() method.
		return '';
	}

	//------------------------------------------------------------------------------------------ path
	public function path() : string
	{
		// TODO: Implement path() method.
		return '';
	}

}
