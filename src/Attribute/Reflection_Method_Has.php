<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionProperty;

trait Reflection_Method_Has
{
	use Reflection_Has;

	//---------------------------------------------------------------------------- getOtherAttributes
	/**
	 * @param ?class-string<A> $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @phpstan-ignore-next-line not contravariant, but more precise rules
	 * @return list<Reflection_Attribute<$this,($name is null ? object : A)>>
	 * @template A of object
	 */
	protected function getOtherAttributes(?string $name, int $flags, bool $is_repeatable) : array
	{
		$parent = $this->getParent();
		if (is_null($parent) || $parent->isPrivate()) {
			return [];
		}
		$attributes = [];
		$final      = new ReflectionProperty(Reflection_Attribute::class, 'final');
		$parents    = $parent->getAttributes($name, $flags);
		foreach ($parents as $attribute) {
			$final->setValue($attribute, $this);
			$attributes[] = $attribute;
		}
		return $attributes;
	}

}
