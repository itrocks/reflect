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
	/** @var array{'declaring_trait'?:Reflection_Class<object>,'doc_comment'?:array<int<1,max>,string|false>,'final_class'?:class-string,'final_class_raw':class-string|object|string,'parent'?:static|null} */
	private array $cache;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param class-string|object|string $object_or_class_or_method
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class_or_method, string $method = null)
	{
		$this->cache['final_class_raw'] = $object_or_class_or_method;
		if (is_null($method)) {
			/** @phpstan-ignore-next-line Call with an object will result in a fatal error, as expected */
			parent::__construct($object_or_class_or_method);
		}
		else {
			parent::__construct($object_or_class_or_method, $method);
		}
	}

	//------------------------------------------------------------------------------- forceFinalClass
	/** @param class-string $final_class */
	public function forceFinalClass(string $final_class) : void
	{
		unset($this->cache['final_class_raw']);
		$this->cache['final_class'] = $final_class;
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
		$declaring_class = $this->getDeclaringClass();
		$methods         = $declaring_class->getMethods(0);
		if (key_exists($this->name, $methods)) {
			return $declaring_class;
		}
		$traits = $class->getTraits();
		foreach ($traits as $trait) {
			$methods = $trait->getMethods();
			if (isset($methods[$this->name])) {
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
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param int<0,max> $filter self::T_EXTENDS|self::T_IMPLEMENTS|self::T_USE
	 */
	public function getDocComment(int $filter = 0, bool $cache = true, bool $locate = false)
		: string|false
	{
		$doc_comment = parent::getDocComment();
		if (($doc_comment !== false) && $locate) {
			$doc_comment = '/** FROM ' . $this->name . " */\n" . $doc_comment;
		}
		if ($filter === 0) {
			return $doc_comment;
		}
		if ($cache && isset($this->cache['doc_comment'][$filter])) {
			return $this->cache['doc_comment'][$filter];
		}
		/** @var list<string> $already */
		static $already = [];
		static $depth   = 0;
		if ($depth === 0) {
			$already[] = $this->class;
		}
		if (($filter & self::T_IMPLEMENTS) > 0) {
			foreach ($this->getFinalClass()->getImplements() as $interface) {
				if (in_array($interface->name, $already, true) || !$interface->hasMethod($this->name)) {
					continue;
				}
				$prototype = $interface->getMethod($this->name);
				$already[] = $interface->name;
				$append    = $prototype->getDocComment($filter, $cache, $locate);
				if ($append !== false) {
					$doc_comment = ($doc_comment === false) ? $append : $doc_comment . "\n" . $append;
				}
			}
		}
		$has_prototype  = $this->hasPrototype();
		/** @noinspection PhpUnhandledExceptionInspection hasPrototype */
		$prototype       = $has_prototype ? $this->getPrototype() : null;
		$prototype_class = $prototype?->getDeclaringClass();
		if (
			(($filter & self::T_USE) > 0)
			&& isset($prototype_class)
			&& $prototype_class->isTrait()
			&& in_array($prototype->class, $this->getFinalClass()->getTraitNames(), true)
		) {
			$append = $prototype->getDocComment($filter, $cache, $locate);
			if ($append !== false) {
				$doc_comment = ($doc_comment === false) ? $append : $doc_comment . "\n" . $append;
			}
		}
		if ((($filter & self::T_EXTENDS) > 0) && !is_null($parent = $this->getParent())) {
			$depth ++;
			$append = $parent->getDocComment($filter, $cache, $locate);
			$depth --;
			if ($depth === 0) {
				$already = [];
			}
			if ($append !== false) {
				$doc_comment = ($doc_comment === false) ? $append : $doc_comment . "\n" . $append;
			}
		}
		if ($cache) {
			$this->cache['doc_comment'][$filter] = $doc_comment;
		}
		return $doc_comment;
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
	/** @return class-string The one where the method came from with a call to get...() */
	public function getFinalClassName() : string
	{
		if (key_exists('final_class', $this->cache)) {
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
		$parent_class = $this->getFinalClass()->getParentClass();
		if (($parent_class === false) || !$parent_class->hasMethod($this->name)) {
			return null;
		}
		$parent = $parent_class->getMethod($this->name);
		/** @noinspection PhpUnhandledExceptionInspection from getMethod */
		return new static($parent->class, $parent->name);
	}

	//---------------------------------------------------------------------------------- getPrototype
	/** @throws ReflectionException */
	public function getPrototype() : static
	{
		$parent = parent::getPrototype();
		return new static($parent->class, $parent->name);
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
