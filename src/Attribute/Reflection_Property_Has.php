<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Interface;
use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

trait Reflection_Property_Has
{
	use Reflection_Has;

	//---------------------------------------------------------------------------- getOtherAttributes
	/**
	 * @param class-string<A>|null $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @phpstan-ignore-next-line not contravariant, but more precise rules
	 * @return list<Reflection_Attribute<$this,($name is null ? object : A)>>
	 * @template A of object
	 */
	protected function getOtherAttributes(?string $name, int $flags, bool $is_repeatable) : array
	{
		$attributes      = [];
		$declaring_class = new ReflectionProperty(Reflection_Attribute::class, 'declaring_class');
		$final           = new ReflectionProperty(Reflection_Attribute::class, 'final');
		// traits property attributes
		foreach ($this->getDeclaringClass(true)->getTraitNames() as $trait_name) {
			try {
				/** @var static<A> $parent */
				$parent = static::newReflectionProperty($trait_name, $this->name);
			}
			catch (ReflectionException) {
				continue;
			}
			foreach ($parent->getAttributes($name, $flags) as $attribute) {
				$declaring_class->setValue($attribute, $this->getDeclaringClass());
				$final->setValue($attribute, $this);
				/** @var list<Reflection_Attribute<$this,($name is null ? object : A)>> $attributes */
				/** @phpstan-ignore-next-line Forced by $final->setValue */
				$attributes[] = $attribute;
			}
		}
		// parent property attributes
		$parent = $this->getParent();
		if (isset($parent) && !$parent->isPrivate()) {
			foreach ($parent->getAttributes($name, $flags) as $attribute) {
				$final->setValue($attribute, $this);
				$attributes[] = $attribute;
			}
		}
		// get matching property and $name overrides
		$overrides = $this->getFinalClass()->getAttributes(Override::class);
		if ($overrides !== []) {
			$property_name = $this->name;
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
		}
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
