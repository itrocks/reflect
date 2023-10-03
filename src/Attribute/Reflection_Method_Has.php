<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionProperty;

trait Reflection_Method_Has
{
	use Reflection_Has;

	//-------------------------------------------------------------------------------- moreAttributes
	/**
	 * @param list<Reflection_Attribute<$this,A>>&                        $attributes
	 * @param ?class-string<A>                                            $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @phpstan-ignore-next-line not contravariant, but more precise rules
	 * @template A of object
	 */
	protected function moreAttributes(
		array &$attributes, ?string $name, int $flags, bool $is_repeatable
	) : void
	{
		$parent = $this->getParent();
		if (is_null($parent) || $parent->isPrivate()) {
			return;
		}
		$final   = new ReflectionProperty(Reflection_Attribute::class, 'final');
		$parents = $parent->getAttributes($name, $flags);
		foreach ($parents as $attribute) {
			$final->setValue($attribute, $this);
			$attributes[] = $attribute;
		}
	}

}
