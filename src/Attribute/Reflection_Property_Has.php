<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Interface;
use ITRocks\Reflect\Reflection_Attribute;
use ITRocks\Reflect\Reflection_Attribute_Override;
use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Reflection_Property;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

trait Reflection_Property_Has
{
	use Reflection_Has;

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

	//-------------------------------------------------------------------------------- moreAttributes
	/**
	 * @param list<Reflection_Attribute<$this,I>>&                        $attributes
	 * @param class-string<I>|null                                        $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @template I of object
	 */
	protected function moreAttributes(
		array &$attributes, ?string $name, int $flags, bool $is_repeatable
	) : void
	{
		$class_list          = $this->getFinalClass()->getClassList(Reflection_Class::T_ALL);
		$class               = reset($class_list);
		$declaring_class     = new ReflectionProperty(Reflection_Attribute::class, 'declaring_class');
		$is_instanceof_flags = $flags & ReflectionAttribute::IS_INSTANCEOF;
		$last_class          = $class;
		$property            = $this;
		$declaring_name      = $property->getDeclaringClassName(true);
		$final               = new ReflectionProperty(Reflection_Attribute::class, 'final');
		$last_property       = $property;
		$next_property_stop  = $property->isPrivate();
		$property_attributes = $attributes;
		$property_name       = $this->name;
		$property_visible    = true;
		$skip_trait_names    = [];
		$attributes          = [];
		while (($class !== false) && ($is_repeatable || ($attributes === []))) {
			if ($class->isClass()) {
				$last_class = $class;
			}
			if (!(
				$property_visible
				|| $class->isInterface()
				|| in_array($class->name, $skip_trait_names, true)
			)) {
				try {
					/** @var Reflection_Property<object> $property */
					$property         = static::newReflectionProperty($class->name, $property_name);
					$property_visible = true;
				}
				catch (ReflectionException) {
					if ($class->isClass()) {
						break;
					}
					$skip_trait_names = $class->getTraitNames(Reflection_Class::T_USE);
				}
				if ($property_visible) {
					if (($next_property_stop || $property->isPrivate()) && $class->isClass()) {
						break;
					}
					$declaring_name = $property->getDeclaringClassName(true);
					$is_trait       = $class->isTrait();
					if (!$is_trait) {
						$last_class    = $class;
						$last_property = $property;
					}
					$property_attributes = $property->getAttributes($name, $is_instanceof_flags);
					foreach ($property_attributes as $attribute) {
						if ($is_trait) {
							$declaring_class->setValue($attribute, $last_class);
						}
						$final->setValue($attribute, $this);
					}
					$skip_trait_names = [];
				}
			}
			if (($flags & static::T_OVERRIDE) > 0) {
				foreach ($class->getAttributes(Override::class) as $override) {
					$arguments = $override->getArguments();
					if (array_shift($arguments) !== $property_name) {
						continue;
					}
					/** @var list<class-string<I>|I> $arguments */
					foreach ($arguments as $instance_or_name) {
						if (
							is_null($name)
							|| (
								($is_instanceof_flags > 0)
								? is_a($instance_or_name, $name, true)
								: ($name === (
									is_object($instance_or_name) ? get_class($instance_or_name) : $instance_or_name
								))
							)
						) {
							$attribute = new Reflection_Attribute_Override(
								$instance_or_name, $last_property, $override
							);
							$declaring_class->setValue($attribute, $last_class);
							$final->setValue($attribute, $this);
							$attributes[] = $attribute;
						}
					}
				}
			}
			if ($class->name === $declaring_name) {
				$attributes       = array_merge($attributes, $property_attributes);
				$property_visible = false;
			}
			$class = next($class_list);
		}
	}

}
