<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReturnTypeWillChange;

/**
 * @implements Interface\Reflection_Parameter<Class>
 * @template Class of object
 */
class Reflection_Parameter extends ReflectionParameter implements Interface\Reflection_Parameter
{
	use Attribute\Reflection_Parameter_Has;
	use Instantiate;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection Argument type does not match the declared Class is object
	 * @param array{class-string<Class>|Class,string}|Class|string $function
	 * @param non-negative-int|string $param
	 * @throws ReflectionException
	 */
	public function __construct(array|object|string $function, int|string $param)
	{
		parent::__construct($function, $param);
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$type      = (string)$this->getType();
		$reference = $this->isPassedByReference();
		$optional  = $this->isOptional();
		if ($optional) {
			/** @noinspection PhpUnhandledExceptionInspection isOptional */
			$default = $this->getDefaultValueConstantName();
			if (is_null($default)) {
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

	//---------------------------------------------------------------------------------- getAttribute
	/**
	 * @param class-string<A> $name
	 * @return ?Reflection_Attribute<$this,A>
	 * @template A of object
	 */
	public function getAttribute(string $name) : ?Reflection_Attribute
	{
		$attributes = $this->getAttributes($name, self::T_ALL);
		return ($attributes === [])
			? null
			: $attributes[0];
	}

	//------------------------------------------------------------------------- getAttributeInstances
	/**
	 * @param class-string<A>|null $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @return list<A>
	 * @template A of object
	 * @throws ReflectionException
	 */
	public function getAttributeInstances(string $name = null, int $flags = self::T_LOCAL) : array
	{
		$instances = [];
		foreach ($this->getAttributes($name, $flags) as $attribute) {
			/** @var list<A> $instances phpstan has problems outside of Reflection_Class */
			$instances[] = $attribute->newInstance();
		}
		return $instances;
	}

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * @param class-string<A>|null $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @phpstan-ignore-next-line not contravariant, but more precise rules
	 * @return list<Reflection_Attribute<$this,($name is null ? object : A)>>
	 * @template A of object
	 */
	public function getAttributes(?string $name = null, int $flags = self::T_LOCAL) : array
	{
		$attributes = [];
		$parents    = parent::getAttributes($name, $flags & ReflectionAttribute::IS_INSTANCEOF);
		foreach ($parents as $attribute) {
			$attributes[] = new Reflection_Attribute($attribute, $this);
		}
		return $attributes;
	}

	//-------------------------------------------------------------------------- getDeclaringFunction
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Method<Class>
	 */
	public function getDeclaringFunction() : Reflection_Method
	{
		/** @var ReflectionMethod $function */
		$function = parent::getDeclaringFunction();
		/** @noinspection PhpUnhandledExceptionInspection valid parent::getDeclaringFunction() */
		/** @phpstan-ignore-next-line Class of object */
		return new Reflection_Method($function->class, $function->name);
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

	//------------------------------------------------------------------------------------------ path
	public function path() : string
	{
		$function = $this->getDeclaringFunction();
		return $function->getFinalClassName() . '::' . $function->name . '::' . $this->name;
	}

}
