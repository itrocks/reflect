<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Reflection_Method;
use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Type\Reflection_Intersection_Type;
use ITRocks\Reflect\Type\Reflection_Multiple_Type;
use ITRocks\Reflect\Type\Reflection_Named_Type;
use ITRocks\Reflect\Type\Reflection_Undefined_Type;
use ITRocks\Reflect\Type\Reflection_Union_Type;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

class Type_Test extends TestCase
{

	//-------------------------------------------------------------------------------- testAllowsNull
	/** @throws ReflectionException */
	#[TestWith([0, 'int',          false])]
	#[TestWith([1, 'int_types',    false])]
	#[TestWith([2, 'or_and',       true])]
	#[TestWith([3, 'types',        false])]
	#[TestWith([4, 'types_int',    false])]
	#[TestWith([5, 'types_null',   true])]
	#[TestWith([6, 'types_null_2', true])]
	#[TestWith([7, 'without',      true])]
	public function testAllowsNull(int $key, string $property_name, bool $expected) : void
	{
		self::assertEquals(
			$expected,
			(new Reflection_Property(Types::class, $property_name))->getType()->allowsNull(),
			$key . ' Bad value for allowsNull'
		);
	}

	//------------------------------------------------------------------------------- testGetAllTypes
	/** @throws ReflectionException */
	#[TestWith([0, 'exhaustA', 'parent,Traversable,ReflectionClass,ReflectionMethod,ReflectionProperty,self,ITRocks\\Reflect\\Tests\\Types,static,callable,array,string,int,float,bool,null'])]
	#[TestWith([1, 'exhaustB', 'ITRocks\\Reflect\\Tests\\Types,self,ReflectionProperty,ReflectionMethod,ReflectionClass,Traversable,parent,static,callable,array,string,int,float,true,null'])]
	public function testGetAllTypes(int $key, string $function_name, string $type_names) : void
	{
		$type = (new Reflection_Method(Types::class, $function_name))->getReturnType();
		self::assertInstanceOf(Reflection_Union_Type::class, $type, $key . ' Bad instance');
		$all_types = array_map(
			function(Reflection_Named_Type $type) { return $type->getName(); },
			$type->getAllTypes()
		);
		self::assertEquals(explode(',', $type_names), $all_types, $key . ' Bad types');
	}

	//----------------------------------------------------------------------------------- testGetName
	/** @throws ReflectionException */
	#[TestWith([0, 'int',          'int'])]
	#[TestWith([1, 'int_types',    ''])]
	#[TestWith([2, 'or_and',       ''])]
	#[TestWith([3, 'types',        Types::class])]
	#[TestWith([4, 'types_int',    ''])]
	#[TestWith([5, 'types_null',   Types::class])]
	#[TestWith([6, 'types_null_2', Types::class])]
	#[TestWith([7, 'without',      ''])]
	public function testGetName(int $key, string $property_name, string $expected) : void
	{
		$message      = join(':', func_get_args());
		$native_type  = (new ReflectionProperty(Types::class, $property_name))->getType();
		$reflect_type = (new Reflection_Property(Types::class, $property_name))->getType();
		if (is_null($native_type)) {
			self::assertEquals('', $expected, 'Null should have no name' . $message);
			return;
		}
		if (is_a($reflect_type, Reflection_Named_Type::class)) {
			self::assertEquals($expected, $reflect_type->getName(), $message . ' Bad name');
		}
		else {
			self::assertEquals('', $expected, $message . ' Should have no name');
		}
		if (
			is_a($native_type, ReflectionNamedType::class)
			&& is_a($reflect_type, Reflection_Named_Type::class)
		) {
			self::assertEquals(
				$native_type->getName(), $reflect_type->getName(), $message . ' Name do not match native'
			);
		}
	}

