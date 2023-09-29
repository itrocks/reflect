<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionProperty;

trait Reflection_Class_Has
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
		$parents = array_slice($this->getClassList($flags & self::T_INHERIT), 1);
		if ($parents === []) {
			return [];
		}
		$attributes      = [];
		$declaring_class = new ReflectionProperty(Reflection_Attribute::class, 'declaring_class');
		$final           = new ReflectionProperty(Reflection_Attribute::class, 'final');
		$last_class      = $this;
		foreach ($parents as $parent) {
			$parents = $parent->getAttributes($name, $flags & ReflectionAttribute::IS_INSTANCEOF);
			foreach ($parents as $attribute) {
				$parent_class = $attribute->getDeclaringClass();
				if ($parent_class->isClass()) {
					$last_class = $parent_class;
				}
				$declaring_class->setValue($attribute, $last_class);
				$final->setValue($attribute, $this);
				/** @var list<Reflection_Attribute<$this,object>> $attributes */
				$attributes[] = $attribute;
			}
			if (!(is_null($name) || $is_repeatable || ($attributes === []))) {
				break;
			}
		}
		return $attributes;
	}

}
