<?php
namespace ITRocks\Reflect;

use ITRocks\Reflect\Type\Reflection_Type;
use ReflectionException;
use ReflectionProperty;
use ReturnTypeWillChange;

/**
 * @property class-string $class
 */
class Reflection_Property extends ReflectionProperty implements Interfaces\Reflection_Property
{
	use Instantiates;

	//---------------------------------------------------------------------------------------- $cache
	/** @var array{'declaring_trait'?:Reflection_Class<object>,'doc_comment'?:array<int<1,max>,string|false>,'parent'?:static|null} */
	private array $cache = [];

	//---------------------------------------------------------------------------------- $final_class
	/** @var class-string */
	public string $final_class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param class-string|object $class_name
	 * @throws ReflectionException
	 */
	public function __construct(object|string $class_name, string $property_name)
	{
		parent::__construct($class_name, $property_name);
		$this->final_class = is_object($class_name) ? get_class($class_name) : $class_name;
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected property
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
	/** @return class-string the declaring class name for the reflected property */
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
		$traits = $class->getTraits();
		foreach ($traits as $trait) {
			$property = $trait->getProperties(self::T_USE)[$this->name] ?? null;
			if (
				isset($property)
				&& ($property->getDocComment() === $this->getDocComment())
				&& (join(',', $property->getAttributes()) === join(',', $this->getAttributes()))
			) {
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
	 * @param int<0,max> $filter self::T_EXTENDS|self::T_INHERIT|self::T_USE (all work the same way)
	 * @param bool       $cache true if save/use cache
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
		static $already = [];
		$depth ++;
		/** @var list<class-string> $already */
		$already[] = $this->getDeclaringTraitName();
		if (($filter & self::T_USE) > 0) {
			foreach ($this->getFinalClass()->getTraits() as $trait) {
				if (!$trait->hasProperty($this->name)) {
					continue;
				}
				$property = $trait->getProperty($this->name);
				if (in_array($property->getDeclaringTraitName(), $already, true)) {
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
	 * @return Reflection_Class<object>
	 */
	public function getFinalClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->final_class is valid */
		return new Reflection_Class($this->final_class);
	}

	//----------------------------------------------------------------------------- getFinalClassName
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
	public function is(Interfaces\Reflection_Property $property) : bool
	{
		return ($property->getName() === $this->getName())
			&& ($property->getDeclaringTraitName() === $this->getDeclaringTraitName());
	}

}