	//---------------------------------------------------------------------------------- testIdentity
	/** @throws ReflectionException */
	#[TestWith([0, 'int',          ReflectionNamedType::class, Reflection_Named_Type::class])]
	#[TestWith([1, 'int_types',    ReflectionUnionType::class, Reflection_Union_Type::class])]
	#[TestWith([2, 'or_and',       ReflectionUnionType::class, Reflection_Union_Type::class])]
	#[TestWith([3, 'types',        ReflectionNamedType::class, Reflection_Named_Type::class])]
	#[TestWith([4, 'types_int',    ReflectionUnionType::class, Reflection_Union_Type::class])]
	#[TestWith([5, 'types_null',   ReflectionNamedType::class, Reflection_Named_Type::class])]
	#[TestWith([6, 'types_null_2', ReflectionNamedType::class, Reflection_Named_Type::class])]
	#[TestWith([7, 'without',      '',                         Reflection_Undefined_Type::class])]
	public function testIdentity(
		int $ley, string $property_name, string $native_class, string $reflect_class
	) : void
	{
		$message      = join(':', func_get_args());
		$native_type  = (new ReflectionProperty(Types::class,  $property_name))->getType();
		$reflect_type = (new Reflection_Property(Types::class, $property_name))->getType();
		if (is_null($native_type)) {
			self::assertInstanceOf(Reflection_Undefined_Type::class, $reflect_type, $message);
			self::assertEquals('', $native_class, $message . ' Native should be null');
			self::assertEquals(Reflection_Undefined_Type::class, $reflect_class, $message);
		}
		else {
			self::assertEquals(get_class($native_type), $native_class, $message . ' Bad native class');
			self::assertEquals(get_class($reflect_type), $reflect_class, $message . ' Bad Reflect class');
		}
		if (($property_name === 'or_and') && ($reflect_type instanceof Reflection_Union_Type)) {
			$tested = false;
			foreach ($reflect_type->getTypes() as $type) {
				if (is_a($type, Reflection_Intersection_Type::class)) {
					$tested = true;
					break;
				}
			}
			self::assertTrue($tested, $message . ' Reflection_Intersection_Type not found');
		}
	}

	//--------------------------------------------------------------------------------- testIsBuiltin
	/**
	 * @param list<bool> $expected_in_types
	 * @throws ReflectionException
	 */
	#[TestWith([0, 'int',          true])]
	#[TestWith([1, 'int_types',    null, [false, true]])]
	#[TestWith([2, 'or_and',       null, [false, false, false, false, true, true]])]
	#[TestWith([3, 'types',        false])]
	#[TestWith([4, 'types_int',    null, [false, true]])]
	#[TestWith([5, 'types_null',   false])]
	#[TestWith([6, 'types_null_2', false])]
	#[TestWith([7, 'without',      null])]
	public function testIsBuiltin(
		int $key, string $property_name, ?bool $expected, array $expected_in_types = []
	) : void
	{
		$message      = $key . ':' . $property_name . ':' . json_encode($expected);
		$native_type  = (new ReflectionProperty(Types::class,  $property_name))->getType();
		$reflect_type = (new Reflection_Property(Types::class, $property_name))->getType();
		$native_built_in = (isset($native_type) && ($native_type instanceof ReflectionNamedType))
			? $native_type->isBuiltin()
			: null;
		$reflect_built_in = ($reflect_type instanceof Reflection_Named_Type)
			? $reflect_type->isBuiltin()
			: null;
		self::assertEquals($expected, $native_built_in,  $message . ' Bad native isBuiltin() result');
		self::assertEquals($expected, $reflect_built_in, $message . ' Bad Reflect isBuiltin() result');
		if (!($reflect_type instanceof Reflection_Multiple_Type)) {
			return;
		}
		$reflect_types     = $reflect_type->getAllTypes();
		$expected_built_in = reset($expected_in_types);
		$reflect_type      = reset($reflect_types);
		while ($reflect_type !== false) {
			self::assertEquals(
				$expected_built_in,
				$reflect_type->isBuiltin(),
				$message . ' Bad type ' . key($reflect_types) . ':' . json_encode($reflect_type)
			);
			$expected_built_in = next($expected_in_types);
			$reflect_type      = next($reflect_types);
		}
	}

	//---------------------------------------------------------------------------------- testToString
	/** @throws ReflectionException */
	#[TestWith([0, 'classReturnType', Types::class])]
	#[TestWith([1, 'exhaustA', 'parent|Traversable|(ReflectionClass&ReflectionMethod)|ReflectionProperty|self|ITRocks\\Reflect\\Tests\\Types|static|callable|array|string|int|float|bool|null'])]
	#[TestWith([2, 'exhaustB', 'ITRocks\\Reflect\\Tests\\Types|self|ReflectionProperty|(ReflectionMethod&ReflectionClass)|Traversable|parent|static|callable|array|string|int|float|true|null'])]
	public function testToString(int $key, string $method_name, string $expected) : void
	{
		$type = (new Reflection_Method(Types::class, $method_name))->getReturnType();
		self::assertEquals($expected, strval($type), "data set #$key");
	}

}
