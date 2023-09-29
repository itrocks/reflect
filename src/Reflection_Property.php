<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Type\Reflection_Type;
use ReflectionException;
use ReflectionProperty;
use ReturnTypeWillChange;

/**
 * @implements Interface\Reflection_Property<Class>
 * @property class-string<Class> $class
 * @template Class of object
 */
class Reflection_Property extends ReflectionProperty implements Interface\Reflection_Property
{
	use Attribute\Reflection_Property_Has;
	use Instantiates;

	//---------------------------------------------------------------------------------------- $cache
	/** @var array{'declaring_trait'?:Reflection_Class<object>,'doc_comment'?:array<int<1,max>,string|false>,'parent'?:static|null} */
	private array $cache = [];

	//---------------------------------------------------------------------------------- $final_class
	/** @var class-string<Class> */
	public readonly string $final_class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<Class>|Class $object_or_class
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class, string $property)
	{
		parent::__construct($object_or_class, $property);
		$this->final_class = is_object($object_or_class)
			? get_class($object_or_class)
			: $object_or_class;
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class<object>
	 */
	public function getDeclaringClass(bool $trait = false) : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection valid $this->class */
		return $trait
			? $this->getDeclaringTrait(new Reflection_Class($this->class))
			: new Reflection_Class($this->class);
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	public function getDeclaringClassName(bool $trait = false) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection valid $this->class */
		return $trait
			? $this->getDeclaringTrait(new Reflection_Class($this->class))->name
			: $this->class;
	}

	//----------------------------------------------------------------------------- getDeclaringTrait
	/**
	 * @param Reflection_Class<object> $class
	 * @return Reflection_Class<object>
	 */
	private function getDeclaringTrait(Reflection_Class $class) : Reflection_Class
	{
		$traits = $class->getTraits();
		foreach ($traits as $trait) {
			$property = $trait->getProperties(self::T_USE)[$this->name] ?? null;
			if (
				isset($property)
				&& ($property->getDocComment() === $this->getDocComment())
				&& $this->hasSameAttributes($property)
			) {
				return $this->getDeclaringTrait($trait);
			}
		}
		return $class;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * @param int<0,max> $filter self::T_EXTENDS|self::T_INHERIT|self::T_USE (all work the same way)
	 * @param bool       $cache true if save/use cache
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
			$cache_index = $filter | intval($locate);
			if (isset($this->cache['doc_comment'][$cache_index])) {
				return $this->cache['doc_comment'][$cache_index];
			}
		}
		static $already = [];
		$depth ++;
		/** @var list<class-string> $already */
		$already[] = $this->getDeclaringClassName(true);
		if (($filter & self::T_USE) > 0) {
			foreach ($this->getFinalClass()->getTraits() as $trait) {
				if (!$trait->hasProperty($this->name)) {
					continue;
				}
				$property = $trait->getProperty($this->name);
				if (in_array($property->getDeclaringClassName(true), $already, true)) {
					continue;
				}
				$append = $trait->getProperty($this->name)->getDocComment($filter, $cache, $locate);
				if ($append !== false) {
					$doc_comment = ($doc_comment === false) ? $append : ($doc_comment . "\n" . $append);
				}
			}
		}
		if (
			(($filter & self::T_EXTENDS) > 0)
			&& (!is_null($parent = $this->getParent()))
			&& !in_array($parent->class, $already, true)
		) {
			$append = $parent->getDocComment($filter, $cache, $locate);
			if ($append !== false) {
				$doc_comment = ($doc_comment === false) ? $append : ($doc_comment . "\n" . $append);
			}
		}
		$depth --;
		if ($depth === 0) {
			$already = [];
		}
		if (isset($cache_index)) {
			$this->cache['doc_comment'][$cache_index] = $doc_comment;
		}
		return $doc_comment;
	}

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class<Class>
	 */
	public function getFinalClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->final_class is valid */
		return new Reflection_Class($this->final_class);
	}

	//----------------------------------------------------------------------------- getFinalClassName
	/** @return class-string<Class> */
	public function getFinalClassName() : string
	{
		return $this->final_class;
	}

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?static
	{
		if (key_exists('parent', $this->cache)) {
			return $this->cache['parent'];
		}
		$this->cache['parent'] = null;
		if ($this->isPrivate()) {
			return null;
		}
		$parent_class = $this->getDeclaringClass()->getParentClass();
		if ($parent_class === false) {
			return null;
		}
		try {
			$parent = $parent_class->getProperty($this->name);
			$parent = new static($parent->final_class, $parent->name);
		}
		catch (ReflectionException) {
			return null;
		}
		if ($parent->isPrivate()) {
			return null;
		}
		$this->cache['parent'] = $parent;
		/** @noinspection PhpUnhandledExceptionInspection caught getProperty */
		return new static($parent->class, $parent->name);
	}

	//--------------------------------------------------------------------------------------- getType
	/** @phpstan-ignore-next-line getType returns a proxy which is compatible with ReflectionType */
	#[ReturnTypeWillChange]
	public function getType() : Reflection_Type
	{
		return Type::of(parent::getType(), $this);
	}

	//-------------------------------------------------------------------------------------------- is
	public function is(Interface\Reflection_Property $property) : bool
	{
		return ($property instanceof Reflection_Property)
			&& ($property->getName() === $this->getName())
			&& ($property->getDeclaringClassName(true) === $this->getDeclaringClassName(true));
	}

}
