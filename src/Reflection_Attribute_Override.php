<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Interface\Reflection;
use ReflectionAttribute;

/**
 * @extends Reflection_Attribute<Declaring,Instance>
 * @template-covariant Declaring of Reflection
 * @template-covariant Instance of object
 */
class Reflection_Attribute_Override extends Reflection_Attribute
{

	//------------------------------------------------------------------------------------- $override
	/** @var Reflection_Attribute<Reflection,object> */
	protected Reflection_Attribute $override;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $attribute Argument type does not match the declared
	 * @param ReflectionAttribute<Instance>|Instance|class-string<Instance> $attribute_or_instance_or_name
	 * @param Declaring                                                     $declaring
	 * @param Reflection_Attribute<Reflection,object>                       $override
	 */
	public function __construct(
		object|string $attribute_or_instance_or_name, Reflection $declaring,
		Reflection_Attribute $override = null
	) {
		parent::__construct($attribute_or_instance_or_name, $declaring);
		$this->override = $override ?? $this;
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/** @return Reflection_Class<object> */
	public function getDeclaringClass(bool $trait = false) : Reflection_Class
	{
		if ($trait && isset($this->override)) {
			return $this->override->getDeclaringClass($trait);
		}
		return parent::getDeclaringClass($trait);
	}

	//----------------------------------------------------------------------------------- getOverride
	/** @return Reflection_Attribute<Reflection,object> */
	public function getOverride() : Reflection_Attribute
	{
		return $this->override;
	}

}
