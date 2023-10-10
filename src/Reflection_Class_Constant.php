<?php
namespace ITRocks\Reflect;

use ReflectionClassConstant;

/**
 * @implements Interface\Reflection_Class_Constant<Class>
 * @property class-string<Class> $class
 * @template Class of object
 */
class Reflection_Class_Constant extends ReflectionClassConstant
	implements Interface\Reflection_Class_Constant
{
	use Attribute\Reflection_Class_Constant_Has;
	use Instantiate;

	//---------------------------------------------------------------------------------- $final_class
	/** @var class-string<Class> */
	public readonly string $final_class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $object_or_class Argument type does not match the declared
	 * @param class-string<Class>|Class $object_or_class
	 */
	public function __construct(object|string $object_or_class, string $constant)
	{
		parent::__construct($object_or_class, $constant);
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
			$contant = $trait->getReflectionConstants(self::T_USE)[$this->name] ?? null;
			if (
				isset($contant)
				&& ($contant->getDocComment() === $this->getDocComment())
				&& $this->hasSameAttributes($contant)
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
		return $doc_comment;
		// TODO $filter
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
	public function getFinalClassName() : string
	{
		return $this->final_class;
	}

	//------------------------------------------------------------------------------------------ path
	public function path() : string
	{
		return $this->getFinalClassName() . '::' . $this->name;
	}

}
