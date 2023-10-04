<?php
namespace ITRocks\Reflect\Attribute;

use ReflectionAttribute;
use ReflectionMethod;

trait Has_Same_Attributes
{

	//----------------------------------------------------------------------------- hasSameAttributes
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param self<object> $reflection
	 */
	private function hasSameAttributes(self $reflection) : bool
	{
		$parent_class = $root_class = get_class($this);
		while ($parent_class !== false) {
			$root_class   = $parent_class;
			$parent_class = get_parent_class($parent_class);
		}
		/** @noinspection PhpUnhandledExceptionInspection exists */
		$get_attributes = new ReflectionMethod($root_class, 'getAttributes');

		/** @noinspection PhpUnhandledExceptionInspection exists */
		/** @var list<ReflectionAttribute<object>> $attributes1 */
		$attributes1 = $get_attributes->invoke($this);
		/** @noinspection PhpUnhandledExceptionInspection exists */
		/** @var list<ReflectionAttribute<object>> $attributes2 */
		$attributes2 = $get_attributes->invoke($reflection);

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
