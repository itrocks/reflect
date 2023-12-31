<?php
namespace ITRocks\Reflect\Attribute;

use Attribute;
use ITRocks\Reflect\Interface\Reflection_Class_Component;
use ITRocks\Reflect\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

trait Reflection_Has
{

	//---------------------------------------------------------------------------------- getAttribute
	/**
	 * @param class-string<A> $name
	 * @return ?Reflection_Attribute<$this,A>
	 * @template A of object
	 */
	public function getAttribute(string $name) : ?Reflection_Attribute
	{
		$attributes = $this->getAttributes($name, self::T_ALL);
		return ($attributes === [])
			? null
			: $attributes[0];
	}

	//------------------------------------------------------------------------- getAttributeInstances
	/**
	 * @param class-string<A>|null $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @return list<A>
	 * @template A of object
	 * @throws ReflectionException
	 */
	public function getAttributeInstances(string $name = null, int $flags = self::T_LOCAL) : array
	{
		$instances = [];
		foreach ($this->getAttributes($name, $flags) as $attribute) {
			$instances[] = $attribute->newInstance();
		}
		/** @var list<A> $instances phpstan has problems outside of Reflection_Class */
		return $instances;
	}

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * @param class-string<A>|null $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @phpstan-ignore-next-line not contravariant, but more precise rules
	 * @return list<Reflection_Attribute<$this,($name is null ? object : A)>>
	 * @template A of object
	 */
	public function getAttributes(string $name = null, int $flags = self::T_LOCAL) : array
	{
		/** @var array<string,array<string,array<int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*>,list<Reflection_Attribute<$this,A>>>>> $cache */
		static $cache = [];
		$cache_key    = $this->path();
		if (isset($cache[$cache_key][(string)$name][$flags])) {
			return $cache[$cache_key][(string)$name][$flags];
		}
		$attributes = [];
		/** @noinspection PhpMultipleClassDeclarationsInspection Identical getAttributes prototypes */
		$parents = parent::getAttributes($name, $flags & ReflectionAttribute::IS_INSTANCEOF);
		foreach ($parents as $attribute) {
			$attributes[] = new Reflection_Attribute($attribute, $this);
		}
		if (($flags & self::T_ALL) > 0) {
			$is_inheritable = is_null($name) || $this->isAttributeInheritable($name);
			$is_repeatable  = is_null($name) || $this->isAttributeRepeatable($name);
			$known_repeated = isset($name) && isset($attributes[1]);
			if (
				$is_inheritable
				&& (
					$is_repeatable
					|| ($attributes === [])
					|| (($flags & Reflection_Class_Component::T_OVERRIDE) > 0) /** @phpstan-ignore-line */
				)
			) {
				$this->moreAttributes($attributes, $name, $flags, $is_repeatable);
				/** @var list<Reflection_Attribute<$this,object>> $attributes */
				if (!$known_repeated && isset($attributes[1])) {
					$repeated_by_name = [];
					foreach ($attributes as $attribute) {
						$attribute_name = $attribute->getName();
						$repeated_by_name[$attribute_name] = ($repeated_by_name[$attribute_name] ?? 0) + 1;
					}
					$is_repeated = new ReflectionProperty(Reflection_Attribute::class, 'is_repeated');
					foreach ($attributes as $attribute) {
						if ($repeated_by_name[$attribute->getName()] > 1) {
							$is_repeated->setValue($attribute, true);
						}
					}
				}
			}
		}
		if (isset($name) && ($attributes === []) && class_exists($name)) {
			$has_default = Reflection_Attribute::getDefault($name);
			if (isset($has_default)) {
				$arguments = new ReflectionProperty(Reflection_Attribute::class, 'arguments');
				$attribute = new Reflection_Attribute($name, $this);
				$arguments->setValue($attribute, $has_default->getArguments());
				$attributes[] = $attribute;
			}
		}
		$cache[$cache_key][(string)$name][$flags] = $attributes;
		return $attributes;
	}

	//------------------------------------------------------------------------ isAttributeInheritable
	public function isAttributeInheritable(string $name) : bool
	{
		return !class_exists($name)
			|| ((new ReflectionClass($name))->getAttributes(Inheritable::class) !== []);
	}

	//------------------------------------------------------------------------- isAttributeRepeatable
	public function isAttributeRepeatable(string $name) : bool
	{
		return !class_exists($name)
			|| (($attributes = (new ReflectionClass($name))->getAttributes(Attribute::class)) === [])
			|| (
				!is_null($flags = $attributes[0]->getArguments()[0] ?? null)
				&& (($flags & Attribute::IS_REPEATABLE) > 0)
			);
	}

	//-------------------------------------------------------------------------------- moreAttributes
	/**
	 * @param list<Reflection_Attribute<$this,I>>&                        $attributes
	 * @param ?class-string<I>                                            $name
	 * @param int-mask-of<ReflectionAttribute::IS_INSTANCEOF|static::T_*> $flags
	 * @template I of object
	 */
	protected abstract function moreAttributes(
		array &$attributes, ?string $name, int $flags, bool $is_repeatable
	) : void;

}
