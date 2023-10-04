<?php
namespace ITRocks\Reflect\Attribute;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS), Inheritable]
class Override_Method
{
	use Repeatable;

	//------------------------------------------------------------------------------------ $overrides
	/** @var list<object|class-string> */
	public array $overrides;

	//----------------------------------------------------------------------------------- __construct
	/** @param non-empty-string $property_name */
	public function __construct(public string $property_name, object|string... $overrides)
	{
		/** @var list<object|class-string> $overrides */
		$this->overrides = $overrides;
	}

}
