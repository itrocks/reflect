<?php
namespace ITRocks\Reflect;

use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use ReturnTypeWillChange;

/**
 * @property class-string $class
 */
class Reflection_Property extends ReflectionProperty implements Interfaces\Reflection_Property
{
	use Instantiates;

	//---------------------------------------------------------------------------------------- $cache
	/** @var array{declaring_trait:Reflection_Class<object>,doc_comment:string,parent:static|null}|array<void> */
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
	/**
	 * @param int  $filter self::T_EXTENDS | self::T_INHERIT | self::T_USE (all work the same way)
	 * @param bool $cache true if save/use cache
	 */
	public function getDocComment(int $filter = 0, bool $cache = true) : string|false
	{
		if ($filter === 0) {
			return parent::getDocComment();
		}
		if ($cache && isset($this->cache['doc_comment'])) {
			/** @phpstan-ignore-next-line doc_comment is always initialized as a string */
			return $this->cache['doc_comment'];
		}
		$doc_comment = "/**\n" . self::DOC_COMMENT_AGGREGATE . $this->getDeclaringTrait()->name . "\n"
			. parent::getDocComment();
		$parent_property = $this->getParent();
		if (isset($parent_property)) {
			$doc_comment .= $parent_property->getDocComment();
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
		if (isset($this->cache['parent'])) {
			/** @phpstan-ignore-next-line  */
			return $this->cache['parent'];
		}
		$parent = null;
		if (!$this->isPrivate()) {
			$parent_class = $this->getDeclaringClass()->getParentClass();
			if ($parent_class !== false) {
				try {
					$parent_property = $parent_class->getProperty($this->name);
					if (!$parent_property->isPrivate()) {
						$parent = new static($parent_property->class, $parent_property->name);
					}
				}
				catch (ReflectionException) {
				}
			}
		}
		$this->cache['parent'] = $parent;
		return $parent;
	}

	//--------------------------------------------------------------------------------------- getType
	/** @phpstan-ignore-next-line getType returns a proxy which is compatible with ReflectionType */
	#[ReturnTypeWillChange]
	public function getType() : ?Type
	{
		/** @var ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null $type */
		$type = parent::getType();
		return isset($type) ? new Type($type, $this) : null;
	}

	//-------------------------------------------------------------------------------------------- is
	public function is(Interfaces\Reflection_Property $property) : bool
	{
		return ($property->getName() === $this->getName())
			&& ($property->getDeclaringTraitName() === $this->getDeclaringTraitName());
	}

}
