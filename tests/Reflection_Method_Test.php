<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Interfaces\Reflection;
use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Tests\Data\A;
use ITRocks\Reflect\Tests\Data\C;
use ITRocks\Reflect\Tests\Data\I;
use ITRocks\Reflect\Tests\Data\O;
use ITRocks\Reflect\Tests\Data\OT;
use ITRocks\Reflect\Tests\Data\P;
use ITRocks\Reflect\Tests\Data\PT;
use ITRocks\Reflect\Tests\Data\R;
use ITRocks\Reflect\Tests\Data\T;
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
	#[TestWith([0, [C::class, 'publicClassMethod'],     C::class])]
	#[TestWith([1, [C::class, 'publicInterfaceMethod'], C::class])]
	#[TestWith([2, [C::class, 'publicParentMethod'],    P::class])]
	#[TestWith([3, [C::class, 'publicTraitMethod'],     C::class])]
	#[TestWith([4, [A::class, 'publicInterfaceMethod'], I::class])]
	public function testClass(int $key, array $callable, string $expected) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $reflection_method->class, "data set $key");
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
		self::assertEquals(reset($callable), $reflection_method->class, "data set $key class");
		self::assertEquals(end($callable), $reflection_method->name, "data set $key name");
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
	#[TestWith([0, [C::class, 'publicClassMethod'],     C::class])]
	#[TestWith([1, [C::class, 'publicInterfaceMethod'], C::class])]
	#[TestWith([2, [C::class, 'publicParentMethod'],    P::class])]
	#[TestWith([3, [C::class, 'publicTraitMethod'],     C::class])]
	#[TestWith([4, [A::class, 'publicInterfaceMethod'], I::class])]
	public function testGetDeclaringClassAndName(int $key, array $callable, string $expected) : void
	{
		$native_reflection = new ReflectionMethod(reset($callable), end($callable));
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($native_reflection->getDeclaringClass()->name, $expected, "data set $key native");
		self::assertEquals($expected, $reflection_method->getDeclaringClassName(), "data set $key name");
		self::assertEquals($expected, $reflection_method->getDeclaringClass()->name, "data set $key class");
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
	#[TestWith([7,  [C::class, 'publicRenamedTraitOverriddenMethod'], C::class])]
	#[TestWith([8,  [C::class, 'publicRootMethod'],                   R::class])]
	#[TestWith([9,  [C::class, 'publicTraitMethod'],                  T::class])]
	#[TestWith([10, [C::class, 'publicTraitOverriddenMethod'],        C::class])]
	#[TestWith([11, [O::class, 'publicInterfaceMethod'],              O::class])]
	#[TestWith([12, [O::class, 'publicParentInterfaceMethod'],        O::class])]
	#[TestWith([13, [O::class, 'publicTraitMethod'],                  T::class])]
	#[TestWith([14, [O::class, 'publicTraitOverriddenMethod'],        T::class])]
	#[TestWith([15, [O::class, 'publicTraitInterfaceMethod'],         OT::class])]
	public function testGetDeclaringTraitAndName(int $key, array $callable, string $expected) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $reflection_method->getDeclaringTraitName(), "data set $key name");
		self::assertEquals($expected, $reflection_method->getDeclaringTrait()->name, "data set $key trait");
	}

	//----------------------------------------------------------------------------- testGetDocComment
	/**
	 * @param array{class-string,string} $callable
	 * @param int<0,max>                 $filter
	 * @throws ReflectionException
	 */
	#[TestWith([0,  [A::class, 'publicInterfaceMethod'], '/** I::publicInterfaceMethod */', 0])]
	#[TestWith([1,  [A::class, 'publicInterfaceMethod'], '/** I::publicInterfaceMethod */', Reflection::T_INHERIT])]
	#[TestWith([2,  [O::class, 'publicInterfaceMethod'], '/** O::publicInterfaceMethod */', 0])]
	#[TestWith([3,  [O::class, 'publicInterfaceMethod'], "/** O::publicInterfaceMethod */\n/** I::publicInterfaceMethod */", Reflection::T_EXTENDS])]
	#[TestWith([4,  [O::class, 'publicInterfaceMethod'], "/** O::publicInterfaceMethod */\n/** I::publicInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([5,  [O::class, 'publicInterfaceMethod'], '/** O::publicInterfaceMethod */', Reflection::T_USE])]
	#[TestWith([6,  [O::class, 'publicInterfaceMethod'], "/** O::publicInterfaceMethod */\n/** I::publicInterfaceMethod */", Reflection::T_INHERIT])]
	#[TestWith([7,  [C::class, 'publicTraitOverriddenMethod'], "/** C::publicTraitOverriddenMethod */", 0])]
	#[TestWith([11, [C::class, 'publicTraitOverriddenMethod'], "/** C::publicTraitOverriddenMethod */", Reflection::T_INHERIT])]
	#[TestWith([12, [C::class, 'publicRenamedTraitOverriddenMethod'], "/** T::publicTraitOverriddenMethod */", 0])]
	#[TestWith([13, [C::class, 'publicRenamedTraitOverriddenMethod'], "/** T::publicTraitOverriddenMethod */", Reflection::T_INHERIT])]
	#[TestWith([14, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */", 0])]
	#[TestWith([15, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */\n/** P::publicParentInterfaceMethod */", Reflection::T_EXTENDS])]
	#[TestWith([16, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	#[TestWith([17, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */", Reflection::T_USE])]
	#[TestWith([18, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */\n/** P::publicParentInterfaceMethod */\n/** PI::publicParentInterfaceMethod */", Reflection::T_EXTENDS | Reflection::T_IMPLEMENTS])]
	#[TestWith([19, [O::class, 'publicParentInterfaceMethod'], "/** O::publicParentInterfaceMethod */\n/** P::publicParentInterfaceMethod */\n/** PI::publicParentInterfaceMethod */", Reflection::T_INHERIT])]
	#[TestWith([20, [O::class, 'publicTraitInterfaceMethod'], "/** OT::publicTraitInterfaceMethod */", 0])]
	#[TestWith([21, [O::class, 'publicTraitInterfaceMethod'], "/** OT::publicTraitInterfaceMethod */\n/** OI::publicTraitInterfaceMethod */", Reflection::T_IMPLEMENTS])]
	public function testGetDocComment(int $key, array $callable, string|false $expected, int $filter)
		: void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $reflection_method->getDocComment($filter, false), "data set $key");
	}

	//---------------------------------------------------------------------- testGetFinalClassAndName
	/**
	 * @param array{class-string,string} $callable
	 * @param class-string               $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0, [C::class, 'publicClassMethod'],     C::class])]
	#[TestWith([1, [C::class, 'publicInterfaceMethod'], C::class])]
	#[TestWith([2, [C::class, 'publicParentMethod'],    C::class])]
	#[TestWith([3, [C::class, 'publicTraitMethod'],     C::class])]
	#[TestWith([4, [A::class, 'publicInterfaceMethod'], A::class])]
	public function testGetFinalClassAndName(int $key, array $callable, string $expected) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $reflection_method->getFinalClassName(), "data set $key name");
		self::assertEquals($expected, $reflection_method->getFinalClass()->name, "data set $key class");
	}

}
