<?php
namespace ITRocks\Reflect;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @extends ReflectionClass<Class>
 * @implements Interface\Reflection_Class<Class>
 * @template Class of object
 */
class Reflection_Class extends ReflectionClass implements Interface\Reflection_Class
{
	use Attribute\Reflection_Class_Has;
	use Instantiate;

	//---------------------------------------------------------------------------------------- $cache
	/** @var array{'doc_comment'?:array<int<1,max>,string|false>,'interface_names'?:array<int<0,max>,list<class-string>>,'namespace_use'?:array<string,string>,'tokens'?:list<array{int,string,int}|string>} */
	protected array $cache = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<Class>|Class $object_or_class
	 * @throws ReflectionException
	 */
	public function __construct(object|string $object_or_class)
	{
		parent::__construct($object_or_class);
	}

	//---------------------------------------------------------------------------------- getClassList
	/**
	 * @param int-mask-of<self::T_*> $filter
	 * @return array<class-string,Reflection_Class<object>>
	 */
	public function getClassList(int $filter = self::T_INHERIT) : array
	{
		$class_list = [$this->name => $this];
		if (($filter & self::T_USE) > 0) {
			$class_list += $this->getTraits(self::T_USE);
		}
		if (($filter & self::T_IMPLEMENTS) > 0) {
			$class_list += $this->getInterfaces(self::T_IMPLEMENTS);
		}
		if (($filter & self::T_EXTENDS) > 0) {
			$parent = $this->getParentClass();
			if ($parent !== false) {
				$class_list += $parent->getClassList($filter);
			}
		}
		return $class_list;
	}

	//----------------------------------------------------------------------------- getClassListNames
	/**
	 * @param int-mask-of<self::T_*> $filter
	 * @return list<class-string>
	 */
	public function getClassListNames(int $filter = self::T_INHERIT) : array
	{
		$class_list = [$this->name];
		if (($filter & self::T_USE) > 0) {
			$class_list = array_merge(
				$class_list, array_diff($this->getTraitNames(self::T_USE), $class_list)
			);
		}
		if (($filter & self::T_IMPLEMENTS) > 0) {
			$class_list = array_merge(
				$class_list, array_diff($this->getInterfaceNames(self::T_IMPLEMENTS), $class_list)
			);
		}
		if (($filter & self::T_EXTENDS) > 0) {
			$parent = $this->getParentClass();
			if ($parent !== false) {
				$class_list = array_merge(
					$class_list, array_diff($parent->getClassListNames($filter), $class_list)
				);
			}
		}
		return $class_list;
	}

	//---------------------------------------------------------------------------------- getClassTree
	/**
	 * @param int-mask-of<self::T_*> $filter
	 * @return array<class-string,array<class-string,array<class-string,array<class-string,mixed>>>>
	 */
	public function getClassTree(int $filter = self::T_INHERIT) : array
	{
		$class_tree = [];
		if (($filter & self::T_USE) > 0) {
			foreach ($this->getTraits() as $trait) {
				$class_tree += $trait->getClassTree($filter);
			}
		}
		if (($filter & self::T_IMPLEMENTS) > 0) {
			foreach ($this->getInterfaces(self::T_LOCAL) as $interface) {
				$class_tree += $interface->getClassTree($filter);
			}
		}
		if (($filter & self::T_EXTENDS) > 0) {
			$parent = $this->getParentClass();
			if ($parent !== false) {
				$class_tree += $parent->getClassTree($filter);
			}
		}
		return [$this->name => $class_tree];
	}

