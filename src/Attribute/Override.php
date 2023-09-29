<?php
namespace ITRocks\Reflect\Attribute;

use Attribute;
use Stringable;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS), Inheritable]
class Override
{
	use Repeatable;

	//------------------------------------------------------------------------------------ $overrides
	/** @var array<object> */
	public array $overrides;

	//-------------------------------------------------------------------------------- $property_name
	/** @var non-empty-string */
	public string $property_name;

	//----------------------------------------------------------------------------------- __construct
	/** @param non-empty-string $property_name */
	public function __construct(string $property_name, object... $overrides)
	{
		$this->overrides     = $overrides;
		$this->property_name = $property_name;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$overrides = [];
		foreach ($this->overrides as $override) {
			$overrides[] = ($override instanceof Stringable)
				? strval($override)
				: get_class($override);
		}
		return $this->property_name . ', ' . join(', ', $overrides);
	}

}
