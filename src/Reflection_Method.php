<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Type\Reflection_Type;
use ReflectionException;
use ReflectionMethod;
use ReturnTypeWillChange;

/**
 * A rich extension of the PHP ReflectionMethod class, adding:
 * - annotations management
 */
class Reflection_Method extends ReflectionMethod implements Interfaces\Reflection_Method
{
	use Instantiates;

	//---------------------------------------------------------------------------------------- $cache
	/** @var array{declaring_trait:Reflection_Class,final_class:class-string,final_class_raw:class-string|object|string,parent:static|null}|array<void> */
	private array $cache = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param class-string|object|string $object_or_class_or_method
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class_or_method, string $method = null)
	{
		$this->cache['final_class_raw'] = $object_or_class_or_method;
		if (is_null($method)) {
			// @phpstan-ignore-next-line Call with an object will result in a fatal error, as expected
			parent::__construct($object_or_class_or_method);
		}
		else {
			parent::__construct($object_or_class_or_method, $method);
		}
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected method
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class<object>
	 */
	public function getDeclaringClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->class is always valid */
		return new Reflection_Class($this->class);
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/** @return class-string declaring class name */
	public function getDeclaringClassName() : string
	{
		return $this->class;
	}

	//----------------------------------------------------------------------------- getDeclaringTrait
	/** @return Reflection_Class<object> */
	public function getDeclaringTrait() : Reflection_Class
	{
		if (isset($this->cache['declaring_trait'])) {
			/** @phpstan-ignore-next-line declaring_trait is always initialized as a Reflection_Class */
			return $this->cache['declaring_trait'];
		}
		$declaring_trait = $this->getDeclaringTraitInternal($this->getDeclaringClass());
		$this->cache['declaring_trait'] = $declaring_trait;
		return $declaring_trait;
	}

	//--------------------------------------------------------------------- getDeclaringTraitInternal
	/**
	 * @param Reflection_Class<object> $class
	 * @return Reflection_Class<object>
	 */
	private function getDeclaringTraitInternal(Reflection_Class $class) : Reflection_Class
	{
		$traits = $class->getTraits();
		foreach ($traits as $trait) {
			$properties = $trait->getProperties(0);
			if (isset($properties[$this->name])) {
				return $this->getDeclaringTraitInternal($trait);
			}
		}
		return $class;
	}

	//------------------------------------------------------------------------- getDeclaringTraitName
	public function getDeclaringTraitName() : string
	{
		return $this->getDeclaringTrait()->name;
	}

	//--------------------------------------------------------------------------------- getDocComment
	public function getDocComment(int $filter = 0) : string|false
	{
		return parent::getDocComment();
	}

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class<object> The one where the method came from with a call to get...()
	 */
	public function getFinalClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->final_class is valid */
		return static::newReflectionClass($this->getFinalClassName());
	}

	//----------------------------------------------------------------------------- getFinalClassName
	/** @return class-string The one where the property came from with a call to get...() */
	public function getFinalClassName() : string
	{
		if (isset($this->cache['final_class'])) {
			/** @phpstan-ignore-next-line final_class is always initialized as a class-string */
			return $this->cache['final_class'];
		}
		/** @var class-string|object|string $final_class Defined as long as final_class is not */
		$final_class = $this->cache['final_class_raw'];
		unset($this->cache['final_class_raw']);
		if (is_object($final_class)) {
			$final_class = get_class($final_class);
		}
		else {
			$position = strpos($final_class, '::');
			if ($position !== false) {
				$final_class = substr($final_class, 0, $position);
			}
		}
		/** @var class-string $final_class All cases lead to this */
		$this->cache['final_class'] = $final_class;
		return $final_class;
	}

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?static
	{
		if (isset($this->cache['parent'])) {
			/** @phpstan-ignore-next-line  */
			return $this->cache['parent'];
		}
		$parent = null;
		if (!$this->isPrivate()) {
			$parent_class = $this->getDeclaringClass()->getParentClass();
			if ($parent_class !== false) {
				try {
					$parent_method = $parent_class->getMethod($this->name);
					if (!$parent_method->isPrivate()) {
						$parent = new static($parent_method->class, $parent_method->name);
					}
				}
				catch (ReflectionException) {
				}
			}
		}
		$this->cache['parent'] = $parent;
		return $parent;
	}

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by "\n".
	 */
	public function getPrototypeString() : string
	{
		$parameters = $this->getParameters();
		return ($this->isAbstract() ? 'abstract ' : '')
			. ($this->isPublic() ? 'public ' : ($this->isProtected() ? 'protected ' : 'private '))
			. ($this->isStatic() ? 'static ' : '')
			. 'function ' . $this->name
			. ($this->returnsReference() ? '& ' : '')
			. '(' . join(', ', $parameters) . ")\n" . '{';
	}

	//--------------------------------------------------------------------------------- getReturnType
	/** @phpstan-ignore-next-line getType returns a proxy which is compatible with ReflectionType */
	#[ReturnTypeWillChange]
	public function getReturnType() : Reflection_Type
	{
		return Type::of(parent::getReturnType(), $this);
	}

	//---------------------------------------------------------------------------------- hasParameter
	/** Returns true if the method has a parameter named $parameter_name */
	public function hasParameter(string $parameter_name) : bool
	{
		foreach (parent::getParameters() as $parameter) {
			if ($parameter->name === $parameter_name) {
				return true;
			}
		}
		return false;
	}

}
