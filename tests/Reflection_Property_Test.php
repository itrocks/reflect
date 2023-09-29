<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Tests\Data\F;
use ITRocks\Reflect\Tests\Data\Limited;
use ITRocks\Reflect\Tests\Data\MC;
use ITRocks\Reflect\Tests\Data\More;
use ITRocks\Reflect\Tests\Data\MP;
use ITRocks\Reflect\Tests\Data\MPT;
use ITRocks\Reflect\Tests\Data\MT;
use ITRocks\Reflect\Tests\Data\MTT;
use ITRocks\Reflect\Tests\Data\Trait_1;
use ITRocks\Reflect\Tests\Data\Trait_2;
use ITRocks\Reflect\Tests\Data\Trait_3;
use ITRocks\Reflect\Tests\Data\Trait_Property_Override;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class Reflection_Property_Test extends TestCase
{

	//------------------------------------------------------------------------------- testConstructor
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string|object $class
	 * @param array{class-string,string} $expected
	 */
	#[TestWith([0, MC::class, 'public_class_property', [MC::class, 'public_class_property']])]
	#[TestWith([1, new F, 'public_final_property', [F::class, 'public_final_property']])]
	public function testConstructor(int $key, object|string $class, string $name, array $expected)
		: void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid property */
		$property = new Reflection_Property($class, $name);
		self::assertEquals($expected, [$property->class, $property->name], "data set #$key");
	}

	//------------------------------------------------------------------ testGetDeclaringClassAndName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string $class
	 */
	#[TestWith([0, MC::class, 'public_class_property',                   MC::class])]
	#[TestWith([1, F::class,  'public_class_property',                   MC::class])]
	#[TestWith([2, F::class,  'public_final_property',                   F::class])]
	#[TestWith([3, MC::class, 'public_parent_property',                  MP::class])]
	#[TestWith([4, MC::class, 'public_trait_overridden_property',        MC::class])]
	#[TestWith([5, MC::class, 'public_trait_property',                   MC::class])]
	#[TestWith([6, MC::class, 'public_trait_trait_overridden_property',  MC::class])]
	#[TestWith([7, MC::class, 'public_trait_trait_property',             MC::class])]
	#[TestWith([8, MC::class, 'public_parent_overridden_property',       MC::class])]
	#[TestWith([9, MC::class, 'public_parent_trait_overridden_property', MC::class])]
	#[TestWith([9, MC::class, 'public_parent_trait_property',            MP::class])]
	public function testGetDeclaringClassAndName(
		int $key, string $class, string $name, string $expected
	) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid property */
		$native = new ReflectionProperty($class, $name);
		/** @noinspection PhpUnhandledExceptionInspection Valid property */
		$reflection = new Reflection_Property($class, $name);
		self::assertEquals($native->getDeclaringClass()->name, $expected, "data set #$key native");
		self::assertEquals($expected, $reflection->getDeclaringClassName(), "data set #$key name");
		self::assertEquals($expected, $reflection->getDeclaringClass()->name, "data set #$key class");
	}

	//------------------------------------------------------------------ testGetDeclaringTraitAndName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string $class
	 */
	#[TestWith([0,  MC::class, 'public_class_property',                   MC::class])]
	#[TestWith([1,  F::class,  'public_class_property',                   MC::class])]
	#[TestWith([2,  F::class,  'public_final_property',                   F::class])]
	#[TestWith([3,  MC::class, 'public_parent_property',                  MP::class])]
	#[TestWith([4,  MC::class, 'public_trait_overridden_property',        MC::class])]
	#[TestWith([5,  MC::class, 'public_trait_property',                   MT::class])]
	#[TestWith([6,  MC::class, 'public_trait_trait_overridden_property',  MC::class])]
	#[TestWith([7,  MC::class, 'public_trait_trait_property',             MTT::class])]
	#[TestWith([8,  MC::class, 'public_parent_overridden_property',       MC::class])]
	#[TestWith([9,  MC::class, 'public_parent_trait_overridden_property', MC::class])]
	#[TestWith([10, MC::class, 'public_parent_trait_property',            MPT::class])]
	#[TestWith([11, Trait_Property_Override::class, 'property', Trait_Property_Override::class])]
	#[TestWith([12, Trait_1::class, 'property', Trait_1::class])]
	#[TestWith([13, Trait_2::class, 'property', Trait_2::class])]
	#[TestWith([14, Trait_3::class, 'property', Trait_3::class])]
	#[TestWith([15, Limited::class, 'property', Trait_3::class])] // Special case : could not differentiate property from the class and the trait => force inherit
	public function testGetDeclaringTraitAndName(
		int $key, string $class, string $name, string $expected
	) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid property */
		$reflection = new Reflection_Property($class, $name);
		self::assertEquals($expected, $reflection->getDeclaringClassName(true), "data set #$key name");
		self::assertEquals($expected, $reflection->getDeclaringClass(true)->name, "data set #$key class");
	}

	//----------------------------------------------------------------------------- testGetDocComment
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param int<0,max>   $filter
	 * @param class-string $class
	 */
	#[TestWith([0,  MC::class, 'public_class_property',                   Reflection::T_INHERIT, "/** MC:class_property */"])]
	#[TestWith([1,  F::class,  'public_class_property',                   Reflection::T_INHERIT, "/** MC:class_property */"])]
	#[TestWith([2,  F::class,  'public_final_property',                   Reflection::T_INHERIT, "/** F:final_property */"])]
	#[TestWith([3,  MC::class, 'public_parent_property',                  Reflection::T_INHERIT, "/** MP:parent_property */"])]
	#[TestWith([4,  MC::class, 'public_trait_overridden_property',        Reflection::T_INHERIT, "/** MC:trait_overridden_property */\n/** MT:trait_overridden_property */"])]
	#[TestWith([5,  MC::class, 'public_trait_property',                   Reflection::T_INHERIT, "/** MT:trait_property */"])]
	#[TestWith([6,  MC::class, 'public_trait_trait_overridden_property',  Reflection::T_INHERIT, "/** MC:trait_trait_overridden_property */\n/** MT:trait_trait_overridden_property */\n/** MTT:trait_trait_overridden_property */"])]
	#[TestWith([7,  MC::class, 'public_trait_trait_property',             Reflection::T_INHERIT, "/** MTT:trait_trait_property */"])]
	#[TestWith([8,  MC::class, 'public_parent_overridden_property',       Reflection::T_INHERIT, "/** MC:parent_overridden_property */\n/** MP:parent_overridden_property */"])]
	#[TestWith([9,  MC::class, 'public_parent_trait_overridden_property', Reflection::T_INHERIT, "/** MC:parent_trait_overridden_property */\n/** MPT:parent_trait_overridden_property */"])]
	#[TestWith([10, MC::class, 'public_parent_trait_property',            Reflection::T_INHERIT, "/** MPT:parent_trait_property */"])]
	#[TestWith([11, Trait_Property_Override::class, 'property', Reflection::T_INHERIT, "/** 1 */\n/** 2 */\n/** 1 */\n/** 2 */"])]
	#[TestWith([12, Trait_1::class, 'property', Reflection::T_INHERIT, "/** 2 */\n/** 1 */\n/** 2 */"])]
	#[TestWith([13, Trait_2::class, 'property', Reflection::T_INHERIT, "/** 1 */\n/** 2 */"])]
	#[TestWith([14, Trait_3::class, 'property', Reflection::T_INHERIT, "/** 2 */"])]
	#[TestWith([15, Limited::class, 'property', Reflection::T_INHERIT, "/** 2 */"])] // Special case : could not differentiate property from the class and the trait => force inherit
	#[TestWith([16, More::class, 'private', Reflection::T_INHERIT, "/**\n\t * B\n\t * @noinspection PhpUnusedPrivateFieldInspection\n\t * @phpstan-ignore-next-line For testing\n\t */"])]
	#[TestWith([17, MC::class, 'public_class_property',                   Reflection::T_LOCAL, "/** MC:class_property */"])]
	#[TestWith([18, F::class,  'public_class_property',                   Reflection::T_LOCAL, "/** MC:class_property */"])]
	#[TestWith([19, F::class,  'public_final_property',                   Reflection::T_LOCAL, "/** F:final_property */"])]
	#[TestWith([20, MC::class, 'public_parent_property',                  Reflection::T_LOCAL, "/** MP:parent_property */"])]
	#[TestWith([21, MC::class, 'public_trait_overridden_property',        Reflection::T_LOCAL, "/** MC:trait_overridden_property */"])]
	#[TestWith([22, MC::class, 'public_trait_property',                   Reflection::T_LOCAL, "/** MT:trait_property */"])]
	#[TestWith([23, MC::class, 'public_trait_trait_overridden_property',  Reflection::T_LOCAL, "/** MC:trait_trait_overridden_property */"])]
	#[TestWith([24, MC::class, 'public_trait_trait_property',             Reflection::T_LOCAL, "/** MTT:trait_trait_property */"])]
	#[TestWith([25, MC::class, 'public_parent_overridden_property',       Reflection::T_LOCAL, "/** MC:parent_overridden_property */"])]
	#[TestWith([26, MC::class, 'public_parent_trait_overridden_property', Reflection::T_LOCAL, "/** MC:parent_trait_overridden_property */"])]
	#[TestWith([27, MC::class, 'public_parent_trait_property',            Reflection::T_LOCAL, "/** MPT:parent_trait_property */"])]
	#[TestWith([28, MC::class, 'public_class_property',                   Reflection::T_EXTENDS, "/** MC:class_property */"])]
	#[TestWith([29, F::class,  'public_class_property',                   Reflection::T_EXTENDS, "/** MC:class_property */"])]
	#[TestWith([30, F::class,  'public_final_property',                   Reflection::T_EXTENDS, "/** F:final_property */"])]
	#[TestWith([31, MC::class, 'public_parent_property',                  Reflection::T_EXTENDS, "/** MP:parent_property */"])]
	#[TestWith([32, MC::class, 'public_trait_overridden_property',        Reflection::T_EXTENDS, "/** MC:trait_overridden_property */"])]
	#[TestWith([33, MC::class, 'public_trait_property',                   Reflection::T_EXTENDS, "/** MT:trait_property */"])]
	#[TestWith([34, MC::class, 'public_trait_trait_overridden_property',  Reflection::T_EXTENDS, "/** MC:trait_trait_overridden_property */"])]
	#[TestWith([35, MC::class, 'public_trait_trait_property',             Reflection::T_EXTENDS, "/** MTT:trait_trait_property */"])]
	#[TestWith([36, MC::class, 'public_parent_overridden_property',       Reflection::T_EXTENDS, "/** MC:parent_overridden_property */\n/** MP:parent_overridden_property */"])]
	#[TestWith([37, MC::class, 'public_parent_trait_overridden_property', Reflection::T_EXTENDS, "/** MC:parent_trait_overridden_property */\n/** MPT:parent_trait_overridden_property */"])]
	#[TestWith([38, MC::class, 'public_parent_trait_property',            Reflection::T_EXTENDS, "/** MPT:parent_trait_property */"])]
	#[TestWith([39, MC::class, 'public_class_property',                   Reflection::T_USE, "/** MC:class_property */"])]
	#[TestWith([40, F::class,  'public_class_property',                   Reflection::T_USE, "/** MC:class_property */"])]
	#[TestWith([41, F::class,  'public_final_property',                   Reflection::T_USE, "/** F:final_property */"])]
	#[TestWith([42, MC::class, 'public_parent_property',                  Reflection::T_USE, "/** MP:parent_property */"])]
	#[TestWith([43, MC::class, 'public_trait_overridden_property',        Reflection::T_USE, "/** MC:trait_overridden_property */\n/** MT:trait_overridden_property */"])]
	#[TestWith([44, MC::class, 'public_trait_property',                   Reflection::T_USE, "/** MT:trait_property */"])]
	#[TestWith([45, MC::class, 'public_trait_trait_overridden_property',  Reflection::T_USE, "/** MC:trait_trait_overridden_property */\n/** MT:trait_trait_overridden_property */\n/** MTT:trait_trait_overridden_property */"])]
	#[TestWith([46, MC::class, 'public_trait_trait_property',             Reflection::T_USE, "/** MTT:trait_trait_property */"])]
	#[TestWith([47, MC::class, 'public_parent_overridden_property',       Reflection::T_USE, "/** MC:parent_overridden_property */"])]
	#[TestWith([48, MC::class, 'public_parent_trait_overridden_property', Reflection::T_USE, "/** MC:parent_trait_overridden_property */"])]
	#[TestWith([49, MC::class, 'public_parent_trait_property',            Reflection::T_USE, "/** MPT:parent_trait_property */"])]
	public function testGetDocComment(int $key, string $class, string $name, int $filter, string $expected) : void
	{
		require_once __DIR__ . '/Data/Trait_Property_Override.php';
		/** @noinspection PhpUnhandledExceptionInspection Valid callable */
		$reflection = new Reflection_Property($class, $name);
		$actual     = $reflection->getDocComment($filter, false);
		self::assertEquals($expected, $actual, "data set #$key");
		$actual = $reflection->getDocComment($filter);
		self::assertEquals($expected, $actual, "data set #$key cache write");
		$actual = $reflection->getDocComment($filter);
		self::assertEquals($expected, $actual, "data set #$key cache read");
	}

	//----------------------------------------------------------------------- testGetDocCommentLocate
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param int<0,max>   $filter
	 * @param class-string $class
	 */
	#[TestWith([0,  MC::class, 'public_class_property',                   Reflection::T_INHERIT, "/** FROM " . MC::class . " */\n/** MC:class_property */"])]
	#[TestWith([1,  F::class,  'public_class_property',                   Reflection::T_INHERIT, "/** FROM " . MC::class . " */\n/** MC:class_property */"])]
	#[TestWith([2,  F::class,  'public_final_property',                   Reflection::T_INHERIT, "/** FROM " . F::class . " */\n/** F:final_property */"])]
	#[TestWith([3,  MC::class, 'public_parent_property',                  Reflection::T_INHERIT, "/** FROM " . MP::class . " */\n/** MP:parent_property */"])]
	#[TestWith([4,  MC::class, 'public_trait_overridden_property',        Reflection::T_INHERIT, "/** FROM " . MC::class . " */\n/** MC:trait_overridden_property */\n/** FROM " . MT::class . " */\n/** MT:trait_overridden_property */"])]
	#[TestWith([5,  MC::class, 'public_trait_property',                   Reflection::T_INHERIT, "/** FROM " . MT::class . " */\n/** MT:trait_property */"])]
	#[TestWith([6,  MC::class, 'public_trait_trait_overridden_property',  Reflection::T_INHERIT, "/** FROM " . MC::class . " */\n/** MC:trait_trait_overridden_property */\n/** FROM " . MT::class . " */\n/** MT:trait_trait_overridden_property */\n/** FROM " . MTT::class . " */\n/** MTT:trait_trait_overridden_property */"])]
	#[TestWith([7,  MC::class, 'public_trait_trait_property',             Reflection::T_INHERIT, "/** FROM " . MTT::class . " */\n/** MTT:trait_trait_property */"])]
	#[TestWith([8,  MC::class, 'public_parent_overridden_property',       Reflection::T_INHERIT, "/** FROM " . MC::class . " */\n/** MC:parent_overridden_property */\n/** FROM " . MP::class . " */\n/** MP:parent_overridden_property */"])]
	#[TestWith([9,  MC::class, 'public_parent_trait_overridden_property', Reflection::T_INHERIT, "/** FROM " . MC::class . " */\n/** MC:parent_trait_overridden_property */\n/** FROM " . MPT::class . " */\n/** MPT:parent_trait_overridden_property */"])]
	#[TestWith([10, MC::class, 'public_parent_trait_property',            Reflection::T_INHERIT, "/** FROM " . MPT::class . " */\n/** MPT:parent_trait_property */"])]
	#[TestWith([14, Trait_3::class, 'property', Reflection::T_INHERIT, "/** FROM " . Trait_3::class . " */\n/** 2 */"])]
	#[TestWith([15, Limited::class, 'property', Reflection::T_INHERIT, "/** FROM " . Trait_3::class . " */\n/** 2 */"])] // Special case : could not differentiate property from the class and the trait => force inherit
	#[TestWith([16, More::class, 'private', Reflection::T_INHERIT, "/** FROM " . More::class . " */\n/**\n\t * B\n\t * @noinspection PhpUnusedPrivateFieldInspection\n\t * @phpstan-ignore-next-line For testing\n\t */"])]
	public function testGetDocCommentLocate(int $key, string $class, string $name, int $filter, string $expected) : void
	{
		require_once __DIR__ . '/Data/Trait_Property_Override.php';
		/** @noinspection PhpUnhandledExceptionInspection Valid callable */
		$reflection = new Reflection_Property($class, $name);
		$actual     = $reflection->getDocComment($filter, false, true);
		self::assertEquals($expected, $actual, "data set #$key");
		$actual = $reflection->getDocComment($filter, true, true);
		self::assertEquals($expected, $actual, "data set #$key cache write");
		$actual = $reflection->getDocComment($filter, true, true);
		self::assertEquals($expected, $actual, "data set #$key cache read");
	}

	//---------------------------------------------------------------------- testGetFinalClassAndName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param array{class-string,string} $property
	 * @param class-string               $expected
	 */
	#[TestWith([0, [MC::class, 'public_class_property'],        MC::class])]
	#[TestWith([1, [MC::class, 'public_parent_property'],       MC::class])]
	#[TestWith([2, [MC::class, 'public_parent_trait_property'], MC::class])]
	#[TestWith([3, [MC::class, 'public_trait_property'],        MC::class])]
	#[TestWith([4, [new F,     'public_trait_property'],        F::class])]
	public function testGetFinalClassAndName(int $key, array $property, string $expected) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid callable */
		$reflection = new Reflection_Property($property[0], $property[1]);
		self::assertEquals($expected, $reflection->getFinalClassName(), "data set #$key name");
		self::assertEquals($expected, $reflection->getFinalClass()->name, "data set #$key class");
	}

	//--------------------------------------------------------------------------------- testGetParent
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param array{class-string,string}                     $property
	 * @param ?array{class-string,class-string,class-string} $expected
	 */
	#[TestWith([0, [MC::class, 'public_class_property'],                   null])]
	#[TestWith([1, [MC::class, 'public_parent_property'],                  null])]
	#[TestWith([2, [MC::class, 'public_parent_trait_property'],            null])]
	#[TestWith([3, [MC::class, 'public_trait_property'],                   null])]
	#[TestWith([4, [new F,     'public_trait_property'],                   null])]
	#[TestWith([5, [MC::class, 'public_trait_overridden_property'],        null])]
	#[TestWith([6, [MC::class, 'public_parent_overridden_property'],       [MP::class, MP::class, MP::class]])]
	#[TestWith([7, [MC::class, 'public_parent_trait_overridden_property'], [MP::class, MP::class, MPT::class]])]
	#[TestWith([8, [MC::class, 'private_class_property'],                  null])]
	#[TestWith([9, [More::class, 'private'],                               null])]
	#[TestWith([9, [More::class, 'private2'],                              null])]
	public function testGetParent(int $key, array $property, ?array $expected) : void
	{
		require_once __DIR__ . '/Data/Trait_Property_Override.php';
		/** @noinspection PhpUnhandledExceptionInspection Valid callable */
		$reflection = new Reflection_Property($property[0], $property[1]);
		$parent     = $reflection->getParent();
		self::assertEquals(
			isset($expected) ? array_merge($expected, [$reflection->name]) : [],
			isset($parent)
				? [
					$parent->class,
					$parent->getDeclaringClassName(),
					$parent->getDeclaringClassName(true),
					$parent->name
				]
				: [],
			"data set #$key"
		);
	}

	//----------------------------------------------------------------------------------- testGetType
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param array{class-string,string} $property
	 */
	#[TestWith([0, [MC::class, 'public_class_property'], 'mixed'])]
	public function testGetType(int $key, array $property, string $expected) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid property */
		$native_property = new ReflectionProperty($property[0], $property[1]);
		/** @noinspection PhpUnhandledExceptionInspection Valid property */
		$reflection_property = new Reflection_Property($property[0], $property[1]);
		self::assertEquals($expected, $native_property->getType(), "data set #$key native");
		self::assertEquals($expected, $reflection_property->getType(), "data set #$key type");
	}

	//---------------------------------------------------------------------------------------- testIs
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param array{class-string,string} $property1
	 * @param array{class-string,string} $property2
	 */
	#[TestWith([
		0,
		[MC::class, 'public_parent_trait_overridden_property'],
		[MPT::class, 'public_parent_trait_overridden_property'],
		false
	])]
	#[TestWith([
		1,
		[MC::class, 'public_parent_property'],
		[MP::class, 'public_parent_property'],
		true
	])]
	public function testIs(int $key, array $property1, array $property2, bool $expected) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid property */
		$reflection1 = new Reflection_Property($property1[0], $property1[1]);
		/** @noinspection PhpUnhandledExceptionInspection Valid property */
		$reflection2 = new Reflection_Property($property2[0], $property2[1]);
		self::assertEquals($expected, $reflection1->is($reflection2), "data set #$key");
	}

}
