<?php
namespace ITRocks\Reflect\Attribute;

use Attribute;
use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

trait Reflection_Has
{

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
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|self::T_*> $flags
	 * @return list<A>
	 * @template A of object
	 * @throws ReflectionException
	 */
	public function getAttributeInstances(string $name = null, int $flags = 0) : array
	{
		$instances = [];
		foreach ($this->getAttributes($name, $flags) as $attribute) {
			$instances[] = $attribute->newInstance();
		}
		/** @var list<A> $instances phpstan has problems outside of Reflection_Class */
		return $instances;
	}

	//------------------------------------------------------------------------ isAttributeInheritable
	public function isAttributeInheritable(string $name) : bool
	{
		return !class_exists($name)
			|| ((new ReflectionClass($name))->getAttributes(Inheritable::class) !== []);
	}

	//------------------------------------------------------------------------- isAttributeRepeatable
	public function isAttributeRepeatable(string $name) : bool
	{
		return !class_exists($name)
			|| (($attributes = (new ReflectionClass($name))->getAttributes(Attribute::class)) === [])
			|| (
				!is_null($flags = $attributes[0]->getArguments()[0] ?? null)
				&& (($flags & Attribute::IS_REPEATABLE) > 0)
			);
	}

}
