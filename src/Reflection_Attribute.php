<?php
namespace ITRocks\Reflect;

use Attribute;
use ITRocks\Reflect\Attribute\Has_Default;
use ITRocks\Reflect\Attribute\Has_Set_Declaring;
use ITRocks\Reflect\Attribute\Has_Set_Declaring_Class;
use ITRocks\Reflect\Attribute\Has_Set_Final;
use ITRocks\Reflect\Attribute\Has_Set_Reflection_Attribute;
use ITRocks\Reflect\Attribute\Inheritable;
use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Interface\Reflection_Class_Component;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Enriches ReflectionAttribute with:
 * - additional methods devoted to keep trace of the declaring and final Reflection objects
 * - newInstances propagates declaring and final Reflection objects
 * - Reflection_Attribute with a named attribute can be used to instantiate the default attribute,
 *   when not explicitly declared
 *
 * @implements Interface\Reflection_Attribute<Declaring,Instance>
 * @template-covariant Declaring of Reflection
 * @template-covariant Instance of object
 */
class Reflection_Attribute implements Interface\Reflection_Attribute
{
	use Instantiates;

	//------------------------------------------------------------------------------------ $arguments
	/** @var ?list<mixed> */
	protected ?array $arguments = null;

	//------------------------------------------------------------------------------------ $attribute
	/**
	 * @phpstan-ignore-next-line Template type Instance is declared as covariant, but occurs in invariant position
	 * @var ($target is null ? ReflectionAttribute<Instance> : null)
	 */
	protected ?ReflectionAttribute $attribute;

	//------------------------------------------------------------------------------------ $declaring
	protected Reflection $declaring;

	//------------------------------------------------------------------------------ $declaring_class
	/** @var ?Reflection_Class<object> */
	protected ?Reflection_Class $declaring_class = null;

	//---------------------------------------------------------------------------------------- $final
	/**
	 * @noinspection PhpDocFieldTypeMismatchInspection Property type does not match
	 * @phpstan-ignore-next-line Template type Instance is declared as covariant, but occurs in invariant position
	 * @var Declaring
	 */
	protected Reflection $final;

	//------------------------------------------------------------------------------------- $instance
	/**
	 * @noinspection PhpDocFieldTypeMismatchInspection Property type does not match
	 * @phpstan-ignore-next-line Template type Instance is declared as covariant, but occurs in invariant position
	 * @var ?Instance
	 */
	protected ?object $instance;

	//------------------------------------------------------------------------------- $is_inheritable
	/** @phpstan-ignore-next-line Uninitialized is its valid default state */
	protected bool $is_inheritable;

	//-------------------------------------------------------------------------------- $is_repeatable
	/** @phpstan-ignore-next-line Uninitialized is its valid default state */
	protected bool $is_repeatable;

	//---------------------------------------------------------------------------------- $is_repeated
	protected bool $is_repeated = false;

	//----------------------------------------------------------------------------------------- $name
	/** @var class-string */
	protected string $name;

	//--------------------------------------------------------------------------------------- $target
	/** @var ($instance is null ? int-mask-of<Attribute::TARGET_*> : null) */
	protected ?int $target;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(object|string $attribute_or_instance_or_name, Reflection $declaring)
	{
		if ($attribute_or_instance_or_name instanceof ReflectionAttribute) {
			/** @var ReflectionAttribute<Instance> $attribute_or_instance_or_name */
			$this->attribute = $attribute_or_instance_or_name;
			$this->instance  = null;
			$this->name      = $attribute_or_instance_or_name->getName();
			$this->target    = null;
		}
		else {
			$this->attribute = null;
			if (is_object($attribute_or_instance_or_name)) {
				$this->instance  = $attribute_or_instance_or_name;
				$this->name      = get_class($attribute_or_instance_or_name);
			}
			else {
				$this->instance  = null;
				$this->name      = $attribute_or_instance_or_name;
			}
			$this->target    = match(true) {
				($declaring instanceof ReflectionClass        ) => Attribute::TARGET_CLASS,
				($declaring instanceof ReflectionClassConstant) => Attribute::TARGET_CLASS_CONSTANT,
				($declaring instanceof ReflectionFunction     ) => Attribute::TARGET_FUNCTION,
				($declaring instanceof ReflectionMethod       ) => Attribute::TARGET_METHOD,
				($declaring instanceof ReflectionParameter    ) => Attribute::TARGET_PARAMETER,
				($declaring instanceof ReflectionProperty     ) => Attribute::TARGET_PROPERTY,
				default => 0
			};
		}
		$this->declaring = $declaring;
		$this->final     = $declaring;
	}

