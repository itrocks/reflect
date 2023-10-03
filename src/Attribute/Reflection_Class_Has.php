<?php
namespace ITRocks\Reflect\Attribute;

use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionProperty;

trait Reflection_Class_Has
{
	use Reflection_Has;

	//-------------------------------------------------------------------------------- moreAttributes
	/**
	 * @param list<Reflection_Attribute<$this,A>>&                        $attributes
	 * @param class-string<A>|null                                        $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @template A of object
	 */
	protected function moreAttributes(
		array &$attributes, ?string $name, int $flags, bool $is_repeatable
	) : void
	{
		$parents = array_slice($this->getClassList($flags & self::T_INHERIT), 1);
		if ($parents === []) {
			return;
		}
		$final = new ReflectionProperty(Reflection_Attribute::class, 'final');
		foreach ($parents as $parent) {
			$parents = $parent->getAttributes($name, $flags & ReflectionAttribute::IS_INSTANCEOF);
			foreach ($parents as $attribute) {
				$final->setValue($attribute, $this);
				/** @var list<Reflection_Attribute<$this,object>> $attributes */
				$attributes[] = $attribute;
			}
			if (!($is_repeatable || ($attributes === []))) {
				break;
			}
		}
	}

}
