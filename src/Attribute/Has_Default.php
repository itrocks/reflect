<?php
namespace ITRocks\Reflect\Attribute;

use Attribute;

/**
 * Designates an Attribute that is always set, and always has a default instance.
 * When you call getAttributes($name) or getAttribute($name), if none of this attribute is set,
 * a default one will be automatically instantiated.
 * getAttributes() without $name will not return Has_Default attributes.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Has_Default
{

	//------------------------------------------------------------------------------------ $arguments
	/** @var list<mixed> */
	public array $arguments;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(mixed... $arguments)
	{
		/** @var list<mixed> $arguments */
		$this->arguments = $arguments;
	}

}
