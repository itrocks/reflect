<?php
namespace ITRocks\Reflect;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ReturnTypeWillChange;

/**
 * @extends ReflectionClass<T>
 * @implements Interfaces\Reflection_Class<T>
 * @template T of object
 */
class Reflection_Class extends ReflectionClass implements Interfaces\Reflection_Class
{
	use Instantiates;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection Inspector bug
	 * @param class-string<T>|T $object_or_class
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class)
	{
		parent::__construct($object_or_class);
	}

	//-------------------------------------------------------------------------------- getConstructor
	public function getConstructor() : ?Reflection_Method
	{
		$constructor = parent::getConstructor();
		if (isset($constructor)) {
			/** @noinspection PhpUnhandledExceptionInspection $constructor is valid */
			$constructor = new Reflection_Method($this->name, $constructor->name);
		}
		return $constructor;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Accumulates documentations of parents and the class itself
	 *
	 * @param int $filter @default self::T_EXTEND|self::T_IMPLEMENT|self::T_USE
	 */
	public function getDocComment(int $filter = 0) : string|false
	{
		$doc_comment = parent::getDocComment();
		if ($filter === 0) {
			return $doc_comment;
		}
		/** @var array<class-string,true> $already */
		static $already    = [];
		static $call_stack = 0;
		$call_stack ++;
		if ((($filter & self::T_USE) > 0) && !$this->isInterface()) {
			foreach ($this->getTraits(self::T_USE) as $trait) {
				$doc_comment .= "\n" . self::DOC_COMMENT_AGGREGATE . $trait->name . "\n";
				$doc_comment .= $trait->getDocComment($filter);
			}
		}
		if ((($filter & self::T_IMPLEMENTS) > 0) && !$this->isTrait()) {
			foreach ($this->getInterfaces(self::T_IMPLEMENTS) as $interface) {
				if (isset($already[$interface->name])) {
					continue;
				}
				$already[$interface->name] = true;
				$doc_comment .= "\n" . self::DOC_COMMENT_AGGREGATE . $interface->name . "\n";
				$doc_comment .= $interface->getDocComment($filter);
			}
		}
		if ((($filter & self::T_EXTENDS) > 0) && !$this->isTrait()) {
			$parent_class = $this->getParentClass();
			if ($parent_class !== false) {
				$doc_comment .= "\n" . self::DOC_COMMENT_AGGREGATE . $parent_class->name . "\n";
				$doc_comment .= $parent_class->getDocComment($filter);
			}
		}
		$call_stack --;
		if ($call_stack === 0) {
			$already = [];
		}
		return $doc_comment;
	}

	//----------------------------------------------------------------------------- getInterfaceNames
	/** @return list<class-string> */
	public function getInterfaceNames(int $filter = T_EXTENDS) : array
	{
		$interfaces = parent::getInterfaceNames();
		if (($filter & T_EXTENDS) > 0) {
			return $interfaces;
		}
		$parent_class = $this->getParentClass();
		if ($parent_class === false) {
			return $interfaces;
		}
		foreach ($parent_class->getInterfaceNames() as $interface) {
			unset($interfaces[array_search($interface, $interfaces, true)]);
		}
		return array_values($interfaces);
	}

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array<class-string,static>
	 */
	public function getInterfaces(int $filter = T_EXTENDS) : array
	{
		$parent_interfaces = [];
		foreach (parent::getInterfaces() as $interface) {
			$parent_interfaces[$interface->name] = $interface;
		}
		if (($filter & T_EXTENDS) === 0) {
			$parent_class = $this->getParentClass();
			if ($parent_class !== false) {
				foreach ($parent_class->getInterfaces() as $interface) {
					unset($parent_interfaces[$interface->name]);
				}
			}
		}
		$interfaces = [];
		foreach ($parent_interfaces as $interface) {
			/** @noinspection PhpUnhandledExceptionInspection $interface is valid */
			$interfaces[$interface->name] = new static($interface->name);
		}
		return $interfaces;
	}

	//-------------------------------------------------------------------------------- getParentClass
	public function getParentClass() : static|false
	{
		$parent_class = parent::getParentClass();
		if ($parent_class !== false) {
			/** @noinspection PhpUnhandledExceptionInspection $parent_class is valid */
			return new static($parent_class->name);
		}
		return false;
	}

	//---------------------------------------------------------------------------- getParentClassName
	/** @return class-string|string */
	public function getParentClassName() : string
	{
		$parent_class = parent::getParentClass();
		return ($parent_class === false) ? '' : $parent_class->name;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets an array of properties for the class
	 *
	 * Properties visible for current class, not the privates ones from parents and traits are
	 * retrieved but if you set T_EXTENDS and T_USE to get them.
	 * If you set self::T_SORT properties will be sorted by (@)display_order class annotation
	 *
	 * @noinspection PhpDocMissingThrowsInspection $property from parent::getProperties()
	 * @param class-string|null $final_class If set, forces the final class to this name
	 *                                       (mostly for internal use)
	 * @return array<string,Reflection_Property> key is the name of the property
	 */
	public function getProperties(int $filter = null, string $final_class = null) : array
	{
		$any_inherit    = self::T_EXTENDS | self::T_USE;
		$any_visibility = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED
			| ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_READONLY
			| ReflectionProperty::IS_STATIC;
		if (isset($filter) && (($filter & $any_visibility) === 0)) {
			$filter |= $any_visibility;
		}
		$reflection_properties = [];
		foreach (parent::getProperties($filter) as $property) {
			$reflection_properties[$property->name] = $property;
		}
		if ($reflection_properties === []) {
			return $reflection_properties;
		}
		if (is_null($final_class)) {
			$final_class = $this->name;
		}
		if (
			is_null($filter)
			|| (($filter & $any_inherit) === $any_inherit)
			|| (($filter & $any_inherit) === 0)
		) {
			$properties = [];
			foreach ($reflection_properties as $property_name => $reflection_property) {
				/** @noinspection PhpUnhandledExceptionInspection $property from parent::getProperties() */
				$property = new Reflection_Property($this->name, $property_name);
				$property->final_class      = $final_class;
				$properties[$property_name] = $property;
			}
			return $properties;
		}
		if (($filter & self::T_EXTENDS) === 0) {
			$parent_class = $this->getParentClass();
			if ($parent_class !== false) {
				$parent_properties = $parent_class->getProperties(
					$filter & ~ReflectionProperty::IS_PRIVATE
				);
				foreach ($parent_properties as $property_name => $reflection_property) {
					unset($reflection_properties[$property_name]);
				}
			}
		}
		if (($filter & self::T_USE) === 0) {
			foreach ($reflection_properties as $property_name => $reflection_property) {
				if ($reflection_property->getDeclaringClass()->isTrait()) {
					unset($reflection_properties[$property_name]);
				}
			}
		}
		$properties = [];
		foreach ($reflection_properties as $property_name => $reflection_property) {
			/** @noinspection PhpUnhandledExceptionInspection $property from parent::getProperties() */
			$property = new Reflection_Property($this->name, $property_name);
			$property->final_class = $final_class;
			$properties[$property_name] = $property;
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Retrieves reflected properties
	 *
	 * Only a property visible for current class can be retrieved, not the privates ones from parent
	 * classes or traits.
	 *
	 * @param string $name The 'name' of the property to get, or a 'property.path'
	 * @return Reflection_Property
	 * @throws ReflectionException
	 */
	#[ReturnTypeWillChange]
	public function getProperty(string $name) : Reflection_Property
	{
		return new Reflection_Property($this->name, $name);
	}

	//--------------------------------------------------------------------------------- getTraitNames
	/** @return list<class-string> */
	public function getTraitNames(int $filter = 0) : array
	{
		$traits = parent::getTraitNames();
		if (($filter & self::T_EXTENDS) > 0) {
			$parent_class = $this->getParentClass();
			if ($parent_class !== false) {
				$traits = array_merge($traits, $parent_class->getTraitNames($filter));
			}
		}
		return $traits;
	}

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * @noinspection PhpDocMissingThrowsInspection from parent::getTraits()
	 * @return array<class-string,static>
	 */
	public function getTraits(int $filter = 0) : array
	{
		$traits = [];
		foreach (parent::getTraits() as $trait) {
			/** @noinspection PhpUnhandledExceptionInspection from parent::getTraits() */
			$traits[$trait->name] = new static($trait->name);
		}
		if (($filter & self::T_EXTENDS) > 0) {
			$parent_class = $this->getParentClass();
			if ($parent_class !== false) {
				$traits = array_merge($traits, $parent_class->getTraits($filter));
			}
		}
		return $traits;
	}

	//------------------------------------------------------------------------------------------- isA
	/**
	 * Returns true if the class has $name into its parents, interfaces or traits
	 *
	 * @param class-string $name
	 * @param ?int $filter self::T_EXTENDS|self::T_IMPLEMENTS|self::T_USE
	 */
	public function isA(string $name, ?int $filter = null) : bool
	{
		if ($this->name === $name) {
			return true;
		}
		if ($filter === 0) {
			return false;
		}
		if (is_null($filter)) {
			$filter = self::T_EXTENDS | self::T_IMPLEMENTS | self::T_USE;
		}
		if ((($filter & self::T_EXTENDS) > 0) && class_exists($name)) {
			return is_a($this->name, $name, true);
		}
		if ((($filter & self::T_IMPLEMENTS) > 0) && interface_exists($name)) {
			if (in_array($name, $this->getInterfaceNames($filter), true)) {
				return true;
			}
		}
		if ((($filter & self::T_USE) > 0) && trait_exists($name)) {
			if (in_array($name, $this->getTraitNames($filter), true)) {
				return true;
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * Default behaviour (no argument) is the same as PHP ReflectionClass:
	 * - Abstract classes are abstract (this is the main use)
	 * - Interfaces and Traits are not abstract
	 *
	 * If $interface_trait_is_abstract is true:
	 * - Abstract classes, Interfaces and Traits are always abstract
	 * - Only non-abstract classes are not abstract
	 */
	public function isAbstract(bool $interface_trait_is_abstract = false) : bool
	{
		return parent::isAbstract()
			|| ($interface_trait_is_abstract && ($this->isInterface() || $this->isTrait()));
	}

	//--------------------------------------------------------------------------------------- isClass
	public function isClass() : bool
	{
		return !$this->isInterface() && !$this->isTrait();
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param class-string|object $object_or_class
	 * @throws ReflectionException
	 */
	public static function of(object|string $object_or_class) : static
	{
		return new static($object_or_class);
	}

}
