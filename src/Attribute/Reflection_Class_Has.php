<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionProperty;

trait Reflection_Class_Has
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
		$attributes = [];
		$parents    = parent::getAttributes($name, $flags & ReflectionAttribute::IS_INSTANCEOF);
		foreach ($parents as $attribute) {
			$attributes[] = new Reflection_Attribute($attribute, $this);
		}
		$known_repeated = isset($name) && isset($parents[1]);
		if (($flags & self::T_INHERIT) > 0) {
			$is_inheritable = is_null($name) || $this->isAttributeInheritable($name);
			$is_repeatable  = is_null($name) || $this->isAttributeRepeatable($name);
			if ($is_inheritable && (($attributes === []) || $is_repeatable)) {
				$parents = array_slice($this->getClassList($flags & self::T_INHERIT), 1);
				if ($parents !== []) {
					$declaring_class = new ReflectionProperty(Reflection_Attribute::class, 'declaring_class');
					$final           = new ReflectionProperty(Reflection_Attribute::class, 'final');
					$last_class = $this;
					foreach ($parents as $parent) {
						$parents = $parent->getAttributes($name, $flags & ReflectionAttribute::IS_INSTANCEOF);
						foreach ($parents as $parent_attribute) {
							$parent_class = $parent_attribute->getDeclaringClass();
							if ($parent_class->isClass()) {
								$last_class = $parent_class;
							}
							$declaring_class->setValue($parent_attribute, $last_class);
							$final->setValue($parent_attribute, $this);
							/** @var list<Reflection_Attribute<$this,object>> $attributes */
							$attributes[] = $parent_attribute;
						}
						if (!(is_null($name) || $is_repeatable || ($attributes === []))) {
							break;
						}
					}
				}
			}
		}
		if (isset($name) && ($attributes === []) && class_exists($name)) {
			$has_default = Reflection_Attribute::getDefault($name);
			if (isset($has_default)) {
				$arguments = new ReflectionProperty(Reflection_Attribute::class, 'arguments');
				$attribute = new Reflection_Attribute($name, $this);
				$arguments->setValue($attribute, $has_default->getArguments());
				$attributes[] = $attribute;
			}
		}
		if (!$known_repeated && isset($attributes[1])) {
			$repeated_by_name = [];
			foreach ($attributes as $attribute) {
				$attribute_name = $attribute->getName();
				$repeated_by_name[$attribute_name] = ($repeated_by_name[$attribute_name] ?? 0) + 1;
			}
			$is_repeated = new ReflectionProperty(Reflection_Attribute::class, 'is_repeated');
			foreach ($attributes as $attribute) {
				if (($repeated_by_name[$attribute->getName()] ?? 0) > 1) {
					$is_repeated->setValue($attribute, true);
				}
			}
		}
		$cache[$cache_key][strval($name)][$flags] = $attributes;
		return $attributes;
	}

}