	//---------------------------------------------------------------------------------- getArguments
	public function getArguments() : array
	{
		if (isset($this->arguments)) {
			return $this->arguments;
		}
		/** @phpstan-ignore-next-line getArguments() always return list<mixed> */
		return isset($this->attribute)
			? $this->attribute->getArguments()
			: [];
	}

	//---------------------------------------------------------------------------------- getDeclaring
	public function getDeclaring() : Reflection
	{
		return $this->declaring;
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/** @return Reflection_Class<object> */
	public function getDeclaringClass(bool $trait = false) : Reflection_Class
	{
		if (isset($this->declaring_class) && !$trait) {
			return $this->declaring_class;
		}
		$declaring = $this->declaring;
		if ($declaring instanceof Reflection_Class) {
			return $declaring;
		}
		if ($declaring instanceof Reflection_Class_Component) {
			/** @phpstan-ignore-next-line Current interfacing level matches */
			return $declaring->getDeclaringClass($trait);
		}
		/** @var Reflection_Parameter<Declaring> $declaring Last possibility */
		return $declaring->getDeclaringFunction()->getDeclaringClass($trait);
	}

	//------------------------------------------------------------------------------------ getDefault
	public static function getDefault(string $name) : ?ReflectionAttribute
	{
		return class_exists($name)
			? ((new ReflectionClass($name))->getAttributes(Has_Default::class)[0] ?? null)
			: null;
	}

	//-------------------------------------------------------------------------------------- getFinal
	public function getFinal() : Reflection
	{
		return $this->final;
	}

	//--------------------------------------------------------------------------------- getFinalClass
	/** @return ?Reflection_Class<object> */
	public function getFinalClass() : ?Reflection_Class
	{
		if ($this->final instanceof Reflection_Class) {
			return $this->final;
		}
		elseif ($this->final instanceof Reflection_Class_Component) {
			/** @var Reflection_Class<object> $final */
			$final = $this->final->getFinalClass();
			return $final;
		}
		return null;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------- getTarget
	public function getTarget() : int
	{
		/** @phpstan-ignore-next-line valid target mask + attribute set if target not set */
		return $this->target ?? $this->attribute->getTarget();
	}

	//--------------------------------------------------------------------------------- isInheritable
	public function isInheritable() : bool
	{
		if (!isset($this->is_inheritable)) {
			/** @noinspection PhpUnhandledExceptionInspection class_exists */
			$this->is_inheritable = class_exists($this->name)
				&& ((new ReflectionClass($this->name))->getAttributes(Inheritable::class) !== []);
		}
		return $this->is_inheritable;
	}

	//---------------------------------------------------------------------------------- isRepeatable
	public function isRepeatable() : bool
	{
		if (!isset($this->is_repeatable)) {
			/** @noinspection PhpUnhandledExceptionInspection class_exists */
			$this->is_repeatable = !class_exists($name = $this->name)
				|| (($attributes = (new ReflectionClass($name))->getAttributes(Attribute::class)) === [])
				|| (
					!is_null($flags = $attributes[0]->getArguments()[0] ?? null)
					&& (($flags & Attribute::IS_REPEATABLE) > 0)
				);
		}
		return $this->is_repeatable;
	}

	//------------------------------------------------------------------------------------ isRepeated
	public function isRepeated() : bool
	{
		return $this->is_repeated || (isset($this->attribute) && $this->attribute->isRepeated());
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @noinspection PhpDocSignatureInspection Return type does not match the declared
	 * @return Instance
	 * @throws ReflectionException
	 */
	public function newInstance() : object
	{
		if (isset($this->attribute)) {
			if (!class_exists($this->name)) {
				throw new ReflectionException('Attribute class "' . $this->name . '" not found');
			}
			$instance = $this->attribute->newInstance();
		}
		elseif (isset($this->instance)) {
			$instance = clone $this->instance;
		}
		else {
			/** @var Instance $instance */
			$instance = static::newReflectionClass($this->name)->newInstanceArgs($this->getArguments());
		}
		if ($instance instanceof Has_Set_Declaring) {
			$instance->setDeclaring($this->declaring);
		}
		if (($instance instanceof Has_Set_Declaring_Class) && isset($this->declaring_class)) {
			$instance->setDeclaringClass($this->declaring_class);
		}
		if ($instance instanceof Has_Set_Final) {
			$instance->setFinal($this->final);
		}
		if ($instance instanceof Has_Set_Reflection_Attribute) {
			$instance->setReflectionAttribute($this);
		}
		return $instance;
	}

}
