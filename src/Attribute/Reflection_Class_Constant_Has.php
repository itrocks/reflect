<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;

trait Reflection_Class_Constant_Has
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
		// TODO: Implement moreAttributes() method.
	}

}
