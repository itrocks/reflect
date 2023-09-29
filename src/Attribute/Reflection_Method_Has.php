<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionProperty;

trait Reflection_Method_Has
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
		foreach (parent::getAttributes($name, $flags) as $attribute) {
			$attributes[] = new Reflection_Attribute($attribute, $this);
		}
		if (($flags & self::T_INHERIT) > 0) {
			$is_inheritable = is_null($name) || $this->isAttributeInheritable($name);
			$is_repeatable  = is_null($name) || $this->isAttributeRepeatable($name);
			if ($is_inheritable && (($attributes === []) || $is_repeatable)) {
				$overridden_method = $this->getParent();
				if (isset($overridden_method)) {
					$final   = new ReflectionProperty(Reflection_Attribute::class, 'final');
					$parents = $overridden_method->getAttributes($name, $flags);
					foreach ($parents as $parent_attribute) {
						$final->setValue($parent_attribute, $this);
						$attributes[] = $parent_attribute;
					}
				}
			}
		}
		$cache[$cache_key][strval($name)][$flags] = $attributes;
		return $attributes;
	}

}
