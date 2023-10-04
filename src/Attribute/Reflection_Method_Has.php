<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Reflection_Attribute;
use ITRocks\Reflect\Reflection_Attribute_Override;
use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Reflection_Method;
use ReflectionAttribute;
use ReflectionException;
use ReflectionProperty;

trait Reflection_Method_Has
{
	use Has_Same_Attributes;
	use Reflection_Has;

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
		$declaring_name      = $this->getDeclaringClassName(true);
		$final               = new ReflectionProperty(Reflection_Attribute::class, 'final');
		$is_instanceof_flags = $flags & ReflectionAttribute::IS_INSTANCEOF;
		$last_class          = $class;
		$last_method         = $this;
		$method              = $this;
		$method_attributes   = $attributes;
		$method_name         = $this->name;
		$method_visible      = true;
		$next_method_stop    = $this->isPrivate();
		$skip_class_names    = [];
		$attributes          = [];
		while (($class !== false) && ($is_repeatable || ($attributes === []))) {
			if ($class->isClass()) {
				$last_class = $class;
			}
			if (!($method_visible || in_array($class->name, $skip_class_names, true))) {
				try {
					/** @var Reflection_Method<object> $method */
					$method         = static::newReflectionMethod($class->name, $method_name);
					$method_visible = true;
				}
				catch (ReflectionException) {
					if ($class->isClass()) {
						break;
					}
					$skip_class_names = $class->isInterface()
						? $class->getInterfaceNames(Reflection_Class::T_IMPLEMENTS)
						: $class->getTraitNames(Reflection_Class::T_USE);
				}
				if ($method_visible) {
					if (($next_method_stop || $method->isPrivate()) && $class->isClass()) {
						break;
					}
					$declaring_name = $method->getDeclaringClassName(true);
					$is_trait       = $class->isTrait();
					if (!$is_trait) {
						$last_class  = $class;
						$last_method = $method;
					}
					$method_attributes = $method->getAttributes($name, $is_instanceof_flags);
					foreach ($method_attributes as $attribute) {
						if ($is_trait) {
							$declaring_class->setValue($attribute, $last_class);
						}
						$final->setValue($attribute, $this);
					}
					$skip_class_names = [];
				}
			}
			if (($flags & static::T_OVERRIDE) > 0) {
				foreach ($class->getAttributes(Override_Method::class) as $override) {
					$arguments = $override->getArguments();
					if (array_shift($arguments) !== $method_name) {
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
								$instance_or_name, $last_method, $override
							);
							$declaring_class->setValue($attribute, $last_class);
							$final->setValue($attribute, $this);
							$attributes[] = $attribute;
						}
					}
				}
			}
			if ($class->name === $declaring_name) {
				$attributes     = array_merge($attributes, $method_attributes);
				$method_visible = false;
			}
			$class = next($class_list);
		}
	}

}
