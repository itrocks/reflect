<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Undefined;
use ReflectionException;
use ReflectionMethod;
use ReturnTypeWillChange;

/**
 * @implements Interface\Reflection_Method<Class>
 * @property class-string<Class> $class
 * @template Class of object
 */
class Reflection_Method extends ReflectionMethod implements Interface\Reflection_Method
{
	use Attribute\Reflection_Method_Has;
	use Instantiate;

	//---------------------------------------------------------------------------------------- $cache
	/** @var array{'declaring_trait'?:Reflection_Class<object>,'doc_comment'?:array<int<1,max>,string|false>,'final_class'?:class-string<Class>,'final_class_raw':class-string|object|string,'parent'?:static|null} */
	private array $cache;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class_or_method Argument type does not match the declared
	 * @param class-string<Class>|Class|string $object_or_class_or_method
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
	/** @param class-string<Class> $final_class */
	public function forceFinalClass(string $final_class) : void
	{
		unset($this->cache['final_class_raw']);
		$this->cache['final_class'] = $final_class;
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class<object>
	 */
	public function getDeclaringClass(bool $trait = false) : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->class is always valid */
		return ($trait && !parent::getDeclaringClass()->isInterface())
			? $this->getDeclaringTrait(new Reflection_Class($this->class), $this->name)
			: new Reflection_Class($this->class);
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return class-string declaring class name
	 */
	public function getDeclaringClassName(bool $trait = false) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->class is always valid */
		return ($trait && !parent::getDeclaringClass()->isInterface())
			? $this->getDeclaringTrait(new Reflection_Class($this->class), $this->name)->name
			: $this->class;
	}

	//----------------------------------------------------------------------------- getDeclaringTrait
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param Reflection_Class<object> $class
	 * @return Reflection_Class<object>
	 */
	private function getDeclaringTrait(Reflection_Class $class, string $name) : Reflection_Class
	{
		if (is_null($alias = $class->getTraitAliases()[$name] ?? null)) {
			$traits = $class->getTraits();
		}
		else {
			/** @var class-string<object> $trait_name */
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
				return $this->getDeclaringTrait($method->getDeclaringClass(), $method->name);
			}
		}
		return $class;
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
			$doc_comment = '/** FROM ' . $this->getDeclaringClassName(true) . " */\n" . $doc_comment;
		}
		if ($filter === self::T_LOCAL) {
			return $doc_comment;
		}
		static $depth = 0;
		if ($cache && ($depth === 0)) {
			/** @var int<1,max> $cache_index */
			$cache_index = $filter | (int)$locate;
			if (isset($this->cache['doc_comment'][$cache_index])) {
				return $this->cache['doc_comment'][$cache_index];
			}
		}
		$depth ++;
		/** @var list<class-string> $already */
		static $already = [];
		$already[] = $this->getDeclaringClassName(true);
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
			&& ((($filter & self::T_USE) > 0) || !$parent->getDeclaringClass(true)->isTrait())
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
	 * @return Reflection_Class<Class> The one where the method came from with a call to get...()
	 */
	public function getFinalClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->final_class is valid */
		return static::newReflectionClass($this->getFinalClassName());
	}

	//----------------------------------------------------------------------------- getFinalClassName
	/** @return class-string<Class> The one where the method came from with a call to get...() */
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
		/** @var class-string<Class> $final_class */
		$this->cache['final_class'] = $final_class;
		return $final_class;
	}

	//--------------------------------------------------------------------------------- getParameters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array<string,Reflection_Parameter<Class>>
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
				return $this->hasPrototype()
					? $this->getPrototype()
					: null;
			}
			$method = $parent_class->getMethod($method->name);
			if ($method->isPrivate()) {
				/** @noinspection PhpUnhandledExceptionInspection hasPrototype */
				return $this->hasPrototype()
					? $this->getPrototype()
					: null;
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
			. (($return_type instanceof Undefined) ? '' : (' : ' . $return_type));
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

	//------------------------------------------------------------------------------------------ path
	public function path() : string
	{
		return $this->getFinalClassName() . '::' . $this->name;
	}

}
