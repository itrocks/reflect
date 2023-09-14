<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Interfaces\Reflection;
use ITRocks\Reflect\Reflection_Class;
use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Tests\Data\A;
use ITRocks\Reflect\Tests\Data\C;
use ITRocks\Reflect\Tests\Data\I;
use ITRocks\Reflect\Tests\Data\II;
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
use ITRocks\Reflect\Tests\Data\TT;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class Reflection_Class_Test extends TestCase
{

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
	 * @param class-string $class_name
	 * @param int<0,max> $filter
	 * @throws ReflectionException
	 */
	#[TestWith([0,  C::class, 0, "/** C:DC */"])]
	#[TestWith([1,  C::class, Reflection::T_EXTENDS, "/** C:DC */\n/** P:DC */\n/** R:DC */"])]
	#[TestWith([2,  C::class, Reflection::T_IMPLEMENTS, "/** C:DC */\n/** I:DC */\n/** II:DC */"])]
	#[TestWith([3,  C::class, Reflection::T_USE, "/** C:DC */\n/** T:DC */\n/** TT:DC */"])]
	#[TestWith([4,  C::class, Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS, "/** C:DC */\n/** I:DC */\n/** II:DC */\n/** P:DC */\n/** PI:DC */\n/** R:DC */"])]
	#[TestWith([5,  C::class, Reflection::T_EXTENDS | Reflection::T_USE, "/** C:DC */\n/** T:DC */\n/** TT:DC */\n/** P:DC */\n/** PT:DC */\n/** R:DC */"])]
	#[TestWith([6,  C::class, Reflection::T_IMPLEMENTS | Reflection::T_USE, "/** C:DC */\n/** T:DC */\n/** TT:DC */\n/** I:DC */\n/** II:DC */"])]
	#[TestWith([7,  C::class, Reflection::T_INHERIT, "/** C:DC */\n/** T:DC */\n/** TT:DC */\n/** I:DC */\n/** II:DC */\n/** P:DC */\n/** PT:DC */\n/** PI:DC */\n/** R:DC */"])]
	#[TestWith([8,  I::class, 0, "/** I:DC */"])]
	#[TestWith([9,  I::class, Reflection::T_EXTENDS, "/** I:DC */"])]
	#[TestWith([10, I::class, Reflection::T_IMPLEMENTS, "/** I:DC */\n/** II:DC */"])]
	#[TestWith([11, I::class, Reflection::T_USE, "/** I:DC */"])]
	#[TestWith([12, T::class, 0, "/** T:DC */"])]
	#[TestWith([13, T::class, Reflection::T_EXTENDS, "/** T:DC */"])]
	#[TestWith([14, T::class, Reflection::T_IMPLEMENTS, "/** T:DC */"])]
	#[TestWith([15, T::class, Reflection::T_USE, "/** T:DC */\n/** TT:DC */"])]
	public function testGetDocComment(int $key, string $class_name, int $filter, string $expected)
		: void
	{
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
	 * @param class-string $class_name
	 * @param int<0,max> $filter
	 * @throws ReflectionException
	 */
	#[TestWith([0,  C::class, 0, "/** FROM " . C::class . " */\n/** C:DC */"])]
	#[TestWith([1,  C::class, Reflection::T_EXTENDS, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . P::class . " */\n/** P:DC */\n/** FROM " . R::class . " */\n/** R:DC */"])]
	#[TestWith([2,  C::class, Reflection::T_IMPLEMENTS, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */"])]
	#[TestWith([3,  C::class, Reflection::T_USE, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */"])]
	#[TestWith([4,  C::class, Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */\n/** FROM " . P::class . " */\n/** P:DC */\n/** FROM " . PI::class . " */\n/** PI:DC */\n/** FROM " . R::class . " */\n/** R:DC */"])]
	#[TestWith([5,  C::class, Reflection::T_EXTENDS | Reflection::T_USE, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */\n/** FROM " . P::class . " */\n/** P:DC */\n/** FROM " . PT::class . " */\n/** PT:DC */\n/** FROM " . R::class . " */\n/** R:DC */"])]
	#[TestWith([6,  C::class, Reflection::T_IMPLEMENTS | Reflection::T_USE, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */\n/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */"])]
	#[TestWith([7,  C::class, Reflection::T_INHERIT, "/** FROM " . C::class . " */\n/** C:DC */\n/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */\n/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */\n/** FROM " . P::class . " */\n/** P:DC */\n/** FROM " . PT::class . " */\n/** PT:DC */\n/** FROM " . PI::class . " */\n/** PI:DC */\n/** FROM " . R::class . " */\n/** R:DC */"])]
	#[TestWith([8,  I::class, 0, "/** FROM " . I::class . " */\n/** I:DC */"])]
	#[TestWith([9,  I::class, Reflection::T_EXTENDS, "/** FROM " . I::class . " */\n/** I:DC */"])]
	#[TestWith([10, I::class, Reflection::T_IMPLEMENTS, "/** FROM " . I::class . " */\n/** I:DC */\n/** FROM " . II::class . " */\n/** II:DC */"])]
	#[TestWith([11, I::class, Reflection::T_USE, "/** FROM " . I::class . " */\n/** I:DC */"])]
	#[TestWith([12, T::class, 0, "/** FROM " . T::class . " */\n/** T:DC */"])]
	#[TestWith([13, T::class, Reflection::T_EXTENDS, "/** FROM " . T::class . " */\n/** T:DC */"])]
	#[TestWith([14, T::class, Reflection::T_IMPLEMENTS, "/** FROM " . T::class . " */\n/** T:DC */"])]
	#[TestWith([15, T::class, Reflection::T_USE, "/** FROM " . T::class . " */\n/** T:DC */\n/** FROM " . TT::class . " */\n/** TT:DC */"])]
	public function testGetDocCommentLocate(
		int $key, string $class_name, int $filter, string $expected
	) : void
	{
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

	//------------------------------------------------------------------------- testGetInterfaceNames
	/**
	 * @param class-string       $class_name
	 * @param int<0,max>         $filter
	 * @param list<class-string> $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0, C::class, 0, [I::class]])]
	#[TestWith([1, C::class, Reflection::T_IMPLEMENTS, [I::class, II::class]])]
	#[TestWith([2, C::class, Reflection::T_EXTENDS, [I::class, PI::class]])]
	#[TestWith([3, C::class, Reflection::T_INHERIT, [I::class, II::class, PI::class]])]
	public function testGetInterfaceNames(int $key, string $class_name, int $filter, array $expected)
		: void
	{
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
		$expected = [I::class, II::class, PI::class];
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
		try {
			/** @noinspection PhpExpressionResultUnusedInspection For exception testing purpose */
			$native_class->getMethod('doesNotExist');
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
		$reflection_class->getMethod('doesNotExist');
	}

	//-------------------------------------------------------------------------------- testGetMethods
	/**
	 * @param class-string                                               $class_name
	 * @param int<0,max>                                                 $filter
	 * @param list<array{class-string,string,class-string,class-string}> $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0, MC::class, 0, [
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class]
	]])]
	#[TestWith([1, MC::class, Reflection::T_EXTENDS, [
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MP::class,  'protectedParentMethod',           MC::class, MP::class],
		[MP::class,  'publicParentMethod',              MC::class, MP::class]
	]])]
	#[TestWith([2, MC::class, Reflection::T_IMPLEMENTS, [
		[MC::class,  'privateAbstractTraitMethod',      MC::class, MC::class],
		[MC::class,  'privateAbstractTraitTraitMethod', MC::class, MC::class],
		[MC::class,  'privateClassMethod',              MC::class, MC::class],
		[MC::class,  'protectedClassMethod',            MC::class, MC::class],
		[MC::class,  'publicClassMethod',               MC::class, MC::class],
		[MI::class,  'interfaceMethod',                 MC::class, MI::class],
		[MII::class, 'interfaceInterfaceMethod',        MC::class, MII::class]
	]])]
	#[TestWith([3, MC::class, Reflection::T_USE, [
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
	#[TestWith([8, A::class, Reflection_Method::IS_PRIVATE, []])]
	public function testGetMethods(int $key, string $class_name, int $filter, array $expected) : void
	{
		$actual = [];
		foreach ((new Reflection_Class($class_name))->getMethods($filter) as $method) {
			$actual[] = join(' :: ', [
				$method->getDeclaringClassName(),
				$method->getName(),
				$method->getFinalClassName(),
				$method->getDeclaringTraitName()
			]);
		}
		foreach ($expected as &$line) {
			$line = join(' :: ', $line);
		}
		self::assertEquals($expected, $actual, "Data set #$key");
	}

	//--------------------------------------------------------------------------- testGetNamespaceUse
	public function testGetNamespaceUse() : void
	{
		$class    = new Reflection_Class(Namespace_Use::class);
		$expected = [
			'T'                => 'A',
			'C'                => 'C',
			'Reflection_Class' => 'ITRocks\Reflect\Reflection_Class',
			'T1'               => Parse_Test::class,
			'Types'            => Types::class
		];
		$actual = $class->getNamespaceUse();
		self::assertEquals($expected, $actual);
		$actual = $class->getNamespaceUse();
		self::assertEquals($expected, $actual, 'cached');
	}

	//--------------------------------------------------------------------- testGetParentClassAndName
	/**
	 * @param class-string $class_name
	 * @param class-string $expected
	 * @throws ReflectionException
	 */
	#[TestWith([A::class, ''])]
	#[TestWith([C::class, P::class])]
	#[TestWith([I::class, ''])]
	#[TestWith([T::class, ''])]
	public function testGetParentClassAndName(string $class_name, string $expected) : void
	{
		$class = new Reflection_Class($class_name);
		$parent_class = $class->getParentClass();
		self::assertEquals($expected, ($parent_class === false) ? '' : $parent_class->name, "$class_name class");
		self::assertEquals($expected, $class->getParentClassName(), "$class_name name");
	}

}
