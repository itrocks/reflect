<?php
namespace ITRocks\Reflect\Tests;

use CA;
use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Tests\Data\A;
use ITRocks\Reflect\Tests\Data\C;
use ITRocks\Reflect\Tests\Data\I;
use ITRocks\Reflect\Tests\Data\II;
use ITRocks\Reflect\Tests\Data\IIB;
use ITRocks\Reflect\Tests\Data\MC;
use ITRocks\Reflect\Tests\Data\MI;
use ITRocks\Reflect\Tests\Data\MII;
use ITRocks\Reflect\Tests\Data\MP;
use ITRocks\Reflect\Tests\Data\MPT;
use ITRocks\Reflect\Tests\Data\MT;
use ITRocks\Reflect\Tests\Data\MTT;
use ITRocks\Reflect\Tests\Data\Namespace_Use;
use ITRocks\Reflect\Tests\Data\P;
use ITRocks\Reflect\Tests\Data\PI;
use ITRocks\Reflect\Tests\Data\PT;
use ITRocks\Reflect\Tests\Data\R;
use ITRocks\Reflect\Tests\Data\T;
use ITRocks\Reflect\Tests\Data\TO;
use ITRocks\Reflect\Tests\Data\TT;
use NS\B\CB;
use NS\B\CD;
use NS\C\CC;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class Reflection_Class_Test extends TestCase
{

	//-------------------------------------------------------------------- getUndefinedClassComponent
	/**
	 * @param ReflectionClass<C>  $native_class
	 * @param Reflection_Class<C> $reflection_class
	 * @template C of object
	 */
	public function getUndefinedClassComponent(
		ReflectionClass $native_class, Reflection_Class $reflection_class,
		string $get_class_component_method, string $class_component_name
	) : void
	{
		try {
			/** @phpstan-ignore-next-line Parameter #1 $callback of function call_user_func expects callable */
			call_user_func([$native_class, $get_class_component_method], $class_component_name);
			$code    = 0;
			$message = '';
		}
		catch (ReflectionException $exception) {
			$code    = $exception->getCode();
			$message = $exception->getMessage();
		}
		$this->expectException(ReflectionException::class);
		$this->expectExceptionCode($code);
		$this->expectExceptionMessage($message);
		/** @phpstan-ignore-next-line Parameter #1 $callback of function call_user_func expects callable */
		call_user_func([$reflection_class, $get_class_component_method], $class_component_name);
	}

	//------------------------------------------------------------------------------- testConstructor
	public function testConstructor() : void
	{
		$native     = new ReflectionClass(self::class);
		$reflection = new Reflection_Class(self::class);
		self::assertEquals($native->name, $reflection->name, 'class');

		$native     = new ReflectionClass($this);
		/** @noinspection PhpUnhandledExceptionInspection object */
		$reflection = new Reflection_Class($this);
		self::assertEquals($native->name, $reflection->name, '$this');

		$this->expectException(ReflectionException::class);
		/** @phpstan-ignore-next-line Testing invalid call */
		new Reflection_Class('Unknown_little_thing');
	}

	//---------------------------------------------------------------------- testGetClassListAndNames
	/**
	 * @param int                          $key
	 * @param int-mask-of<Reflection::T_*> $filter
	 * @param list<class-string>           $expected
	 */
	#[TestWith([0, Reflection::T_LOCAL, [C::class]])]
	#[TestWith([1, Reflection::T_EXTENDS, [C::class, P::class, R::class]])]
	#[TestWith([2, Reflection::T_IMPLEMENTS, [C::class, I::class, II::class, IIB::class]])]
	#[TestWith([3, Reflection::T_USE, [C::class, T::class, TO::class, TT::class]])]
	#[TestWith([4, Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS, [C::class, I::class, II::class, IIB::class, P::class, PI::class, R::class]])]
	#[TestWith([5, Reflection::T_EXTENDS | Reflection::T_USE, [C::class, T::class, TO::class, TT::class, P::class, PT::class, R::class]])]
	#[TestWith([6, Reflection::T_IMPLEMENTS | Reflection::T_USE, [C::class, T::class, TO::class, TT::class, I::class, II::class, IIB::class]])]
	#[TestWith([7, Reflection::T_INHERIT, [C::class, T::class, TO::class, TT::class, I::class, II::class, IIB::class, P::class, PT::class, PI::class, R::class]])]
	public function testGetClassListAndNames(int $key, int $filter, array $expected) : void
	{
		$class = new Reflection_Class(C::class);
		$list  = $class->getClassListNames($filter);
		self::assertEquals($expected, $list, "data set #$key names");
		$list = array_keys($class->getClassList($filter));
		self::assertEquals($expected, $list, "data set #$key list");
	}

	//------------------------------------------------------------------------------ testGetClassTree
	/**
	 * @param int                          $key
	 * @param int-mask-of<Reflection::T_*> $filter
	 * @param array<class-string,array<class-string,array<class-string,array<class-string,mixed>>>> $expected
	 */
	#[TestWith([0, Reflection::T_LOCAL,                              [C::class => []]])]
	#[TestWith([1, Reflection::T_EXTENDS,                            [C::class => [P::class => [R::class => []]]]])]
	#[TestWith([2, Reflection::T_IMPLEMENTS,                         [C::class => [I::class => [II::class => [], IIB::class => []]]]])]
	#[TestWith([3, Reflection::T_USE,                                [C::class => [T::class => [TT::class => []], TO::class => []]]])]
	#[TestWith([4, Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS, [C::class => [I::class => [II::class => [], IIB::class => []], P::class => [PI::class => [], I::class => [II::class => [], IIB::class => []], R::class => []]]]])]
	#[TestWith([5, Reflection::T_EXTENDS | Reflection::T_USE,        [C::class => [T::class => [TT::class => []], P::class => [PT::class => [TO::class => []], R::class => []], TO::class => []]]])]
	#[TestWith([6, Reflection::T_IMPLEMENTS | Reflection::T_USE,     [C::class => [T::class => [TT::class => []], I::class => [II::class => [], IIB::class => []], TO::class => []]]])]
	#[TestWith([7, Reflection::T_INHERIT,                            [C::class => [T::class => [TT::class => []], I::class => [II::class => [], IIB::class => []], P::class => [PT::class => [TO::class => []], PI::class => [], I::class => [II::class => [], IIB::class => []], R::class => []], TO::class => []]]])]
	public function testGetClassTree(int $key, int $filter, array $expected) : void
	{
		$class = new Reflection_Class(C::class);
		self::assertEquals($expected, $class->getClassTree($filter), "data set #$key");
	}

	//---------------------------------------------------------------------------- testGetConstructor
	public function testGetConstructor() : void
	{
		$native     = (new ReflectionClass(C::class))->getConstructor();
		$reflection = (new Reflection_Class(C::class))->getConstructor();
		self::assertNotNull($native);
		self::assertNotNull($reflection);
		self::assertEquals([$native->class, $native->name], [$reflection->class, $reflection->name]);
		$native     = (new ReflectionClass(R::class))->getConstructor();
		$reflection = (new Reflection_Class(R::class))->getConstructor();
		self::assertNull($native);
		self::assertNull($reflection);
	}

	//----------------------------------------------------------------------------- testGetDocComment
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string                 $class_name
	 * @param int-mask-of<Reflection::T_*> $filter
	 */
	#[TestWith([0,  C::class, Reflection::T_LOCAL, "/** C:DC */"])]
	#[TestWith([1,  C::class, Reflection::T_EXTENDS, "/** C:DC */\n/** P:DC */\n/** R:DC */"])]
	#[TestWith([2,  C::class, Reflection::T_IMPLEMENTS, "/** C:DC */\n/** I:DC */\n/** II:DC */"])]
	#[TestWith([3,  C::class, Reflection::T_USE, "/** C:DC */\n/** T:DC */\n/** TT:DC */"])]
	#[TestWith([4,  C::class, Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS, "/** C:DC */\n/** I:DC */\n/** II:DC */\n/** P:DC */\n/** PI:DC */\n/** R:DC */"])]
	#[TestWith([5,  C::class, Reflection::T_EXTENDS | Reflection::T_USE, "/** C:DC */\n/** T:DC */\n/** TT:DC */\n/** P:DC */\n/** PT:DC */\n/** R:DC */"])]
	#[TestWith([6,  C::class, Reflection::T_IMPLEMENTS | Reflection::T_USE, "/** C:DC */\n/** T:DC */\n/** TT:DC */\n/** I:DC */\n/** II:DC */"])]
	#[TestWith([7,  C::class, Reflection::T_INHERIT, "/** C:DC */\n/** T:DC */\n/** TT:DC */\n/** I:DC */\n/** II:DC */\n/** P:DC */\n/** PT:DC */\n/** PI:DC */\n/** R:DC */"])]
	#[TestWith([8,  I::class, Reflection::T_LOCAL, "/** I:DC */"])]
	#[TestWith([9,  I::class, Reflection::T_EXTENDS, "/** I:DC */"])]
	#[TestWith([10, I::class, Reflection::T_IMPLEMENTS, "/** I:DC */\n/** II:DC */"])]
	#[TestWith([11, I::class, Reflection::T_USE, "/** I:DC */"])]
	#[TestWith([12, T::class, Reflection::T_LOCAL, "/** T:DC */"])]
	#[TestWith([13, T::class, Reflection::T_EXTENDS, "/** T:DC */"])]
	#[TestWith([14, T::class, Reflection::T_IMPLEMENTS, "/** T:DC */"])]
	#[TestWith([15, T::class, Reflection::T_USE, "/** T:DC */\n/** TT:DC */"])]
	public function testGetDocComment(int $key, string $class_name, int $filter, string $expected)
		: void
	{
		/** @noinspection PhpUnhandledExceptionInspection Always valid */
		$reflection_class = new Reflection_Class($class_name);
		self::assertEquals(
			$expected, $reflection_class->getDocComment($filter, false), "data set #$key"
		);
		self::assertEquals(
			$expected, $reflection_class->getDocComment($filter), "data set #$key cache write"
		);
		self::assertEquals(
			$expected, $reflection_class->getDocComment($filter), "data set #$key cache read"
		);
	}

	//----------------------------------------------------------------------- testGetDocCommentLocate
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string                 $class_name
	 * @param int-mask-of<Reflection::T_*> $filter
	 */
	#[TestWith([0,  C::class, Reflection::T_LOCAL, "/** FROM " . C::class . " */\n/** C:DC */"])]
	#[TestWith([1,  C::class, Reflection::T_EXTENDS, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . P::class . " */\n/** P:DC */\n/** FROM " . R::class . " */\n/** R:DC */"])]
	#[TestWith([2,  C::class, Reflection::T_IMPLEMENTS, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */"])]
	#[TestWith([3,  C::class, Reflection::T_USE, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */"])]
	#[TestWith([4,  C::class, Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */\n/** FROM " . P::class . " */\n/** P:DC */\n/** FROM " . PI::class . " */\n/** PI:DC */\n/** FROM " . R::class . " */\n/** R:DC */"])]
	#[TestWith([5,  C::class, Reflection::T_EXTENDS | Reflection::T_USE, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */\n/** FROM " . P::class . " */\n/** P:DC */\n/** FROM " . PT::class . " */\n/** PT:DC */\n/** FROM " . R::class . " */\n/** R:DC */"])]
	#[TestWith([6,  C::class, Reflection::T_IMPLEMENTS | Reflection::T_USE, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */\n/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */"])]
	#[TestWith([7,  C::class, Reflection::T_INHERIT, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */\n/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */\n/** FROM " . P::class . " */\n/** P:DC */\n/** FROM " . PT::class . " */\n/** PT:DC */\n/** FROM " . PI::class . " */\n/** PI:DC */\n/** FROM " . R::class . " */\n/** R:DC */"])]
	#[TestWith([8,  I::class, Reflection::T_LOCAL, "/** FROM " . I::class . " */\n/** I:DC */"])]
	#[TestWith([9,  I::class, Reflection::T_EXTENDS, "/** FROM " . I::class . " */\n/** I:DC */"])]
	#[TestWith([10, I::class, Reflection::T_IMPLEMENTS, "/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */"])]
	#[TestWith([11, I::class, Reflection::T_USE, "/** FROM " . I::class . " */\n/** I:DC */"])]
	#[TestWith([12, T::class, Reflection::T_LOCAL, "/** FROM " . T::class . " */\n/** T:DC */"])]
	#[TestWith([13, T::class, Reflection::T_EXTENDS, "/** FROM " . T::class . " */\n/** T:DC */"])]
	#[TestWith([14, T::class, Reflection::T_IMPLEMENTS, "/** FROM " . T::class . " */\n/** T:DC */"])]
	#[TestWith([15, T::class, Reflection::T_USE, "/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */"])]
	public function testGetDocCommentLocate(
		int $key, string $class_name, int $filter, string $expected
	) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection class-string */
		$reflection_class = new Reflection_Class($class_name);
		self::assertEquals(
			$expected, $reflection_class->getDocComment($filter, false, true), "data set #$key"
		);
		self::assertEquals(
			$expected, $reflection_class->getDocComment($filter, true, true), "data set #$key cache write"
		);
		self::assertEquals(
			$expected, $reflection_class->getDocComment($filter, true, true), "data set #$key cache read"
		);
	}

	//-------------------------------------------------------------------------------- testGetExtends
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string $class_name
	 * @param list<class-string> $expected
	 */
	#[TestWith([0, C::class, [P::class]])]
	#[TestWith([1, R::class, []])]
	#[TestWith([2, I::class, [II::class, IIB::class]])]
	public function testGetExtends(int $key, string $class_name, array $expected) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection class-string */
		$reflection_class = new Reflection_Class($class_name);
		self::assertEquals($expected, $reflection_class->getExtends(), "data set #$key");
	}

	//------------------------------------------------------------------------- testGetInterfaceNames
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string                 $class_name
	 * @param int-mask-of<Reflection::T_*> $filter
	 * @param list<class-string>           $expected
	 */
	#[TestWith([0, C::class, Reflection::T_LOCAL, [I::class]])]
	#[TestWith([1, C::class, Reflection::T_IMPLEMENTS, [I::class, II::class, IIB::class]])]
	#[TestWith([2, C::class, Reflection::T_EXTENDS, [I::class, PI::class]])]
	#[TestWith([3, C::class, Reflection::T_INHERIT, [I::class, II::class, IIB::class, PI::class]])]
	public function testGetInterfaceNames(int $key, string $class_name, int $filter, array $expected)
		: void
	{
		/** @noinspection PhpUnhandledExceptionInspection class-string */
		$reflection_class = new Reflection_Class($class_name);
		self::assertEquals($expected, $reflection_class->getInterfaceNames($filter), "data set #$key");
	}

	//----------------------------------------------------------------------------- testGetInterfaces
	public function testGetInterfaces() : void
	{
		$actual = array_map(
			function(Reflection_Class $interface) { return $interface->name; },
			(new Reflection_Class(C::class))->getInterfaces()
		);
		$expected = [I::class, II::class, IIB::class, PI::class];
		self::assertEquals(array_combine($expected, $expected), $actual);
	}

	//--------------------------------------------------------------------------------- testGetMethod
	public function testGetMethod() : void
	{
		$native_class     = new ReflectionClass(C::class);
		$reflection_class = new Reflection_Class(C::class);
		self::assertEquals(
			get_object_vars($native_class->getMethod('publicClassMethod')),
			get_object_vars($reflection_class->getMethod('publicClassMethod'))
		);
		$this->getUndefinedClassComponent(
			$native_class, $reflection_class, 'getMethod', 'doesNotExist'
		);
	}

	//-------------------------------------------------------------------------------- testGetMethods
	/**
	 * @noinspection DuplicatedCode Same results, but not same input
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string                                               $class_name
	 * @param ?int-mask-of<Reflection::T_*>                              $filter
	 * @param list<array{class-string,string,class-string,class-string}> $expected
	 */
	#[TestWith([0, MC::class, Reflection::T_LOCAL, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class]
	]])]
	#[TestWith([1, MC::class, Reflection::T_EXTENDS, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MP::class,  'protectedParentMethod',           MC::class, MP::class],
		[MP::class,  'publicParentMethod',              MC::class, MP::class]
	]])]
	#[TestWith([2, MC::class, Reflection::T_IMPLEMENTS, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MI::class,  'interfaceMethod',                 MC::class, MI::class],
		[MII::class, 'interfaceInterfaceMethod',        MC::class, MII::class]
	]])]
	#[TestWith([3, MC::class, Reflection::T_USE, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MC::class,  'privateTraitMethod',              MC::class, MT::class],
		[MC::class,  'protectedTraitMethod',            MC::class, MT::class],
		[MC::class,  'publicTraitMethod',               MC::class, MT::class],
		[MC::class,  'privateTraitTraitMethod',         MC::class, MTT::class],
		[MC::class,  'protectedTraitTraitMethod',       MC::class, MTT::class],
		[MC::class,  'publicTraitTraitMethod',          MC::class, MTT::class]
	]])]
	#[TestWith([4, MC::class, Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MP::class,  'protectedParentMethod',           MC::class, MP::class],
		[MP::class,  'publicParentMethod',              MC::class, MP::class],
		[MI::class,  'interfaceMethod',                 MC::class, MI::class],
		[MII::class, 'interfaceInterfaceMethod',        MC::class, MII::class]
	]])]
	#[TestWith([5, MC::class, Reflection::T_EXTENDS | Reflection::T_USE, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MP::class,  'protectedParentMethod',           MC::class, MP::class],
		[MP::class,  'publicParentMethod',              MC::class, MP::class],
		[MP::class,  'protectedParentTraitMethod',      MC::class, MPT::class],
		[MP::class,  'publicParentTraitMethod',         MC::class, MPT::class],
		[MC::class,  'privateTraitMethod',              MC::class, MT::class],
		[MC::class,  'protectedTraitMethod',            MC::class, MT::class],
		[MC::class,  'publicTraitMethod',               MC::class, MT::class],
		[MC::class,  'privateTraitTraitMethod',         MC::class, MTT::class],
		[MC::class,  'protectedTraitTraitMethod',       MC::class, MTT::class],
		[MC::class,  'publicTraitTraitMethod',          MC::class, MTT::class]
	]])]
	#[TestWith([6, MC::class, Reflection::T_IMPLEMENTS | Reflection::T_USE, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MC::class,  'privateTraitMethod',              MC::class, MT::class],
		[MC::class,  'protectedTraitMethod',            MC::class, MT::class],
		[MC::class,  'publicTraitMethod',               MC::class, MT::class],
		[MC::class,  'privateTraitTraitMethod',         MC::class, MTT::class],
		[MC::class,  'protectedTraitTraitMethod',       MC::class, MTT::class],
		[MC::class,  'publicTraitTraitMethod',          MC::class, MTT::class],
		[MI::class,  'interfaceMethod',                 MC::class, MI::class],
		[MII::class, 'interfaceInterfaceMethod',        MC::class, MII::class]
	]])]
	#[TestWith([7, MC::class, Reflection::T_INHERIT, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MP::class,  'protectedParentMethod',           MC::class, MP::class],
		[MP::class,  'publicParentMethod',              MC::class, MP::class],
		[MP::class,  'protectedParentTraitMethod',      MC::class, MPT::class],
		[MP::class,  'publicParentTraitMethod',         MC::class, MPT::class],
		[MC::class,  'privateTraitMethod',              MC::class, MT::class],
		[MC::class,  'protectedTraitMethod',            MC::class, MT::class],
		[MC::class,  'publicTraitMethod',               MC::class, MT::class],
		[MC::class,  'privateTraitTraitMethod',         MC::class, MTT::class],
		[MC::class,  'protectedTraitTraitMethod',       MC::class, MTT::class],
		[MC::class,  'publicTraitTraitMethod',          MC::class, MTT::class],
		[MI::class,  'interfaceMethod',                 MC::class, MI::class],
		[MII::class, 'interfaceInterfaceMethod',        MC::class, MII::class]
	]])]
	#[TestWith([8, MC::class, null, [
		[MC::class,  'overrideParentMethod',            MC::class, MC::class],
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MP::class,  'protectedParentMethod',           MC::class, MP::class],
		[MP::class,  'publicParentMethod',              MC::class, MP::class],
		[MP::class,  'protectedParentTraitMethod',      MC::class, MPT::class],
		[MP::class,  'publicParentTraitMethod',         MC::class, MPT::class],
		[MC::class,  'privateTraitMethod',              MC::class, MT::class],
		[MC::class,  'protectedTraitMethod',            MC::class, MT::class],
		[MC::class,  'publicTraitMethod',               MC::class, MT::class],
		[MC::class,  'privateTraitTraitMethod',         MC::class, MTT::class],
		[MC::class,  'protectedTraitTraitMethod',       MC::class, MTT::class],
		[MC::class,  'publicTraitTraitMethod',          MC::class, MTT::class],
		[MI::class,  'interfaceMethod',                 MC::class, MI::class],
		[MII::class, 'interfaceInterfaceMethod',        MC::class, MII::class]
	]])]
	#[TestWith([9, A::class, Reflection_Method::IS_PRIVATE, []])]
	public function testGetMethods(int $key, string $class_name, ?int $filter, array $expected) : void
	{
		$actual = [];
		/** @noinspection PhpUnhandledExceptionInspection Always valid */
		foreach ((new Reflection_Class($class_name))->getMethods($filter) as $method) {
			$actual[] = join(' :: ', [
				$method->getDeclaringClassName(),
				$method->getName(),
				$method->getFinalClassName(),
				$method->getDeclaringClassName(true)
			]);
		}
		foreach ($expected as &$line) {
			$line = join(' :: ', $line);
		}
		self::assertEquals($expected, $actual, "Data set #$key");
	}

	//------------------------------------------------------------------ testGetMultipleNamespaceUses
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string $class_name
	 */
	#[TestWith([CA::class, 'NS\B'])]
	#[TestWith([CB::class, 'NS\C'])]
	#[TestWith([CC::class, 'NS\D'])]
	#[TestWith([CD::class, 'NS\E'])]
	public function testGetMultipleNamespaceUses(string $class_name, string $expected) : void
	{
		require_once __DIR__ . '/Data/Namespace_Use_Multiple.php';
		/** @noinspection PhpUnhandledExceptionInspection Valid class name */
		$class    = new Reflection_Class($class_name);
		$expected = [substr($expected, intval(strrpos($expected, '\\')) + 1) => $expected];
		self::assertEquals($expected, $class->getNamespaceUses(), $class_name);
		self::assertEquals($expected, $class->getNamespaceUses(), $class_name . ' cached');
	}

	//-------------------------------------------------------------------------- testGetNamespaceUses
	public function testGetNamespaceUses() : void
	{
		$class    = new Reflection_Class(Namespace_Use::class);
		$expected = [
			'T'                => 'A',
			'C'                => 'C',
			'Reflection_Class' => 'ITRocks\Reflect\Reflection_Class',
			'T1'               => Parse_Test::class,
			'Types'            => Types::class
		];
		$actual = $class->getNamespaceUses();
		self::assertEquals($expected, $actual);
		$actual = $class->getNamespaceUses();
		self::assertEquals($expected, $actual, 'cached');
	}

	//--------------------------------------------------------------------- testGetParentClassAndName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string $class_name
	 * @param class-string $expected
	 */
	#[TestWith([A::class, ''])]
	#[TestWith([C::class, P::class])]
	#[TestWith([I::class, ''])]
	#[TestWith([T::class, ''])]
	public function testGetParentClassAndName(string $class_name, string $expected) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Always valid */
		$class        = new Reflection_Class($class_name);
		$parent_class = $class->getParentClass();
		self::assertEquals(
			$expected, ($parent_class === false) ? '' : $parent_class->name, "$class_name class"
		);
		self::assertEquals($expected, $class->getParentClassName(), "$class_name name");
	}

	//----------------------------------------------------------------------------- testGetProperties
	/**
	 * @noinspection DuplicatedCode Same results, but not same input
	 * @param class-string $class_name
	 * @param ?int-mask-of<Reflection::T_*>                              $filter
	 * @param list<array{class-string,string,class-string,class-string}> $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0, MC::class, Reflection::T_LOCAL, [
		[MC::class,  'private_class_property',                  MC::class, MC::class],
		[MC::class,  'private_trait_trait_property',            MC::class, MC::class],
		[MC::class,  'protected_class_property',                MC::class, MC::class],
		[MC::class,  'public_class_property',                   MC::class, MC::class],
		[MC::class,  'public_parent_overridden_property',       MC::class, MC::class],
		[MC::class,  'public_parent_trait_overridden_property', MC::class, MC::class],
		[MC::class,  'public_trait_overridden_property',        MC::class, MC::class],
		[MC::class,  'public_trait_trait_overridden_property',  MC::class, MC::class]
	]])]
	#[TestWith([1, MC::class, Reflection::T_EXTENDS, [
		[MC::class,  'private_class_property',                  MC::class, MC::class],
		[MC::class,  'private_trait_trait_property',            MC::class, MC::class],
		[MC::class,  'protected_class_property',                MC::class, MC::class],
		[MC::class,  'public_class_property',                   MC::class, MC::class],
		[MC::class,  'public_parent_overridden_property',       MC::class, MC::class],
		[MC::class,  'public_parent_trait_overridden_property', MC::class, MC::class],
		[MC::class,  'public_trait_overridden_property',        MC::class, MC::class],
		[MC::class,  'public_trait_trait_overridden_property',  MC::class, MC::class],
		[MP::class,  'protected_parent_property',               MC::class, MP::class],
		[MP::class,  'public_parent_property',                  MC::class, MP::class]
	]])]
	#[TestWith([2, MC::class, Reflection::T_USE, [
		[MC::class,  'private_class_property',                  MC::class, MC::class],
		[MC::class,  'private_trait_trait_property',            MC::class, MC::class],
		[MC::class,  'protected_class_property',                MC::class, MC::class],
		[MC::class,  'public_class_property',                   MC::class, MC::class],
		[MC::class,  'public_parent_overridden_property',       MC::class, MC::class],
		[MC::class,  'public_parent_trait_overridden_property', MC::class, MC::class],
		[MC::class,  'public_trait_overridden_property',        MC::class, MC::class],
		[MC::class,  'public_trait_trait_overridden_property',  MC::class, MC::class],
		[MC::class,  'private_trait_property',                  MC::class, MT::class],
		[MC::class,  'protected_trait_property',                MC::class, MT::class],
		[MC::class,  'public_trait_property',                   MC::class, MT::class],
		[MC::class,  'protected_trait_trait_property',          MC::class, MTT::class],
		[MC::class,  'public_trait_trait_property',             MC::class, MTT::class]
	]])]
	#[TestWith([3, MC::class, Reflection::T_INHERIT, [
		[MC::class,  'private_class_property',                  MC::class, MC::class],
		[MC::class,  'private_trait_trait_property',            MC::class, MC::class],
		[MC::class,  'protected_class_property',                MC::class, MC::class],
		[MC::class,  'public_class_property',                   MC::class, MC::class],
		[MC::class,  'public_parent_overridden_property',       MC::class, MC::class],
		[MC::class,  'public_parent_trait_overridden_property', MC::class, MC::class],
		[MC::class,  'public_trait_overridden_property',        MC::class, MC::class],
		[MC::class,  'public_trait_trait_overridden_property',  MC::class, MC::class],
		[MP::class,  'protected_parent_property',               MC::class, MP::class],
		[MP::class,  'public_parent_property',                  MC::class, MP::class],
		[MP::class,  'protected_parent_trait_property',         MC::class, MPT::class],
		[MP::class,  'public_parent_trait_property',            MC::class, MPT::class],
		[MC::class,  'private_trait_property',                  MC::class, MT::class],
		[MC::class,  'protected_trait_property',                MC::class, MT::class],
		[MC::class,  'public_trait_property',                   MC::class, MT::class],
		[MC::class,  'protected_trait_trait_property',          MC::class, MTT::class],
		[MC::class,  'public_trait_trait_property',             MC::class, MTT::class]
	]])]
	#[TestWith([4, MC::class, null, [
		[MC::class,  'private_class_property',                  MC::class, MC::class],
		[MC::class,  'private_trait_trait_property',            MC::class, MC::class],
		[MC::class,  'protected_class_property',                MC::class, MC::class],
		[MC::class,  'public_class_property',                   MC::class, MC::class],
		[MC::class,  'public_parent_overridden_property',       MC::class, MC::class],
		[MC::class,  'public_parent_trait_overridden_property', MC::class, MC::class],
		[MC::class,  'public_trait_overridden_property',        MC::class, MC::class],
		[MC::class,  'public_trait_trait_overridden_property',  MC::class, MC::class],
		[MP::class,  'protected_parent_property',               MC::class, MP::class],
		[MP::class,  'public_parent_property',                  MC::class, MP::class],
		[MP::class,  'protected_parent_trait_property',         MC::class, MPT::class],
		[MP::class,  'public_parent_trait_property',            MC::class, MPT::class],
		[MC::class,  'private_trait_property',                  MC::class, MT::class],
		[MC::class,  'protected_trait_property',                MC::class, MT::class],
		[MC::class,  'public_trait_property',                   MC::class, MT::class],
		[MC::class,  'protected_trait_trait_property',          MC::class, MTT::class],
		[MC::class,  'public_trait_trait_property',             MC::class, MTT::class]
	]])]
	#[TestWith([5, A::class, Reflection_Property::IS_PRIVATE, []])]
	public function testGetProperties(int $key, string $class_name, ?int $filter, array $expected)
		: void
	{
		$actual = [];
		foreach ((new Reflection_Class($class_name))->getProperties($filter) as $property) {
			$actual[] = join(' :: ', [
				$property->getDeclaringClassName(),
				$property->getName(),
				$property->getFinalClassName(),
				$property->getDeclaringClassName(true)
			]);
		}
		foreach ($expected as &$line) {
			$line = join(' :: ', $line);
		}
		self::assertEquals($expected, $actual, "Data set #$key");
	}

	//------------------------------------------------------------------------------- testGetProperty
	public function testGetProperty() : void
	{
		$native_class     = new ReflectionClass(MC::class);
		$reflection_class = new Reflection_Class(MC::class);
		/** @noinspection PhpUnhandledExceptionInspection property exists */
		self::assertEquals(
			array_merge(
				get_object_vars($native_class->getProperty('public_class_property')),
				['final_class' => MC::class]
			),
			get_object_vars($reflection_class->getProperty('public_class_property'))
		);
		$this->getUndefinedClassComponent(
			$native_class, $reflection_class, 'getProperty', 'does_not_exist'
		);
	}

	//--------------------------------------------------------------------------------- testGetTokens
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string $class_name
	 */
	#[TestWith([MC::class, false])]
	#[TestWith([ReflectionClass::class, true])]
	public function testGetTokens(string $class_name, bool $expected_empty) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid class */
		$expected = $expected_empty
			? []
			: token_get_all(strval(file_get_contents(strval(
				(new ReflectionClass($class_name))->getFileName()
			))));
		/** @noinspection PhpUnhandledExceptionInspection Valid class */
		self::assertEquals($expected, (new Reflection_Class($class_name))->getTokens(), $class_name);
		/** @noinspection PhpUnhandledExceptionInspection Valid class */
		self::assertEquals(
			$expected, (new Reflection_Class($class_name))->getTokens(), $class_name . ' cache read'
		);
	}

	//-------------------------------------------------------------------------- testGetTraitAndNames
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string                 $class_name
	 * @param int-mask-of<Reflection::T_*> $filter
	 * @param list<class-string>           $expected
	 */
	#[TestWith([0, C::class, Reflection::T_LOCAL, [T::class, TO::class ]])]
	#[TestWith([1, C::class, Reflection::T_EXTENDS, [T::class, TO::class, PT::class]])]
	#[TestWith([2, C::class, Reflection::T_EXTENDS | Reflection::T_USE, [T::class, TO::class, TT::class, PT::class]])]
	public function testGetTraitAndNames(int $key, string $class_name, int $filter, array $expected)
		: void
	{
		/** @noinspection PhpUnhandledExceptionInspection Always valid */
		$class = new Reflection_Class($class_name);
		self::assertEquals($expected, $class->getTraitNames($filter), "data set #$key names");
		self::assertEquals(
			array_combine($expected, $expected),
			array_map(
				function(Reflection_Class $trait) { return $trait->name; },
				$class->getTraits($filter)
			),
			"data set #$key traits"
		);
	}

	//--------------------------------------------------------------------------------------- testIsA
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string                 $class_name
	 * @param int-mask-of<Reflection::T_*> $filter
	 */
	#[TestWith([0,  P::class,  Reflection::T_LOCAL,      false])]
	#[TestWith([1,  P::class,  Reflection::T_EXTENDS,    true])]
	#[TestWith([2,  P::class,  Reflection::T_IMPLEMENTS, false])]
	#[TestWith([3,  P::class,  Reflection::T_USE,        false])]
	#[TestWith([4,  P::class,  Reflection::T_INHERIT,    true])]
	#[TestWith([5,  R::class,  Reflection::T_LOCAL,      false])]
	#[TestWith([6,  R::class,  Reflection::T_EXTENDS,    true])]
	#[TestWith([7,  R::class,  Reflection::T_EXTENDS,    true])]
	#[TestWith([8,  T::class,  Reflection::T_LOCAL,      false])]
	#[TestWith([9,  T::class,  Reflection::T_EXTENDS,    false])]
	#[TestWith([10, T::class,  Reflection::T_IMPLEMENTS, false])]
	#[TestWith([11, T::class,  Reflection::T_USE,        true])]
	#[TestWith([12, T::class,  Reflection::T_INHERIT,    true])]
	#[TestWith([13, TT::class, Reflection::T_LOCAL,      false])]
	#[TestWith([14, TT::class, Reflection::T_EXTENDS,    false])]
	#[TestWith([15, TT::class, Reflection::T_IMPLEMENTS, false])]
	#[TestWith([16, TT::class, Reflection::T_USE,        true])]
	#[TestWith([17, TT::class, Reflection::T_INHERIT,    true])]
	#[TestWith([18, PT::class, Reflection::T_LOCAL,      false])]
	#[TestWith([19, PT::class, Reflection::T_EXTENDS,    false])]
	#[TestWith([20, PT::class, Reflection::T_IMPLEMENTS, false])]
	#[TestWith([21, PT::class, Reflection::T_USE,        false])]
	#[TestWith([22, PT::class, Reflection::T_EXTENDS | Reflection::T_USE, true])]
	#[TestWith([23, PT::class, Reflection::T_INHERIT,    true])]
	#[TestWith([24, I::class,  Reflection::T_LOCAL,      false])]
	#[TestWith([25, I::class,  Reflection::T_EXTENDS,    false])]
	#[TestWith([26, I::class,  Reflection::T_IMPLEMENTS, true])]
	#[TestWith([27, I::class,  Reflection::T_USE,        false])]
	#[TestWith([28, I::class,  Reflection::T_INHERIT,    true])]
	#[TestWith([29, II::class, Reflection::T_LOCAL,      false])]
	#[TestWith([30, II::class, Reflection::T_EXTENDS,    false])]
	#[TestWith([31, II::class, Reflection::T_IMPLEMENTS, true])]
	#[TestWith([32, II::class, Reflection::T_USE,        false])]
	#[TestWith([33, II::class, Reflection::T_INHERIT,    true])]
	#[TestWith([34, PI::class, Reflection::T_LOCAL,      false])]
	#[TestWith([35, PI::class, Reflection::T_EXTENDS,    false])]
	#[TestWith([36, PI::class, Reflection::T_IMPLEMENTS, false])]
	#[TestWith([37, PI::class, Reflection::T_USE,        false])]
	#[TestWith([38, PI::class, Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS, true])]
	#[TestWith([39, PI::class, Reflection::T_INHERIT,    true])]
	#[TestWith([40, C::class,  Reflection::T_LOCAL,      true])]
	public function testIsA(int $key, string $class_name, int $filter, bool $expected) : void
	{
		$class = new Reflection_Class(C::class);
		self::assertEquals($expected, $class->isA($class_name, $filter), "dataset #$key");
	}

	//-------------------------------------------------------------------------------- testIsAbstract
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string $class_name
	 */
	#[TestWith([0, A::class,  false, true])]
	#[TestWith([1, A::class,  true,  true])]
	#[TestWith([2, C::class,  false, false])]
	#[TestWith([3, C::class,  true,  false])]
	#[TestWith([4, T::class,  false, false])]
	#[TestWith([5, T::class,  true,  true])]
	#[TestWith([6, I::class,  false, true])]
	#[TestWith([7, I::class,  true,  true])]
	#[TestWith([8, II::class, false, false])]
	#[TestWith([9, II::class, true,  true])]
	public function testIsAbstract(
		int $key, string $class_name, bool $interface_trait_is_abstract, bool $expected
	) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid class name */
		$class = new Reflection_Class($class_name);
		self::assertEquals(
			$expected, $class->isAbstract($interface_trait_is_abstract), "dataset #$key"
		);
	}

	//----------------------------------------------------------------------------------- testIsClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param class-string $class_name
	 */
	#[TestWith([A::class, true])]
	#[TestWith([C::class, true])]
	#[TestWith([T::class, false])]
	#[TestWith([I::class, false])]
	public function testIsClass(string $class_name, bool $expected) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid class name */
		$class = new Reflection_Class($class_name);
		self::assertEquals($expected, $class->isClass(), $class_name);
	}

	//---------------------------------------------------------------------------------------- testOf
	public function testOf() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection Valid class */
		$class = Reflection_Class::of(C::class);
		self::assertInstanceOf(Reflection_Class::class, $class, 'instance');
		self::assertEquals(C::class, $class->name, 'name');
	}

}
