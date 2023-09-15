<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Type\Reflection_Type;
use ITRocks\Reflect\Type\Reflection_Undefined_Type;
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
		$declaring_trait = $this->getDeclaringClass();
		if (!$declaring_trait->isInterface()) {
			$declaring_trait = $this->getDeclaringTraitInternal($declaring_trait, $this->name);
		}
		$this->cache['declaring_trait'] = $declaring_trait;
		return $declaring_trait;
	}

	//--------------------------------------------------------------------- getDeclaringTraitInternal
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param Reflection_Class<object> $class
	 * @return Reflection_Class<object>
	 */
	private function getDeclaringTraitInternal(Reflection_Class $class, string $name)
		: Reflection_Class
	{
		if (is_null($alias = $class->getTraitAliases()[$name] ?? null)) {
			$traits = $class->getTraits();
		}
		else {
			/** @var class-string $trait_name */
			[$trait_name, $name] = explode('::', $alias);
			/** @noinspection PhpUnhandledExceptionInspection getTraits */
			$traits = [static::newReflectionClass($trait_name)];
		}
		foreach ($traits as $trait) {
			$method = $trait->getMethods(self::T_USE)[$name] ?? null;
			if (
				isset($method)
				&& ($method->getEndLine()   === $this->getEndLine())
				&& ($method->getFileName()  === $this->getFileName())
				&& ($method->getStartLine() === $this->getStartLine())
			) {
				return $this->getDeclaringTraitInternal($method->getDeclaringClass(), $method->name);
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
	public function getDocComment(
		int $filter = self::T_LOCAL, bool $cache = true, bool $locate = false
	) : string|false
	{
		$doc_comment = parent::getDocComment();
		if (($doc_comment !== false) && $locate) {
			$doc_comment = '/** FROM ' . $this->getDeclaringTraitName() . " */\n" . $doc_comment;
		}
		if ($filter === self::T_LOCAL) {
			return $doc_comment;
		}
		static $depth = 0;
		if ($cache && ($depth === 0)) {
			/** @var int<1,max> $cache_index */
			$cache_index = $filter | intval($locate);
			if (isset($this->cache['doc_comment'][$cache_index])) {
				return $this->cache['doc_comment'][$cache_index];
			}
		}
		$depth ++;
		/** @var list<class-string> $already */
		static $already = [];
		$already[] = $this->getDeclaringTraitName();
		if (($filter & self::T_IMPLEMENTS) > 0) {
			foreach ($this->getFinalClass()->getInterfaces(self::T_LOCAL) as $interface) {
				if (in_array($interface->name, $already, true) || !$interface->hasMethod($this->name)) {
					continue;
				}
				$append = $interface->getMethod($this->name)->getDocComment($filter, $cache, $locate);
				if ($append !== false) {
					$doc_comment = ($doc_comment === false) ? $append : ($doc_comment . "\n" . $append);
				}
			}
		}
		if (
			(($filter & self::T_EXTENDS) > 0)
			&& !is_null($parent = $this->getParent())
			&& !in_array($parent->class, $already, true)
			&& ((($filter & self::T_USE) > 0) || !$parent->getDeclaringTrait()->isTrait())
		) {
			$append = $parent->getDocComment($filter, $cache, $locate);
			if ($append !== false) {
				$doc_comment = ($doc_comment === false) ? $append : ($doc_comment . "\n" . $append);
			}
		}
		$depth --;
		if ($depth === 0) {
			$already = [];
			if (isset($cache_index)) {
				$this->cache['doc_comment'][$cache_index] = $doc_comment;
			}
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

	//--------------------------------------------------------------------------------- getParameters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array<string,Reflection_Parameter>
	 */
	public function getParameters() : array
	{
		$parameters = [];
		foreach (parent::getParameters() as $parameter) {
			/** @noinspection PhpUnhandledExceptionInspection getParameters */
			$parameters[$parameter->name] = static::newReflectionParameter(
				[$this->getFinalClassName(), $this->name], $parameter->name
			);
		}
		return $parameters;
	}

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?static
	{
		$method = $this;
		while ($method->class === $this->class) {
			$parent_class = $method->getFinalClass()->getParentClass();
			if (($parent_class === false) || !$parent_class->hasMethod($method->name)) {
				/** @noinspection PhpUnhandledExceptionInspection hasPrototype */
				return $this->hasPrototype() ? $this->getPrototype() : null;
			}
			$method = $parent_class->getMethod($method->name);
			if ($method->isPrivate()) {
				/** @noinspection PhpUnhandledExceptionInspection hasPrototype */
				return $this->hasPrototype() ? $this->getPrototype() : null;
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection from getMethod */
		return new static($method->class, $method->name);
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
		$parameters  = $this->getParameters();
		$return_type = $this->getReturnType();
		return ($this->isAbstract() ? 'abstract ' : '')
			. ($this->isPublic() ? 'public ' : ($this->isProtected() ? 'protected ' : 'private '))
			. ($this->isStatic() ? 'static ' : '')
			. 'function ' . $this->name
			. ($this->returnsReference() ? '& ' : '')
			. '(' . join(', ', $parameters) . ')'
			. (($return_type instanceof Reflection_Undefined_Type) ? '' : (' : ' . $return_type));
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
