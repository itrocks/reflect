<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Interfaces\Reflection;
use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Tests\Data\A;
use ITRocks\Reflect\Tests\Data\C;
use ITRocks\Reflect\Tests\Data\I;
use ITRocks\Reflect\Tests\Data\O;
use ITRocks\Reflect\Tests\Data\OI;
use ITRocks\Reflect\Tests\Data\OT;
use ITRocks\Reflect\Tests\Data\P;
use ITRocks\Reflect\Tests\Data\PI;
use ITRocks\Reflect\Tests\Data\PT;
use ITRocks\Reflect\Tests\Data\R;
use ITRocks\Reflect\Tests\Data\T;
use ITRocks\Reflect\Tests\Data\TT;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

class Reflection_Method_Test extends TestCase
{

	//------------------------------------------------------------------------------------- testClass
	/**
	 * @param array{class-string,string} $callable
	 * @param class-string               $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0, [A::class, 'publicInterfaceMethod'],  I::class])]
	#[TestWith([1, [C::class, 'publicClassMethod'],      C::class])]
	#[TestWith([2, [C::class, 'publicInterfaceMethod'],  C::class])]
	#[TestWith([3, [C::class, 'publicParentMethod'],     P::class])]
	#[TestWith([4, [C::class, 'publicTraitMethod'],      C::class])]
	#[TestWith([4, [C::class, 'publicTraitTraitMethod'], C::class])]
	public function testClass(int $key, array $callable, string $expected) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $reflection_method->class, "data set #$key");
	}

	//------------------------------------------------------------------------------- testConstructor
	/**
	 * @param array{class-string|object,string}|string $arguments
	 * @param array{class-string,string} $callable
	 * @throws ReflectionException
	 */
	#[TestWith([0, C::class . '::publicClassMethod', [C::class, 'publicClassMethod']])]
	#[TestWith([1, [C::class, 'publicClassMethod'], [C::class, 'publicClassMethod']])]
	#[TestWith([2, [new C, 'publicClassMethod'], [C::class, 'publicClassMethod']])]
	public function testConstructor(int $key, array|string $arguments, array $callable) : void
	{
		$reflection_method = is_array($arguments)
			? new Reflection_Method(reset($arguments), end($arguments))
			: new Reflection_Method($arguments);
		self::assertEquals(reset($callable), $reflection_method->class, "data set #$key class");
		self::assertEquals(end($callable), $reflection_method->name, "data set #$key name");
	}

	//--------------------------------------------------------------------------- testForceFinalClass
	public function testForceFinalClass() : void
	{
		$reflection_method = new Reflection_Method(A::class, 'publicInterfaceMethod');
		$reflection_method->forceFinalClass(static::class);
		self::assertEquals(static::class, $reflection_method->getFinalClass()->name);
	}

