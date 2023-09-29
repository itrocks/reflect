<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Interface;
use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionProperty;

trait Reflection_Property_Has
{
	use Reflection_Has;

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * @param class-string<A>|null $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @phpstan-ignore-next-line not contravariant, but more precise rules
	 * @return list<Reflection_Attribute<$this,($name is null ? object : A)>>
	 * @template A of object
	 */
	public function getAttributes(string $name = null, int $flags = 0) : array
	{
		/** @var array<string,array<string,array<int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*>,list<Reflection_Attribute<$this,A>>>>> $cache */
		static $cache = [];
		$cache_key    = strval($this);
		if (isset($cache[$cache_key][strval($name)][$flags])) {
			return $cache[$cache_key][strval($name)][$flags];
		}
		$already    = [];
		$attributes = [];
		foreach (parent::getAttributes($name, $flags) as $attribute) {
			$already[$attribute->getName()] = true;
			$attributes[] = new Reflection_Attribute($attribute, $this);
		}
		$is_inheritable = is_null($name) || $this->isAttributeInheritable($name);
		if (!$is_inheritable) {
			$cache[$cache_key][strval($name)][$flags] = $attributes;
			return $attributes;
		}
		$is_repeatable = is_null($name) || $this->isAttributeRepeatable($name);
		if (!$is_repeatable && isset($already[$name])) {
			$cache[$cache_key][strval($name)][$flags] = $attributes;
			return $attributes;
		}
		$overridden_property = $this->getParent();
		if (isset($overridden_property)) {
			$final_property    = new ReflectionProperty(Reflection_Attribute::class, 'final');
			$parent_attributes = $overridden_property->getAttributes($name, $flags);
			foreach ($parent_attributes as $parent_attribute) {
				$final_property->setValue($parent_attribute, $this);
			}
		}
		// get matching property and $name overrides
		$declaring_class = new ReflectionProperty(Reflection_Attribute::class, 'declaring_class');
		$overrides      = $this->getFinalClass()->getAttributes(Override::class);
		$property_name  = $this->name;
		foreach ($overrides as $override) {
			$arguments = $override->getArguments();
			if (array_shift($arguments) !== $property_name) {
				continue;
			}
			foreach ($arguments as $attribute) {
				if (is_object($attribute) && (!isset($name) || is_a($attribute, $name, true))) {
					$attribute = new Reflection_Attribute($attribute, $this);
					$declaring_class->setValue($attribute, $override->getDeclaringClass());
					$attributes[] = $attribute;
				}
			}
		}
		$cache[$cache_key][strval($name)][$flags] = $attributes;
		return $attributes;
	}

	//----------------------------------------------------------------------------- hasSameAttributes
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param Interface\Reflection_Property<object> $property
	 */
	private function hasSameAttributes(Interface\Reflection_Property $property) : bool
	{
		$attributes1 = parent::getAttributes();
		/** @noinspection PhpUnhandledExceptionInspection exists */
		/** @var list<ReflectionAttribute<object>> $attributes2 */
		$attributes2 = (new ReflectionMethod('ReflectionProperty::getAttributes'))->invoke($property);
		if (count($attributes1) !== count($attributes2)) {
			return false;
		}
		$attribute1 = reset($attributes1);
		$attribute2 = reset($attributes2);
		while (($attribute1 !== false) && ($attribute2 !== false)) {
			if (
				($attribute1->getName() !== $attribute2->getName())
				|| ($attribute1->getArguments() !== $attribute2->getArguments())
			) {
				return false;
			}
			$attribute1 = next($attributes1);
			$attribute2 = next($attributes2);
		}
		return true;
	}

}