	//-------------------------------------------------------------------------------- getConstructor
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return ?Reflection_Method<Class>
	 */
	public function getConstructor() : ?Reflection_Method
	{
		$constructor = parent::getConstructor();
		if (isset($constructor)) {
			/** @noinspection PhpUnhandledExceptionInspection $constructor is valid */
			$constructor = static::newReflectionMethod($this->name, $constructor->name);
		}
		return $constructor;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Accumulates documentations of parents and the class itself
	 *
	 * @param int<0,max> $filter @default self::T_EXTENDS|self::T_IMPLEMENTS|self::T_USE
	 */
	public function getDocComment(
		int $filter = self::T_LOCAL, bool $cache = true, bool $locate = false
	) : string|false
	{
		$doc_comment = parent::getDocComment();
		if (($doc_comment !== false) && $locate) {
			$doc_comment = '/** FROM ' . $this->name . " */\n" . $doc_comment;
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
		$already[] = $this->name;
		if ((($filter & self::T_USE) > 0) && !$this->isInterface()) {
			foreach ($this->getTraits() as $trait) {
				if (in_array($trait->name, $already, true)) {
					continue;
				}
				$append = $trait->getDocComment($filter, $cache, $locate);
				if ($append !== false) {
					$doc_comment = ($doc_comment === false) ? $append : ($doc_comment . "\n" . $append);
				}
			}
		}
		if ((($filter & self::T_IMPLEMENTS) > 0) && !$this->isTrait()) {
			foreach ($this->getInterfaces(self::T_LOCAL) as $interface) {
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

	//------------------------------------------------------------------------------------ getExtends
	/** @return list<class-string> */
	public function getExtends() : array
	{
		if ($this->isInterface()) {
			return $this->getImplements();
		}
		$extends = $this->getParentClassName();
		if ($extends === '') {
			return [];
		}
		/** @var class-string $extends */
		return [$extends];
	}

	//--------------------------------------------------------------------------------- getImplements
	/**
	 * Parse extends / implements clause content for a class or interface.
	 * Not to be called for a class/interface/trait with no extends nor implements clause!
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return list<class-string>
	 */
	public function getImplements() : array
	{
		$implements    = [];
		$namespace     = $this->getNamespaceName();
		$namespace_use = $this->getNamespaceUses();
		$start_line    = $this->getStartLine();
		$tokens        = $this->getTokens();
		$token         = reset($tokens);
		$token_id      = $this->isInterface()
			? T_EXTENDS
			: T_IMPLEMENTS;
		while (($token !== false) && (!is_array($token) || ($token[2] !== $start_line))) {
			$token = next($tokens);
		}
		while (($token !== false) && ($token[0] !== $token_id)) {
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
			$this->cache['interface_names'][$filter] = [];
			return [];
		}
		$interface_names = $this->getImplements();
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
		$this->cache['interface_names'][$filter] = $interface_names;
		return $interface_names;
	}

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array<class-string<object>,Reflection_Class<object>>
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
	/**
	 * @return Reflection_Method<Class>
	 * @throws ReflectionException
	 */
	public function getMethod(string $name) : Reflection_Method
	{
		return static::newReflectionMethod($this->name, $name);
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Methods visible for current class, not the privates ones from parents and traits are
	 * retrieved but if you set T_EXTENDS and T_USE to get them.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array<string,Reflection_Method<Class>> key is the name of the method
	 */
	public function getMethods(?int $filter = self::T_EXTENDS | self::T_IMPLEMENTS | self::T_USE)
		: array
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
			$methods[$native_method->name] = static::newReflectionMethod(
				$this->name, $native_method->name
			);
		}
		if ($methods === []) {
			return $methods;
		}
		if (isset($filter) && (($filter & $any_inherit) < $any_inherit)) {
			if (($filter & self::T_EXTENDS) === 0) {
				$interfaces   = $this->getInterfaceNames(self::T_IMPLEMENTS);
				$parent_class = $this->getParentClass();
				if ($parent_class !== false) {
					$parent_methods = $parent_class->getMethods(
						$any_inherit | ($any_visibility ^ ReflectionMethod::IS_PRIVATE)
					);
					foreach ($parent_methods as $method_name => $parent_method) {
						$method = $methods[$method_name];
						if (
							($method->getDeclaringClassName(true) === $parent_method->getDeclaringClassName(true))
							&& !in_array($parent_method->getDeclaringClassName(), $interfaces, true)
						) {
							unset($methods[$method_name]);
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
					if ($method->getDeclaringClass(true)->isTrait()) {
						unset($methods[$method_name]);
					}
				}
			}
		}
		return $methods;
	}

	//------------------------------------------------------------------------------ getNamespaceUses
	/** @return array<string,string> array<string $alias, string $use>*/
	public function getNamespaceUses() : array
	{
		if (key_exists('namespace_use', $this->cache)) {
			return $this->cache['namespace_use'];
		}
		$depth         = 0;
		$namespace     = $this->getNamespaceName();
		$in_namespace  = ($namespace === '');
		$namespace_use = [];
		$tokens        = $this->getTokens();
		$token         = reset($tokens);
		while ($token !== false) {
			if ($token[0] === T_NAMESPACE) {
				$in_namespace = (Parse::namespaceName($tokens) === $namespace);
				$token        = current($tokens);
				while (!in_array($token, [false, '{', ';'], true)) {
					$token = next($tokens);
				}
				$depth = 0;
			}
			elseif ($token === '{') {
				$depth ++;
			}
			elseif ($token === '}') {
				$depth --;
				if ($depth < 0) {
					$depth         = 0;
					$in_namespace  = ($namespace === '');
					$namespace_use = [];
				}
			}
			elseif ($in_namespace) {
				if (($token[0] === T_USE) && ($depth === 0)) {
					$namespace_use += Parse::namespaceUse($tokens);
				}
				elseif (
					in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT], true)
					&& (Parse::className($tokens, $namespace) === $this->name)
				) {
					break;
				}
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
		return ($parent_class === false)
			? ''
			: $parent_class->name;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array<string,Reflection_Property<Class>>
	 */
	public function getProperties(?int $filter = self::T_EXTENDS | self::T_USE) : array
	{
		$any_inherit    = self::T_EXTENDS | self::T_USE;
		$any_visibility = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED
			| ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_READONLY
			| ReflectionProperty::IS_STATIC;
		if (isset($filter) && (($filter & $any_visibility) === 0)) {
			$filter |= $any_visibility;
		}
		$properties = [];
		foreach (parent::getProperties($filter) as $native_property) {
			/** @noinspection PhpUnhandledExceptionInspection $property from parent::getProperties() */
			$properties[$native_property->name] = static::newReflectionProperty(
				$this->name, $native_property->name
			);
		}
		if ($properties === []) {
			return $properties;
		}
		if (isset($filter) && (($filter & $any_inherit) < $any_inherit)) {
			if (($filter & self::T_EXTENDS) === 0) {
				$parent_class = $this->getParentClass();
				if ($parent_class !== false) {
					$parent_properties = $parent_class->getProperties(
						$any_inherit | ($any_visibility ^ ReflectionProperty::IS_PRIVATE)
					);
					foreach ($parent_properties as $property_name => $parent_property) {
						$property = $properties[$property_name];
						$parent_declaring_trait   = $parent_property->getDeclaringClassName(true);
						$property_declaring_trait = $property->getDeclaringClassName(true);
						if ($parent_declaring_trait === $property_declaring_trait) {
							unset($properties[$property_name]);
						}
					}
				}
			}
			if (($filter & self::T_USE) === 0) {
				foreach ($properties as $property_name => $property) {
					if ($property->getDeclaringClass(true)->isTrait()) {
						unset($properties[$property_name]);
					}
				}
			}
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
	 * @return Reflection_Property<Class>
	 * @throws ReflectionException
	 */
	public function getProperty(string $name) : Reflection_Property
	{
		return static::newReflectionProperty($this->name, $name);
	}

	//------------------------------------------------------------------------- getReflectionConstant
	/** @return Reflection_Class_Constant<Class> */
	public function getReflectionConstant(string $name) : Reflection_Class_Constant
	{
		return static::newReflectionConstant($this->name, $name);
	}

	//------------------------------------------------------------------------ getReflectionConstants
	/** @return array<string,Reflection_Class_Constant<Class>> */
	public function getReflectionConstants(int $filter = null) : array
	{
		$constants = [];
		foreach (parent::getReflectionConstants($filter) as $constant) {
			$constants[$constant->name] = static::newReflectionConstant($this->name, $constant);
		}
		// TODO More filters
		return $constants;
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
		$this->cache['tokens'] = token_get_all((string)file_get_contents($filename));
		return $this->cache['tokens'];
	}

	//--------------------------------------------------------------------------------- getTraitNames
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return list<class-string>
	 */
	public function getTraitNames(int $filter = self::T_LOCAL) : array
	{
		$trait_names = parent::getTraitNames();
		if (($filter & self::T_USE) > 0) {
			foreach ($trait_names as $trait_name) {
				/** @noinspection PhpUnhandledExceptionInspection Native getTraitNames */
				$trait_names = array_merge(
					$trait_names, array_diff((new static($trait_name))->getTraitNames($filter), $trait_names)
				);
			}
		}
		if (($filter & self::T_EXTENDS) > 0) {
			$parent = $this->getParentClass();
			if ($parent !== false) {
				$trait_names = array_merge(
					$trait_names, array_diff($parent->getTraitNames($filter), $trait_names)
				);
			}
		}
		return $trait_names;
	}

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array<class-string,Reflection_Class<object>>
	 */
	public function getTraits(int $filter = self::T_LOCAL) : array
	{
		$traits = [];
		foreach (parent::getTraits() as $trait) {
			/** @noinspection PhpUnhandledExceptionInspection Native getTraits */
			$traits[$trait->name] = new static($trait->name);
		}
		if (($filter & self::T_USE) > 0) {
			foreach ($traits as $trait) {
				$traits = array_merge($traits, $trait->getTraits($filter));
			}
		}
		if (($filter & self::T_EXTENDS) > 0) {
			$parent = $this->getParentClass();
			if ($parent !== false) {
				$traits = array_merge($traits, $parent->getTraits($filter));
			}
		}
		return $traits;
	}

	//------------------------------------------------------------------------------------------- isA
	/**
	 * Returns true if the class has $name into its parents, interfaces or traits
	 *
	 * @param class-string           $name
	 * @param int-mask-of<self::T_*> $filter
	 */
	public function isA(string $name, int $filter = self::T_INHERIT) : bool
	{
		if ($this->name === $name) {
			return true;
		}
		if ($filter === self::T_LOCAL) {
			return false;
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
			return in_array($name, $this->getTraitNames($filter), true);
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
	public static function of(object|string $object_or_class) : static
	{
		return new static($object_or_class);
	}

	//------------------------------------------------------------------------------------------ path
	public function path() : string
	{
		return $this->name;
	}

}
