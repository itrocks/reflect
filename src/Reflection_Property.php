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
	/** @var array{'declaring_trait'?:Reflection_Class<object>,'doc_comment'?:string|false,'parent'?:static|null} */
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
	/**
	 * @param int<0,max> $filter self::T_EXTENDS|self::T_INHERIT|self::T_USE (all work the same way)
	 * @param bool       $cache true if save/use cache
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
		if ($cache && isset($this->cache['doc_comment'])) {
			return $this->cache['doc_comment'];
		}
		$parent_property = $this->getParent();
		if (isset($parent_property)) {
			$doc_comment .= $parent_property->getDocComment($filter, $cache, $locate);
		}
		if ($cache) {
			$this->cache['doc_comment'] = $doc_comment;
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
