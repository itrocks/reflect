<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Tests\Data\A;
use ITRocks\Reflect\Tests\Data\C;
use ITRocks\Reflect\Tests\Data\I;
use ITRocks\Reflect\Tests\Data\P;
use ITRocks\Reflect\Tests\Data\T;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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

	//------------------------------------------------------------------------ testDeclaringClassName
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
	public function testDeclaringClassName(int $key, array $callable, string $expected) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $reflection_method->getDeclaringClassName(), "data set $key");
	}

	//------------------------------------------------------------------------ testDeclaringTraitName
	/**
	 * @param array{class-string,string} $callable
	 * @param class-string               $expected
	 * @throws ReflectionException
	 */
	#[TestWith([0, [C::class, 'publicClassMethod'],     C::class])]
	#[TestWith([1, [C::class, 'publicInterfaceMethod'], C::class])]
	#[TestWith([2, [C::class, 'publicParentMethod'],    P::class])]
	#[TestWith([3, [C::class, 'publicTraitMethod'],     T::class])]
	#[TestWith([4, [A::class, 'publicInterfaceMethod'], I::class])]
	public function testDeclaringTraitName(int $key, array $callable, string $expected) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $reflection_method->getDeclaringTraitName(), "data set $key");
	}

	//------------------------------------------------------------------------- testGetFinalClassName
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
	public function testGetFinalClassName(int $key, array $callable, string $expected) : void
	{
		$reflection_method = new Reflection_Method(reset($callable), end($callable));
		self::assertEquals($expected, $reflection_method->getFinalClassName(), "data set $key");
	}

}
