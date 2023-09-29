<?php
namespace ITRocks\Reflect\Interface;

use Attribute;
use ITRocks\Reflect\Attribute\Has_Default;
use ReflectionAttribute;
use ReflectionException;

/**
 * @template Declaring of Reflection
 * @template Instance of object
 */
interface Reflection_Attribute
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $attribute Argument type does not match the declared
	 * @param ReflectionAttribute<Instance>|Instance|class-string<Instance> $attribute_or_instance_or_name
	 * @param Declaring $declaring
	 */
	public function __construct(object|string $attribute_or_instance_or_name, Reflection $declaring);

	//---------------------------------------------------------------------------------- getArguments
	/** @return list<mixed> */
	public function getArguments() : array;

	//---------------------------------------------------------------------------------- getDeclaring
	/**
	 * @noinspection PhpDocSignatureInspection Return type does not match the declared
	 * @return Declaring
	 */
	public function getDeclaring() : Reflection;

	//----------------------------------------------------------------------------- getDeclaringClass
	/** @return ?Reflection_Class<object> */
	public function getDeclaringClass(bool $trait = false) : ?Reflection_Class;

	//------------------------------------------------------------------------------------ getDefault
	/**
	 * @param class-string<object> $name
	 * @return ?ReflectionAttribute<Has_Default>
	 */
	public static function getDefault(string $name) : ?ReflectionAttribute;

	//-------------------------------------------------------------------------------------- getFinal
	/**
	 * @noinspection PhpDocSignatureInspection Declaring of Reflection
	 * @return Declaring
	 */
	public function getFinal() : Reflection;

	//--------------------------------------------------------------------------------- getFinalClass
	/** @return ?Reflection_Class<object> */
	public function getFinalClass() : ?Reflection_Class;

	//--------------------------------------------------------------------------------------- getName
	/** @return class-string<Instance>|string */
	public function getName() : string;

	//------------------------------------------------------------------------------------- getTarget
	/** @return int-mask-of<Attribute::TARGET_*> */
	public function getTarget() : int;

	//--------------------------------------------------------------------------------- isInheritable
	public function isInheritable() : bool;

	//---------------------------------------------------------------------------------- isRepeatable
	public function isRepeatable() : bool;

	//------------------------------------------------------------------------------------ isRepeated
	public function isRepeated() : bool;

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @noinspection PhpDocSignatureInspection Return type does not match the declared
	 * @return Instance
	 * @throws ReflectionException
	 */
	public function newInstance() : object;

}
