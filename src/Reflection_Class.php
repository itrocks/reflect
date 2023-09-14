<?php
namespace ITRocks\Reflect;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
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

	//---------------------------------------------------------------------------------------- $cache
	/** @var array{'doc_comment'?:array<int<1,max>,string|false>,'interface_names'?:array<int<0,max>,list<class-string>>,'namespace_use'?:array<string,string>,'tokens'?:list<array{int,string,int}|string>} */
	protected array $cache = [];

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
	 * @param int<0,max> $filter @default self::T_EXTENDS|self::T_IMPLEMENTS|self::T_USE
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
		$already[] = $this->name;
		if ((($filter & self::T_USE) > 0) && !$this->isInterface()) {
			foreach ($this->getTraits(self::T_USE) as $trait) {
				$append = $trait->getDocComment($filter, $cache, $locate);
				if ($append !== false) {
					$doc_comment = ($doc_comment === false) ? $append : ($doc_comment . "\n" . $append);
				}
			}
		}
		if ((($filter & self::T_IMPLEMENTS) > 0) && !$this->isTrait()) {
			foreach ($this->getInterfaces(self::T_IMPLEMENTS) as $interface) {
				if (in_array($interface->name, $already, true)) {
					continue;
				}
				$append = $interface->getDocComment($filter, $cache, $locate);
				if ($append !== false) {
					$doc_comment = ($doc_comment === false) ? $append : ($doc_comment . "\n" . $append);
				}
			}
		}
		if ((($filter & self::T_EXTENDS) > 0) && !$this->isInterface() && !$this->isTrait()) {
			$parent = $this->getParentClass();
			if (($parent !== false) && !in_array($parent->name, $already, true)) {
				$append = $parent->getDocComment($filter, $cache, $locate);
				if ($append !== false) {
					$doc_comment = ($doc_comment === false) ? $append : ($doc_comment . "\n" . $append);
				}
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

	//----------------------------------------------------------------------------- getImplementNames
	/**
	 * Parse extends / implements clause content for a class or interface.
	 * Not to be called for a class/interface/trait with no extends nor implements clause!
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return list<class-string>
	 */
	protected function getImplementNames() : array
	{
		$implements    = [];
		$namespace     = $this->getNamespaceName();
		$namespace_use = $this->getNamespaceUse();
		$tokens        = $this->getTokens();
		$token         = current($tokens);
		$token_id      = $this->isInterface() ? T_EXTENDS : T_IMPLEMENTS;
		while ($token !== false) {
			if ($token[0] === $token_id) {
				break;
			}
			$token = next($tokens);
		}
		while (!in_array($token, ['{', false], true)) {
			if (in_array(
				$token[0], [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_NAME_RELATIVE, T_STRING], true
			)) {
				/** @noinspection PhpUnhandledExceptionInspection Valid $token */
				$implements[] = Parse::referenceClassName($token, $namespace_use, $namespace);
			}
			$token = next($tokens);
		}
		return $implements;
	}

	//----------------------------------------------------------------------------- getInterfaceNames
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return list<class-string>
	 */
	public function getInterfaceNames(int $filter = self::T_EXTENDS | self::T_IMPLEMENTS)
		: array
	{
		if (isset($this->cache['interface_names'][$filter])) {
			return $this->cache['interface_names'][$filter];
		}
		if (parent::getInterfaceNames() === []) {
			// could optimize to run it for T_EXTENDS | T_IMPLEMENTS too, but would be disordered
			/** @phpstan-ignore-next-line Don't understand: list<class-string> accepts [] */
			$this->cache['interface_names'][$filter] = [];
			return [];
		}
		$interface_names = $this->getImplementNames();
		if (($filter & self::T_IMPLEMENTS) > 0) {
			foreach ($interface_names as $interface_name) {
				/** @noinspection PhpUnhandledExceptionInspection Valid getImplementNames result */
				$interface       = new static($interface_name);
				$interface_names = array_merge(
					$interface_names, array_diff($interface->getInterfaceNames($filter), $interface_names)
				);
			}
		}
		if (($filter & self::T_EXTENDS) > 0) {
			$parent = $this->getParentClass();
			if ($parent !== false) {
				$interface_names = array_merge(
					$interface_names, array_diff($parent->getInterfaceNames($filter), $interface_names)
				);
			}
		}
		/** @phpstan-ignore-next-line Don't understand: list<class-string> accepts [] */
		$this->cache['interface_names'][$filter] = $interface_names;
		return $interface_names;
	}

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array<class-string,static>
	 */
	public function getInterfaces(int $filter = self::T_EXTENDS | self::T_IMPLEMENTS)
		: array
	{
		$interfaces = [];
		foreach ($this->getInterfaceNames($filter) as $interface_name) {
			/** @noinspection PhpUnhandledExceptionInspection valid getInterfaceNames result */
			$interfaces[$interface_name] = new static($interface_name);
		}
		return $interfaces;
	}

	//------------------------------------------------------------------------------------- getMethod
	/** @throws ReflectionException */
	public function getMethod(string $name) : Reflection_Method
	{
		return new Reflection_Method($this->name, $name);
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Methods visible for current class, not the privates ones from parents and traits are
	 * retrieved but if you set T_EXTENDS and T_USE to get them.
	 *
	 * @noinspection PhpDocMissingThrowsInspection $property from parent::getMethods()
	 * @return array<string,Reflection_Method> key is the name of the method
	 */
	public function getMethods(int $filter = null) : array
	{
		$any_inherit    = self::T_EXTENDS | self::T_IMPLEMENTS | self::T_USE;
		$any_visibility = ReflectionMethod::IS_ABSTRACT | ReflectionMethod::IS_FINAL
			| ReflectionMethod::IS_PRIVATE | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PUBLIC
			| ReflectionMethod::IS_STATIC;
		if (isset($filter) && (($filter & $any_visibility) === 0)) {
			$filter |= $any_visibility;
		}
		$methods = [];
		foreach (parent::getMethods($filter) as $native_method) {
			/** @noinspection PhpUnhandledExceptionInspection $method from parent::getMethods() */
			$methods[$native_method->name] = new Reflection_Method($this->name, $native_method->name);
		}
		if ($methods === []) {
			return $methods;
		}
		if (isset($filter) && (($filter & $any_inherit) < $any_inherit)) {
			if (($filter & self::T_EXTENDS) === 0) {
				$interfaces   = $this->getInterfaceNames(T_IMPLEMENTS);
				$parent_class = $this->getParentClass();
				if ($parent_class !== false) {
					$parent_method_names = array_keys($parent_class->getMethods(
						$any_inherit | ($any_visibility ^ ReflectionMethod::IS_PRIVATE)
					));
					foreach ($parent_method_names as $parent_method_name) {
						$parent_method = $methods[$parent_method_name];
						if (!in_array($parent_method->getDeclaringClassName(), $interfaces, true)) {
							unset($methods[$parent_method_name]);
						}
					}
				}
			}
			if (($filter & self::T_IMPLEMENTS) === 0) {
				foreach ($methods as $method_name => $method) {
					if ($method->getDeclaringClass()->isInterface()) {
						unset($methods[$method_name]);
					}
				}
			}
			if (($filter & self::T_USE) === 0) {
				foreach ($methods as $method_name => $method) {
					if ($method->getDeclaringTrait()->isTrait()) {
						unset($methods[$method_name]);
					}
				}
			}
		}
		return $methods;
	}

	//------------------------------------------------------------------------------- getNamespaceUse
	/** @return array<string,string> array<string $alias, string $use>*/
	public function getNamespaceUse() : array
	{
		if (key_exists('namespace_use', $this->cache)) {
			return $this->cache['namespace_use'];
		}
		$namespace     = $this->getNamespaceName();
		$namespace_use = [];
		$tokens        = $this->getTokens();
		$token         = reset($tokens);
		while ($token !== false) {
			if (($token[0] === T_NAMESPACE) && (Parse::namespaceName($tokens) === $namespace)) {
				break;
			}
			$token = next($tokens);
		}
		while ($token !== false) {
			if ($token[0] === T_USE) {
				$namespace_use += Parse::namespaceUse($tokens);
			}
			if (in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, '}'], true)) {
				break;
			}
			$token = next($tokens);
		}
		$this->cache['namespace_use'] = $namespace_use;
		return $namespace_use;
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

	//------------------------------------------------------------------------------------- getTokens
	/** @return list<array{int,string,int}|string> */
	public function & getTokens() : array
	{
		if (key_exists('tokens', $this->cache)) {
			return $this->cache['tokens'];
		}
		$filename = $this->getFileName();
		if ($filename === false) {
			$this->cache['tokens'] = [];
			return $this->cache['tokens'];
		}
		$sourcecode = file_get_contents($filename);
		if ($sourcecode === false) {
			$this->cache['tokens'] = [];
			return $this->cache['tokens'];
		}
		$this->cache['tokens'] = token_get_all($sourcecode);
		return $this->cache['tokens'];
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