	//------------------------------------------------------------------ testGetDeclaringClassAndName
	/**
	 * @param array{class-string,string} $callable
	 * @param class-string               $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0,  [A::class, 'publicInterfaceMethod'],              I::class])]
	#[TestWith([1,  [C::class, 'publicClassMethod'],                  C::class])]
	#[TestWith([2,  [C::class, 'publicInterfaceMethod'],              C::class])]
	#[TestWith([3,  [C::class, 'publicParentInterfaceMethod'],        P::class])]
	#[TestWith([4,  [C::class, 'publicParentMethod'],                 P::class])]
	#[TestWith([5,  [C::class, 'publicParentTraitMethod'],            P::class])]
	#[TestWith([6,  [C::class, 'publicParentTraitOverriddenMethod'],  C::class])]
	#[TestWith([7,  [C::class, 'publicRenamedTraitOverriddenMethod'], C::class])]
	#[TestWith([8,  [C::class, 'publicRootMethod'],                   R::class])]
	#[TestWith([9,  [C::class, 'publicTraitMethod'],                  C::class])]
	#[TestWith([10, [C::class, 'publicTraitTraitMethod'],             C::class])]
	#[TestWith([11, [C::class, 'publicTraitOverriddenMethod'],        C::class])]
	#[TestWith([12, [O::class, 'publicInterfaceMethod'],              O::class])]
	#[TestWith([13, [O::class, 'publicParentInterfaceMethod'],        O::class])]
	#[TestWith([14, [O::class, 'publicTraitMethod'],                  O::class])]
	#[TestWith([15, [O::class, 'publicTraitTraitMethod'],             O::class])]
	#[TestWith([16, [O::class, 'publicTraitInterfaceMethod'],         O::class])]
	#[TestWith([17, [O::class, 'publicTraitOverriddenMethod'],        O::class])]
	public function testGetDeclaringClassAndName(int $key, array $callable, string $expected) : void
	{
		$native_reflection = new ReflectionMethod(reset($callable), end($callable));
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals(
			$native_reflection->getDeclaringClass()->name, $expected, "data set #$key native"
		);
		self::assertEquals(
			$expected, $reflection_method->getDeclaringClassName(), "data set #$key name"
		);
		self::assertEquals(
			$expected, $reflection_method->getDeclaringClass()->name, "data set #$key class"
		);
	}

	//------------------------------------------------------------------ testGetDeclaringTraitAndName
	/**
	 * @param array{class-string,string} $callable
	 * @param class-string               $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0,  [A::class, 'publicInterfaceMethod'],              I::class])]
	#[TestWith([1,  [C::class, 'publicClassMethod'],                  C::class])]
	#[TestWith([2,  [C::class, 'publicInterfaceMethod'],              C::class])]
	#[TestWith([3,  [C::class, 'publicParentInterfaceMethod'],        P::class])]
	#[TestWith([4,  [C::class, 'publicParentMethod'],                 P::class])]
	#[TestWith([5,  [C::class, 'publicParentTraitMethod'],            PT::class])]
	#[TestWith([6,  [C::class, 'publicParentTraitOverriddenMethod'],  C::class])]
	#[TestWith([7,  [C::class, 'publicRenamedTraitOverriddenMethod'], T::class])]
	#[TestWith([8,  [C::class, 'publicRootMethod'],                   R::class])]
	#[TestWith([9,  [C::class, 'publicTraitMethod'],                  T::class])]
	#[TestWith([10, [C::class, 'publicTraitTraitMethod'],             TT::class])]
	#[TestWith([11, [C::class, 'publicTraitOverriddenMethod'],        C::class])]
	#[TestWith([12, [O::class, 'publicInterfaceMethod'],              O::class])]
	#[TestWith([13, [O::class, 'publicParentInterfaceMethod'],        O::class])]
	#[TestWith([14, [O::class, 'publicTraitMethod'],                  T::class])]
	#[TestWith([15, [C::class, 'publicTraitTraitMethod'],             TT::class])]
	#[TestWith([16, [O::class, 'publicTraitInterfaceMethod'],         OT::class])]
	#[TestWith([17, [O::class, 'publicTraitOverriddenMethod'],        T::class])]
	public function testGetDeclaringTraitAndName(int $key, array $callable, string $expected) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals(
			$expected, $reflection_method->getDeclaringTraitName(), "data set #$key name"
		);
		self::assertEquals(
			$expected, $reflection_method->getDeclaringTrait()->name, "data set #$key trait"
		);
	}

	//----------------------------------------------------------------------------- testGetDocComment
	/**
	 * @param array{class-string,string} $callable
	 * @param int<0,max>                 $filter
	 * @throws ReflectionException
	 */
	#[TestWith([0,  [A::class, 'publicInterfaceMethod'], "/** I::publicInterfaceMethod */", 0])]
	#[TestWith([1,  [A::class, 'publicInterfaceMethod'], "/** I::publicInterfaceMethod */", Reflection::T_INHERIT])]
	#[TestWith([2,  [C::class, 'publicParentTraitOverriddenMethod'], "/** C::publicParentTraitOverriddenMethod */", 0])]
	#[TestWith([3,  [C::class, 'publicParentTraitOverriddenMethod'], "/** C::publicParentTraitOverriddenMethod */", Reflection::T_EXTENDS])]
	#[TestWith([4,  [C::class, 'publicParentTraitOverriddenMethod'], "/** C::publicParentTraitOverriddenMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([5,  [C::class, 'publicParentTraitOverriddenMethod'], "/** C::publicParentTraitOverriddenMethod */", Reflection::T_USE])]
	#[TestWith([6,  [C::class, 'publicParentTraitOverriddenMethod'], "/** C::publicParentTraitOverriddenMethod */\n/** PT::publicParentTraitOverriddenMethod */", Reflection::T_INHERIT])]
	#[TestWith([7,  [C::class, 'publicRenamedTraitOverriddenMethod'], "/** T::publicTraitOverriddenMethod */", 0])]
	#[TestWith([8,  [C::class, 'publicRenamedTraitOverriddenMethod'], "/** T::publicTraitOverriddenMethod */", Reflection::T_INHERIT])]
	#[TestWith([9,  [C::class, 'publicTraitOverriddenMethod'], "/** C::publicTraitOverriddenMethod */", 0])]
	#[TestWith([10, [C::class, 'publicTraitOverriddenMethod'], "/** C::publicTraitOverriddenMethod */", Reflection::T_INHERIT])]
	#[TestWith([11, [C::class, 'publicTraitTraitMethod'], "/** TT::publicTraitTraitMethod */", 0])]
	#[TestWith([12, [C::class, 'publicTraitTraitMethod'], "/** TT::publicTraitTraitMethod */", Reflection::T_USE])]
	#[TestWith([13, [O::class, 'publicInterfaceMethod'], "/** O::publicInterfaceMethod */", 0])]
	#[TestWith([14, [O::class, 'publicInterfaceMethod'], "/** O::publicInterfaceMethod */\n/** I::publicInterfaceMethod */", Reflection::T_EXTENDS])]
	#[TestWith([15, [O::class, 'publicInterfaceMethod'], "/** O::publicInterfaceMethod */\n/** I::publicInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([16, [O::class, 'publicInterfaceMethod'], "/** O::publicInterfaceMethod */", Reflection::T_USE])]
	#[TestWith([17, [O::class, 'publicInterfaceMethod'], "/** O::publicInterfaceMethod */\n/** I::publicInterfaceMethod */", Reflection::T_INHERIT])]
	#[TestWith([18, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */", 0])]
	#[TestWith([19, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */\n/** P::publicParentInterfaceMethod */\n/** PI::publicParentInterfaceMethod */", Reflection::T_EXTENDS])]
	#[TestWith([20, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([21, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */", Reflection::T_USE])]
	#[TestWith([22, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */\n/** P::publicParentInterfaceMethod */\n/** PI::publicParentInterfaceMethod */", Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS])]
	#[TestWith([23, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */\n/** P::publicParentInterfaceMethod */\n/** PI::publicParentInterfaceMethod */", Reflection::T_INHERIT])]
	#[TestWith([24, [O::class, 'publicTraitInterfaceMethod'], "/** OT::publicTraitInterfaceMethod */", 0])]
	#[TestWith([25, [O::class, 'publicTraitInterfaceMethod'], "/** OT::publicTraitInterfaceMethod */\n/** OI::publicTraitInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([26, [O::class, 'withoutDocComment'], false, Reflection::T_INHERIT])]
	public function testGetDocComment(int $key, array $callable, string|false $expected, int $filter)
		: void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		$actual = $reflection_method->getDocComment($filter, false);
		self::assertEquals($expected, $actual, "data set #$key");
		$actual = $reflection_method->getDocComment($filter);
		self::assertEquals($expected, $actual, "data set #$key cache read");
		$actual = $reflection_method->getDocComment($filter);
		self::assertEquals($expected, $actual, "data set #$key cache write");
	}

	//----------------------------------------------------------------------- testGetDocCommentLocate
	/**
	 * @param array{class-string,string} $callable
	 * @param int<0,max>                 $filter
	 * @throws ReflectionException
	 */
	#[TestWith([0,  [A::class, 'publicInterfaceMethod'], "/** FROM " . I::class . " */\n/** I::publicInterfaceMethod */", 0])]
	#[TestWith([1,  [A::class, 'publicInterfaceMethod'], "/** FROM " . I::class . " */\n/** I::publicInterfaceMethod */", Reflection::T_INHERIT])]
	#[TestWith([2,  [C::class, 'publicRenamedTraitOverriddenMethod'], "/** FROM " . T::class . " */\n/** T::publicTraitOverriddenMethod */", 0])]
	#[TestWith([3,  [C::class, 'publicRenamedTraitOverriddenMethod'], "/** FROM " . T::class . " */\n/** T::publicTraitOverriddenMethod */", Reflection::T_INHERIT])]
	#[TestWith([4,  [C::class, 'publicTraitOverriddenMethod'], "/** FROM " . C::class . " */\n/** C::publicTraitOverriddenMethod */", 0])]
	#[TestWith([5,  [C::class, 'publicTraitOverriddenMethod'], "/** FROM " . C::class . " */\n/** C::publicTraitOverriddenMethod */", Reflection::T_INHERIT])]
	#[TestWith([6,  [C::class, 'publicTraitTraitMethod'], "/** FROM " . TT::class . " */\n/** TT::publicTraitTraitMethod */", 0])]
	#[TestWith([7,  [C::class, 'publicTraitTraitMethod'], "/** FROM " . TT::class . " */\n/** TT::publicTraitTraitMethod */", Reflection::T_USE])]
	#[TestWith([8,  [O::class, 'publicInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicInterfaceMethod */", 0])]
	#[TestWith([9,  [O::class, 'publicInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicInterfaceMethod */\n/** FROM " . I::class . " */\n/** I::publicInterfaceMethod */", Reflection::T_EXTENDS])]
	#[TestWith([10, [O::class, 'publicInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicInterfaceMethod */\n/** FROM " . I::class . " */\n/** I::publicInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([11, [O::class, 'publicInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicInterfaceMethod */", Reflection::T_USE])]
	#[TestWith([12, [O::class, 'publicInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicInterfaceMethod */\n/** FROM " . I::class . " */\n/** I::publicInterfaceMethod */", Reflection::T_INHERIT])]
	#[TestWith([13, [O::class, 'publicParentInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicParentInterfaceMethod */", 0])]
	#[TestWith([14, [O::class, 'publicParentInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicParentInterfaceMethod */\n/** FROM " . P::class . " */\n/** P::publicParentInterfaceMethod */\n/** FROM " . PI::class . " */\n/** PI::publicParentInterfaceMethod */", Reflection::T_EXTENDS])]
	#[TestWith([15, [O::class, 'publicParentInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicParentInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([16, [O::class, 'publicParentInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicParentInterfaceMethod */", Reflection::T_USE])]
	#[TestWith([17, [O::class, 'publicParentInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicParentInterfaceMethod */\n/** FROM " . P::class . " */\n/** P::publicParentInterfaceMethod */\n/** FROM " . PI::class . " */\n/** PI::publicParentInterfaceMethod */", Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS])]
	#[TestWith([18, [O::class, 'publicParentInterfaceMethod'], "/** FROM " . O::class . " */\n/** O::publicParentInterfaceMethod */\n/** FROM " . P::class . " */\n/** P::publicParentInterfaceMethod */\n/** FROM " . PI::class . " */\n/** PI::publicParentInterfaceMethod */", Reflection::T_INHERIT])]
	#[TestWith([19, [O::class, 'publicTraitInterfaceMethod'], "/** FROM " . OT::class . " */\n/** OT::publicTraitInterfaceMethod */", 0])]
	#[TestWith([20, [O::class, 'publicTraitInterfaceMethod'], "/** FROM " . OT::class . " */\n/** OT::publicTraitInterfaceMethod */\n/** FROM " . OI::class . " */\n/** OI::publicTraitInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([21, [O::class, 'withoutDocComment'], false, Reflection::T_INHERIT])]
	public function testGetDocCommentLocate(
		int $key, array $callable, string|false $expected, int $filter
	) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		$actual = $reflection_method->getDocComment($filter, false, true);
		self::assertEquals($expected, $actual, "data set #$key");
		$actual = $reflection_method->getDocComment($filter, true, true);
		self::assertEquals($expected, $actual, "data set #$key cache write");
		$actual = $reflection_method->getDocComment($filter, true, true);
		self::assertEquals($expected, $actual, "data set #$key cache read");
	}

	//---------------------------------------------------------------------- testGetFinalClassAndName
	/**
	 * @param array{class-string,string}|string $callable
	 * @param class-string                      $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0, [A::class, 'publicInterfaceMethod'],              A::class])]
	#[TestWith([1, [C::class, 'publicClassMethod'],                  C::class])]
	#[TestWith([2, [C::class, 'publicInterfaceMethod'],              C::class])]
	#[TestWith([3, [C::class, 'publicParentMethod'],                 C::class])]
	#[TestWith([4, [C::class, 'publicParentTraitMethod'],            C::class])]
	#[TestWith([5, [C::class, 'publicRenamedTraitOverriddenMethod'], C::class])]
	#[TestWith([6, [C::class, 'publicTraitMethod'],                  C::class])]
	#[TestWith([7, [new C,    'publicTraitMethod'],                  C::class])]
	#[TestWith([8, C::class . '::publicTraitMethod',                 C::class])]
	public function testGetFinalClassAndName(int $key, array|string $callable, string $expected)
		: void
	{
		$reflection_method = is_array($callable)
			? new Reflection_Method(reset($callable), end($callable))
			: new Reflection_Method($callable);
		self::assertEquals(
			$expected, $reflection_method->getFinalClassName(), "data set #$key name"
		);
		self::assertEquals(
			$expected, $reflection_method->getFinalClass()->name, "data set #$key class"
		);
	}

	//--------------------------------------------------------------------------------- testGetParent
	/**
	 * @param array{class-string,string}  $callable
	 * @param ?array{class-string,string} $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0,  [A::class, 'publicInterfaceMethod'],              null])]
	#[TestWith([1,  [C::class, 'publicClassMethod'],                  null])]
	#[TestWith([2,  [C::class, 'publicInterfaceMethod'],              [I::class, 'publicInterfaceMethod']])]
	#[TestWith([3,  [C::class, 'publicParentInterfaceMethod'],        [PI::class, 'publicParentInterfaceMethod']])]
	#[TestWith([4,  [C::class, 'publicParentMethod'],                 null])]
	#[TestWith([5,  [C::class, 'publicParentTraitMethod'],            null])]
	#[TestWith([6,  [C::class, 'publicParentTraitOverriddenMethod'],  [P::class, 'publicParentTraitOverriddenMethod']])]
	#[TestWith([7,  [C::class, 'publicRenamedTraitOverriddenMethod'], null])]
	#[TestWith([8,  [C::class, 'publicRootMethod'],                   null])]
	#[TestWith([9,  [C::class, 'publicRootOverriddenMethod'],         [R::class, 'publicRootOverriddenMethod']])]
	#[TestWith([10, [C::class, 'publicTraitMethod'],                  null])]
	#[TestWith([11, [C::class, 'publicTraitOverriddenMethod'],        null])]
	#[TestWith([12, [C::class, 'publicTraitTraitMethod'],             null])]
	#[TestWith([13, [O::class, 'publicInterfaceMethod'],              [I::class, 'publicInterfaceMethod']])]
	#[TestWith([14, [O::class, 'publicParentInterfaceMethod'],        [P::class, 'publicParentInterfaceMethod']])]
	#[TestWith([15, [O::class, 'publicTraitMethod'],                  null])]
	#[TestWith([16, [O::class, 'publicTraitInterfaceMethod'],         [OI::class, 'publicTraitInterfaceMethod']])]
	#[TestWith([17, [O::class, 'publicTraitOverriddenMethod'],        null])]
	#[TestWith([18, [O::class, 'publicTraitTraitMethod'],             null])]
	#[TestWith([19, [P::class, 'publicParentInterfaceMethod'],        [PI::class, 'publicParentInterfaceMethod']])]
	#[TestWith([20, [PI::class, 'publicParentInterfaceMethod'],       null])]
	#[TestWith([21, [C::class, 'privateParentMethod'],                null])]
	public function testGetParent(int $key, array $callable, ?array $expected) : void
	{
		$method = new Reflection_Method(reset($callable), end($callable));
		$parent = $method->getParent();
		self::assertEquals(
			$expected ?? [], isset($parent) ? [$parent->class, $parent->name] : [], "data set #$key"
		);
	}

	//------------------------------------------------------------------------------ testGetPrototype
	/**
	 * @param array{class-string,string}  $callable
	 * @param ?array{class-string,string} $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0,  [A::class, 'publicInterfaceMethod'],              null])]
	#[TestWith([1,  [C::class, 'publicClassMethod'],                  null])]
	#[TestWith([2,  [C::class, 'publicInterfaceMethod'],              [I::class, 'publicInterfaceMethod']])]
	#[TestWith([3,  [C::class, 'publicParentInterfaceMethod'],        [PI::class, 'publicParentInterfaceMethod']])]
	#[TestWith([4,  [C::class, 'publicParentMethod'],                 null])]
	#[TestWith([5,  [C::class, 'publicParentTraitMethod'],            null])]
	#[TestWith([6,  [C::class, 'publicParentTraitOverriddenMethod'],  [P::class, 'publicParentTraitOverriddenMethod']])]
	#[TestWith([7,  [C::class, 'publicRenamedTraitOverriddenMethod'], null])]
	#[TestWith([8,  [C::class, 'publicRootMethod'],                   null])]
	#[TestWith([9,  [C::class, 'publicRootOverriddenMethod'],         [R::class, 'publicRootOverriddenMethod']])]
	#[TestWith([10, [C::class, 'publicTraitMethod'],                  null])]
	#[TestWith([11, [C::class, 'publicTraitOverriddenMethod'],        null])]
	#[TestWith([12, [C::class, 'publicTraitTraitMethod'],             null])]
	#[TestWith([13, [O::class, 'publicInterfaceMethod'],              [I::class, 'publicInterfaceMethod']])]
	#[TestWith([14, [O::class, 'publicParentInterfaceMethod'],        [PI::class, 'publicParentInterfaceMethod']])]
	#[TestWith([15, [O::class, 'publicTraitMethod'],                  null])]
	#[TestWith([16, [O::class, 'publicTraitInterfaceMethod'],         [OI::class, 'publicTraitInterfaceMethod']])]
	#[TestWith([17, [O::class, 'publicTraitOverriddenMethod'],        null])]
	#[TestWith([18, [O::class, 'publicTraitTraitMethod'],             null])]
	#[TestWith([19, [P::class, 'publicParentInterfaceMethod'],        [PI::class, 'publicParentInterfaceMethod']])]
	#[TestWith([20, [PI::class, 'publicParentInterfaceMethod'],       null])]
	public function testGetPrototype(int $key, array $callable, ?array $expected) : void
	{
		$native_method     = new ReflectionMethod(reset($callable), end($callable));
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		$native_prototype = $native_method->hasPrototype()
			? $native_method->getPrototype()
			: null;
		$reflection_prototype = $reflection_method->hasPrototype()
			? $reflection_method->getPrototype()
			: null;
		self::assertEquals(
			$expected ?? [],
			isset($native_prototype) ? [$native_prototype->class, $native_prototype->name] : [],
			"data set #$key native"
		);
		self::assertEquals(
			$expected ?? [],
			isset($reflection_prototype)
				? [$reflection_prototype->class, $reflection_prototype->name]
				: [],
			"data set #$key prototype"
		);
	}

	//------------------------------------------------------------------------ testGetPrototypeString
	/** @throws ReflectionException */
	#[TestWith([0, 'publicClassMethod', 'public function publicClassMethod() : void'])]
	#[TestWith([1, 'withParameter', 'public function withParameter(string $parameter) : string'])]
	public function testGetPrototypeString(int $key, string $method_name, string $expected) : void
	{
		$method = new Reflection_Method(C::class, $method_name);
		self::assertEquals($expected, $method->getPrototypeString(), "data set #$key");
	}

	//----------------------------------------------------------------------------- testGetReturnType
	/**
	 * @param array{class-string,string} $callable
	 * @throws ReflectionException
	 */
	#[TestWith([0, [Types::class, 'classReturnType'], Types::class])]
	#[TestWith([1, [Types::class, 'noReturnType'], ''])]
	#[TestWith([2, [Types::class, 'voidReturnType'], 'void'])]
	public function testGetReturnType(int $key, array $callable, string $expected) : void
	{
		$native_method     = new ReflectionMethod(reset($callable), end($callable));
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $native_method->getReturnType(), "data set #$key native");
		self::assertEquals($expected, $reflection_method->getReturnType(), "data set #$key type");
	}

	//------------------------------------------------------------------------------ testHasParameter
	public function testHasParameter() : void
	{
		$method = new Reflection_Method(C::class, 'withParameter');
		self::assertTrue($method->hasParameter('parameter'), 'parameter');
		self::assertFalse($method->hasParameter('no_parameter'), 'no_parameter');
	}
	
}
