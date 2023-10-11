<?php
namespace ITRocks\Reflect\Tests\Type;

use ITRocks\Reflect\Reflection_Property;
use ITRocks\Reflect\Type\Interface\Multiple;
use ITRocks\Reflect\Type\PHP\Intersection;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\PHP\Union;
use ITRocks\Reflect\Type\PHPStan\Call;
use ITRocks\Reflect\Type\PHPStan\Class_Constant;
use ITRocks\Reflect\Type\PHPStan\Collection;
use ITRocks\Reflect\Type\PHPStan\Exception;
use ITRocks\Reflect\Type\PHPStan\Float_Literal;
use ITRocks\Reflect\Type\PHPStan\Int_Literal;
use ITRocks\Reflect\Type\PHPStan\Int_Mask;
use ITRocks\Reflect\Type\PHPStan\Int_Range;
use ITRocks\Reflect\Type\PHPStan\Of;
use ITRocks\Reflect\Type\PHPStan\Parameter;
use ITRocks\Reflect\Type\PHPStan\Parser;
use ITRocks\Reflect\Type\PHPStan\Shape;
use ITRocks\Reflect\Type\PHPStan\String_Literal;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;

class PHPStan_Parser_Test // phpcs:ignore
	extends TestCase
{

	//---------------------------------------------------------------------------------------- $array
	/** @var array<self> */
	protected array $array = [];

	//--------------------------------------------------------------------------------------- $parser
	protected static Parser $parser;

	//------------------------------------------------------------------------------ setUpBeforeClass
	public static function setUpBeforeClass() : void
	{
		self::$parser = new Parser(new Reflection_Property(__CLASS__, 'array'));
	}

	//-------------------------------------------------------------------------------- testAllowsNull
	/** @throws Exception */
	public function testAllowsNull() : void
	{
		$type = self::$parser->parse('int');
		static::assertInstanceOf(Named::class, $type);
		static::assertFalse($type->allowsNull());
		$type = self::$parser->parse('?int');
		static::assertInstanceOf(Named::class, $type);
		static::assertTrue($type->allowsNull());
	}

	//------------------------------------------------------ testBadNumericLiteralBadCharacterInFloat
	/** @throws Exception */
	public function testBadNumericLiteralBadCharacterInFloat() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::BAD_CHARACTER_IN_FLOAT_LITERAL);
		$this->expectExceptionMessage(
			'Bad character [*] in float literal [15*.18.] into [15*.18.] position 2'
		);
		self::$parser->parse('15*.18.');
	}

	//-------------------------------------------------------- testBadNumericLiteralBadCharacterInInt
	/** @throws Exception */
	public function testBadNumericLiteralBadCharacterInInt() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::BAD_CHARACTER_IN_INT_LITERAL);
		$this->expectExceptionMessage(
			'Bad character [*] in int literal [15**/-19] into [15**/-19] position 2'
		);
		self::$parser->parse('15**/-19');
	}

	//----------------------------------------------------- testBadNumericLiteralMissingDigitAfterDot
	/** @throws Exception */
	public function testBadNumericLiteralMissingDigitAfterDot() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::BAD_CHARACTER_IN_FLOAT_LITERAL);
		$this->expectExceptionMessage(
			'Missing digit after dot [.] in float literal [15.] into [15.] position 3'
		);
		self::$parser->parse('15.');
	}

	//-------------------------------------------------------------- testBadNumericLiteralTooManyDots
	/** @throws Exception */
	public function testBadNumericLiteralTooManyDots() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::BAD_CHARACTER_IN_FLOAT_LITERAL);
		$this->expectExceptionMessage(
			'Too many dots [.] in float literal [15..19] into [15..19] position 3'
		);
		self::$parser->parse('15..19');
	}

	//--------------------------------------------------------- testBadStringLiteralBadCharacterAfter
	public function testBadStringLiteralBadCharacterAfter() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::BAD_CHARACTER_IN_STRING_LITERAL);
		$this->expectExceptionMessage(
			'Bad character [i] after string literal [text] into ["text"int] position 6'
		);
		self::$parser->parse('"text"int');
	}

	//--------------------------------------------------- testBadStringLiteralEndsWithEscapeCharacter
	/** @throws Exception */
	public function testBadStringLiteralEndsWithEscapeCharacter() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::BAD_CHARACTER_IN_STRING_LITERAL);
		$this->expectExceptionMessage("Unterminated string literal [text\\] into ['text\\] position 6");
		self::$parser->parse("'text\\");
	}

	//---------------------------------------------------------------- testBadStringLiteralQuoteAlone
	/** @throws Exception */
	public function testBadStringLiteralQuoteAlone() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::BAD_CHARACTER_IN_STRING_LITERAL);
		$this->expectExceptionMessage("Unterminated string literal into ['] position 1");
		self::$parser->parse("'");
	}

	//-------------------------------------------------------------- testBadStringLiteralUnterminated
	/** @throws Exception */
	public function testBadStringLiteralUnterminated() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::BAD_CHARACTER_IN_STRING_LITERAL);
		$this->expectExceptionMessage("Unterminated string literal [text] into ['text] position 5");
		self::$parser->parse("'text");
	}

	//--------------------------------------------------------------------- testBottomSingleNamedType
	/** @throws Exception */
	public function testBottomSingleNamedType() : void
	{
		/** @var non-empty-list<string> $bottom_names */
		$bottom_names = (new ReflectionClassConstant(Parser::class, 'BOTTOM'))->getValue();
		/** @var non-empty-list<string> $single_names */
		$single_names = (new ReflectionClassConstant(Parser::class, 'SINGLE'))->getValue();
		foreach (array_merge($bottom_names, $single_names) as $name) {
			$type = self::$parser->parse($name);
			self::assertInstanceOf(Named::class, $type);
			self::assertEquals($name, $type->getName());
		}
	}

	//---------------------------------------------------------------------------------- testCallable
	/**
	 * @param non-negative-int $key
	 * @param class-string     $class
	 * @param list<string>     $types
	 * @throws Exception
	 */
	#[TestWith([0, 'callable',             Named::class, 'callable', []])]
	#[TestWith([1, 'callable(int)',        Call::class, 'callable', ['int']])]
	#[TestWith([2, 'Closure(int)',         Call::class, 'Closure', ['int']])]
	#[TestWith([3, '\Closure(int,string)', Call::class, '\Closure', ['int', 'string']])]
	public function testCallable(int $key, string $source, string $class, string $name, array $types)
		: void
	{
		$type = self::$parser->parse($source);
		self::assertInstanceOf($class, $type, "data set #$key");
		if ($type instanceof Named) {
			self::assertEquals($name, $type->getName(), "data set #$key");
		}
		if ($type instanceof Call) {
			$actual_types = array_map(
				fn(Parameter $parameter) => ($parameter->type instanceof Named)
					? $parameter->type->getName()
					: get_class($parameter->type),
				$type->parameters
			);
			self::assertEquals($types, $actual_types, "data set #$key");
		}
	}

	//------------------------------------------------------------------------ testCallableParameters
	/**
	 * @param non-negative-int                          $key
	 * @param list<array{string,bool,bool,string,bool}> $expect
	 * @throws Exception
	 */
	#[TestWith([0, 'callable(string $name)', [
		[Named::class, 'string', false, false, 'name', false]
	]])]
	#[TestWith([1, 'callable(float $n1, int $n2)', [
		[Named::class, 'float', false, false, 'n1', false],
		[Named::class, 'int', false, false, 'n2', false]
	]])]
	#[TestWith([2, 'callable(float, int...)', [
		[Named::class, 'float', false, false, '', false],
		[Named::class, 'int', true, false, '', false]
	]])]
	#[TestWith([3, 'callable(int &, int $n, int=)', [
		[Named::class, 'int', false, true, '', false],
		[Named::class, 'int', false, false, 'n', false],
		[Named::class, 'int', false, false, '', true]
	]])]
	#[TestWith([4, 'callable(int &$n, int $n=)', [
		[Named::class, 'int', false, true, 'n', false],
		[Named::class, 'int', false, false, 'n', true]
	]])]
	#[TestWith([5, 'callable ( callable(int $i) , int ... $n ) ', [
		[Call::class, 'callable', false, false, '', false],
		[Named::class, 'int', true, false, 'n', false]
	]])]
	public function testCallableParameters(int $key, string $source, array $expect) : void
	{
		$type = self::$parser->parse($source);
		self::assertInstanceOf(Call::class, $type, "data set #$key");
		$actual = [];
		foreach ($type->parameters as $parameter) {
			$actual[] = [
				get_class($parameter->type),
				($parameter->type instanceof Named) ? $parameter->type->getName() : '-',
				$parameter->variadic,
				$parameter->reference,
				$parameter->name,
				$parameter->optional
			];
		}
		self::assertEquals($expect, $actual, "data set #$key");
	}

	//---------------------------------------------------------------------------- testCallableReturn
	/**
	 * @param non-negative-int $key
	 * @param class-string     $class
	 * @throws Exception
	 */
	#[TestWith([0, 'callable() : void', Named::class, 'void'])]
	#[TestWith([1, 'callable(int):void', Named::class, 'void'])]
	#[TestWith([2, 'callable(int):callable(callable(float):callable):int', Call::class, 'callable'])]
	public function testCallableReturn(int $key, string $source, string $class, string $name) : void
	{
		$type = self::$parser->parse($source);
		self::assertInstanceOf(Call::class, $type, "data set #$key");
		$return = $type->return;
		self::assertInstanceOf($class, $return, "data set #$key");
		if ($return instanceof Named) {
			self::assertEquals($name, $return->getName(), "data set #$key");
		}
	}

	//----------------------------------------------------------------------------- testClassConstant
	/**
	 * @param non-negative-int $key
	 * @throws Exception
	 */
	#[TestWith([0, 'ReflectionAttribute::TARGET_CLASS'])]
	#[TestWith([1, 'ReflectionAttribute::TARGET_*'])]
	#[TestWith([2, 'ReflectionAttribute::*'])]
	public function testClassConstant(int $key, string $source) : void
	{
		$type = self::$parser->parse($source);
		[$class, $constant] = explode('::', $source, 2);
		self::assertInstanceOf(Class_Constant::class, $type, "data set #$key");
		self::assertEquals($class, $type->class, "data set #$key class");
		self::assertEquals($constant, $type->constant, "data set #$key constant");
	}

	//---------------------------------------------------------------------------- testClassNamedType
	/** @throws Exception */
	public function testClassNamedType() : void
	{
		foreach ([__CLASS__, '\\' . __CLASS__, 'PHPStan_Parser_Test'] as $name) {
			$type = self::$parser->parse($name);
			self::assertInstanceOf(Named::class, $type);
			self::assertEquals($name, $type->getName());
		}
	}

	//------------------------------------------------------------------------------- testClassString
	/** @throws Exception */
	public function testClassString() : void
	{
		$type = self::$parser->parse('class-string<\A\B\C>');
		self::assertInstanceOf(Of::class, $type);
		$type = $type->type;
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('\A\B\C', $type->getName());
	}

	//-------------------------------------------------------------------------------- testCollection
	/**
	 * @param non-negative-int $key
	 * @param class-string     $class
	 * @throws Exception
	 */
	#[TestWith([0, 'array<int>', 'array', Named::class, 'int'])]
	#[TestWith([1, 'non-empty-array<int,string>', 'non-empty-array', Named::class, 'string', 'int'])]
	#[TestWith([2, 'list<int<0,max>>', 'list', Int_Range::class, 'int<0,max>'])]
	#[TestWith([3, 'non-empty-list<Intersection|Union>', 'non-empty-list', Union::class, 'Intersection|Union'])]
	#[TestWith([4, 'iterable<Union>', 'iterable', Named::class, 'Union'])]
	public function testCollection(
		int $key, string $source, string $name, string $class, string $type_type, string $type_key = ''
	) : void
	{
		$type = self::$parser->parse($source);
		self::assertInstanceOf(Collection::class, $type, "data set #$key");
		self::assertEquals($name, $type->getName(), "data set #$key name");
		self::assertInstanceOf($class, $type->type, "data set #$key class");
		self::assertEquals($type_key,  (string)$type->key, "data set #$key key");
		self::assertEquals($type_type, (string)$type->type, "data set #$key type");
	}

	//------------------------------------------------------------------------- testConstantNamedType
	/** @throws Exception */
	public function testConstantNamedType() : void
	{
		foreach (['CONSTANT120', 'PHP_SELF'] as $name) {
			$type = self::$parser->parse($name);
			self::assertInstanceOf(Named::class, $type);
			self::assertEquals($name, $type->getName());
		}
	}

	//------------------------------------------------------------------------------ testFloatLiteral
	/** @throws Exception */
	public function testFloatLiteral() : void
	{
		foreach (['5.28', '.12', '0.12', '0018.12', '-108.3'] as $float) {
			$type = self::$parser->parse($float);
			self::assertInstanceOf(Float_Literal::class, $type);
			self::assertEquals((float)$float, $type->getValue());
		}
	}

	//-------------------------------------------------------------------------------- testIntLiteral
	/** @throws Exception */
	public function testIntLiteral() : void
	{
		foreach (['0', '128', '00128', '-1000'] as $int) {
			$type = self::$parser->parse($int);
			self::assertInstanceOf(Int_Literal::class, $type);
			self::assertEquals((int)$int, $type->getValue());
		}
	}

	//----------------------------------------------------------------------------------- testIntMask
	/**
	 * @param non-negative-int $key
	 * @param list<int|string> $expect
	 * @throws Exception
	 */
	#[TestWith([0, 'int-mask<1, 2, 4>', [1, 2, 4]])]
	public function testIntMask(int $key, string $source, array $expect) : void
	{
		$type = self::$parser->parse($source);
		self::assertInstanceOf(Int_Mask::class, $type, "data set #$key");
		self::assertEquals('int-mask', $type->getName(), "data set #$key name");
		$actual = [];
		foreach ($type->values as $value) {
			$actual[] = ($value instanceof Int_Literal) ? $value->value : strval($value);
		}
		self::assertEquals($expect, $actual, "data set #$key");
	}

	//--------------------------------------------------------------------------------- testIntMaskOf
	/**
	 * @param non-negative-int $key
	 * @param list<int|string> $expect
	 * @throws Exception
	 */
	#[TestWith([0, 'int-mask-of<1|2|4>', [1, 2, 4]])]
	#[TestWith([1,
		'int-mask-of<ReflectionAttribute::TARGET_CLASS|ReflectionAttribute::TARGET_METHOD>',
		['ReflectionAttribute::TARGET_CLASS', 'ReflectionAttribute::TARGET_METHOD']
	])]
	#[TestWith([2,
		'int-mask-of<1|ReflectionAttribute::TARGET_METHOD|4>',
		[1, 'ReflectionAttribute::TARGET_METHOD', 4]
	])]
	public function testIntMaskOf(int $key, string $source, array $expect) : void
	{
		$type = self::$parser->parse($source);
		self::assertInstanceOf(Int_Mask::class, $type, "data set #$key");
		self::assertEquals('int-mask-of', $type->getName(), "data set #$key name");
		$actual = [];
		foreach ($type->values as $value_key => $value) {
			self::assertTrue(
				($value instanceof Class_Constant) || ($value instanceof Int_Literal),
				"data set #$key value $value_key type"
			);
			$actual[] = ($value instanceof Int_Literal) ? $value->value : strval($value);
		}
		self::assertEquals($expect, $actual, "data set #$key");
	}

	//---------------------------------------------------------------------------------- testIntRange
	/**
	 * @param non-negative-int $key
	 * @param int|'min'        $min
	 * @param int|'max'        $max
	 * @throws Exception
	 */
	#[TestWith([0, 'int<-100,100>', -100,  100])]
	#[TestWith([1, 'int<min,-10>',  'min', -10])]
	#[TestWith([2, 'int<10,max>',   10,    'max'])]
	public function testIntRange(int $key, string $source, int|string $min, int|string $max) : void
	{
		$type = self::$parser->parse($source);
		static::assertInstanceOf(Int_Range::class, $type, "data set #$key");
		static::assertEquals($min, $type->min, "data set #$key min");
		static::assertEquals($max, $type->max, "data set #$key max");
	}

	//------------------------------------------------------------------------- testIntersectionUnion
	/** @throws Exception */
	public function testIntersectionUnion() : void
	{
		$type = self::$parser->parse('Intersection&Union|int');
		self::assertInstanceOf(Union::class, $type);
		$types = $type->getTypes();
		self::assertCount(2, $types);
		$type = end($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('int', $type->getName());
		$types = prev($types);
		self::assertInstanceOf(Intersection::class, $types);
		$types = $types->getTypes();
		self::assertCount(2, $types);
		$type = reset($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Intersection', $type);
		$type = next($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Union', $type);
	}

	//------------------------------------------------------------------------------- testMissingType
	public function testMissingType() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::MISSING_TYPE);
		$this->expectExceptionMessage('Missing type into [|int] position 0');
		self::$parser->parse('|int');
	}

	//---------------------------------------------------------------------------------- testMultiple
	/**
	 * @param non-negative-int           $key
	 * @param class-string               $multiple_class
	 * @param array<string,class-string> $expect
	 * @throws Exception
	 */
	#[TestWith([
		0, 'int|string', Union::class, ['int' => Named::class, 'string' => Named::class]
	])]
	#[TestWith([
		1, 'int|string|A\C', Union::class,
		['int' => Named::class, 'string' => Named::class, 'A\C' => Named::class]
	])]
	#[TestWith([
		2, 'int&string', Intersection::class, ['int' => Named::class, 'string' => Named::class]
	])]
	#[TestWith([
		3, 'int&string&A\C', Intersection::class,
		['int' => Named::class, 'string' => Named::class, 'A\C' => Named::class]
	])]
	public function testMultiple(int $key, string $source, string $multiple_class, array $expect)
		: void
	{
		$type = self::$parser->parse($source);
		static::assertInstanceOf($multiple_class, $type, "data set #$key");
		/** @var Intersection|Union $type */
		reset($expect);
		foreach ($type->getTypes() as $type_key => $sub_type) {
			static::assertIsString(current($expect), "data set #$key type $type_key");
			static::assertInstanceOf(current($expect), $sub_type, "data set #$key type $type_key");
			if ($sub_type instanceof Named) {
				static::assertEquals(key($expect), $sub_type->getName(), "data set #$key type $type_key");
			}
			next($expect);
		}
	}

	//---------------------------------------------------------------------------------------- testOf
	/**
	 * @param non-negative-int $key
	 * @param class-string     $class
	 * @throws Exception
	 */
	#[TestWith([0, 'key-of<Parser::SEPARATORS>', 'key-of', Class_Constant::class, 'Parser::SEPARATORS'])]
	#[TestWith([1, 'value-of<Parser::SEPARATORS>', 'value-of', Class_Constant::class, 'Parser::SEPARATORS'])]
	#[TestWith([2, 'value-of<Enumerable>', 'value-of', Named::class, 'Enumerable'])]
	public function testOf(
		int $key, string $source, string $name, string $class, string $type_type
	) : void
	{
		$type = self::$parser->parse($source);
		self::assertInstanceOf(Of::class, $type, "data set #$key");
		self::assertEquals($name, $type->getName(), "data set #$key name");
		self::assertInstanceOf($class, $type->type, "data set #$key class");
		self::assertEquals($type_type, (string)$type->type, "data set #$key type");
	}

	//------------------------------------------------------------------------------- testParentheses
	/**
	 * @param non-negative-int                                                 $key
	 * @param list<list<list<list<list<string>|string>|string>|string>|string> $expect
	 * @throws Exception
	 */
	#[TestWith([0, 'Intersection&(Union|Reflection)',
		[Intersection::class, 'Intersection', [Union::class, 'Union', 'Reflection']]
	])]
	#[TestWith([1, 'Intersection&(Union&Reflection)',
		[Intersection::class, 'Intersection', [Intersection::class, 'Union', 'Reflection']]
	])]
	#[TestWith([1, 'Intersection&(Union|(Multiple&(Closure(int):void|Reflection)))',
		[Intersection::class, 'Intersection', [Union::class, 'Union', [Intersection::class, 'Multiple', [Union::class, 'Closure', 'Reflection']]]]
	])]
	public function testParentheses(int $key, string $source, array $expect) : void
	{
		$type = self::$parser->parse($source);
		static::assertInstanceOf(Multiple::class, $type, "data set #$key");
		$operand = 0;
		$tidy = function(Multiple $multiple) use($key, &$operand, &$tidy) : array {
			$result = [];
			foreach ($multiple->getTypes() as $type) {
				$operand ++;
				static::assertTrue(
					($type instanceof Named) || ($type instanceof Multiple), "data set #$key operand $operand"
				);
				if ($type instanceof Named) {
					$result[] = $type->getName();
				}
				if ($type instanceof Multiple) {
					$result[] = array_merge([get_class($type)], $tidy($type));
				}
			}
			return $result;
		};
		$actual = array_merge([get_class($type)], $tidy($type));
		static::assertEquals($expect, $actual, "data set #$key");
	}

	//------------------------------------------------------------------------------------- testShape
	/**
	 * @param array<string> $values
	 * @throws Exception
	 */
	#[TestWith([0, '\A\B<float,int,string>',   '\A\B',   ['float', 'int', 'string']])]
	#[TestWith([1, 'array{float,int,string}',  'array',  ['float', 'int', 'string']])]
	#[TestWith([2, 'array{float,b:int}',       'array',  ['float', 'b' => 'int']])]
	#[TestWith([3, 'array{0:float,b:int}',     'array',  ['float', 'b' => 'int']])]
	#[TestWith([4, 'array{"a":float,"b":int}', 'array',  ['a' => 'float', 'b' => 'int']])]
	#[TestWith([5, 'object{a:float,b:int}',    'object', ['a' => 'float', 'b' => 'int']])]
	#[TestWith([6, 'object{"a":float,b:int}',  'object', ['a' => 'float', 'b' => 'int']])]
	public function testShape(int $key, string $source, string $name, array $values)
		: void
	{
		$type = self::$parser->parse($source);
		self::assertInstanceOf(Shape::class, $type, "data set #$key");
		self::assertEquals($name, $type->getName(), "data set #$key");
		foreach ($values as $value_key => $value) {
			self::assertNotNull($type->types[$value_key] ?? null, "data set #$key key");
			self::assertEquals($value, $type->types[$value_key], "data set #$key value");
		}
	}

	//------------------------------------------------------------------------ testSquareBracketArray
	/** @throws Exception */
	public function testSquareBracketArray() : void
	{
		$type = self::$parser->parse(self::class . '[]');
		self::assertInstanceOf(Collection::class, $type);
		self::assertEquals(1, $type->getDimensions());
		$element = $type->getElementType();
		self::assertInstanceOf(Named::class, $element);
		self::assertEquals(self::class, $element->getName());

		$type = self::$parser->parse('non-negative-int[][]');
		self::assertInstanceOf(Collection::class, $type);
		self::assertEquals(2, $type->getDimensions());
		$element = $type->getElementType();
		self::assertInstanceOf(Named::class, $element);
		self::assertEquals('non-negative-int', $element->getName());

		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::UNKNOWN_TYPE);
		$this->expectExceptionMessage('Unknown type [bad-type] into [bad-type[][][]] position 0');
		self::$parser->parse('bad-type[][][]');
	}

	//----------------------------------------------------------------------------- testStringLiteral
	/** @throws Exception */
	public function testStringLiteral() : void
	{
		foreach (['"simple"', "'single-quote'", '"with\\"escape"', '"int<0,max>"'] as $string) {
			$type = self::$parser->parse($string);
			self::assertInstanceOf(String_Literal::class, $type);
			self::assertEquals(stripcslashes(substr($string, 1, -1)), $type->getValue());
		}
	}

	//------------------------------------------------------------------------- testUnionIntersection
	/** @throws Exception */
	public function testUnionIntersection() : void
	{
		$type = self::$parser->parse('float|Intersection&Union');
		self::assertInstanceOf(Union::class, $type);
		$types = $type->getTypes();
		self::assertCount(2, $types);
		$type = reset($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('float', $type->getName());
		$types = next($types);
		self::assertInstanceOf(Intersection::class, $types);
		$types = $types->getTypes();
		self::assertCount(2, $types);
		$type = reset($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Intersection', $type);
		$type = next($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Union', $type);
	}

	//-------------------------------------------------------- testUnionIntersectionIntersectionUnion
	/** @throws Exception */
	public function testUnionIntersectionIntersectionUnion() : void
	{
		$type = self::$parser->parse('float|Intersection&Multiple&Union|int');
		self::assertInstanceOf(Union::class, $type);
		$types = $type->getTypes();
		self::assertCount(3, $types);
		$type = end($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('int', $type->getName());
		$type = reset($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('float', $type->getName());
		$types = next($types);
		self::assertInstanceOf(Intersection::class, $types);
		$types = $types->getTypes();
		self::assertCount(3, $types);
		$type = reset($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Intersection', $type);
		$type = next($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Multiple', $type);
		$type = next($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Union', $type);
	}

	//-------------------------------------------------------------------- testUnionIntersectionUnion
	/** @throws Exception */
	public function testUnionIntersectionUnion() : void
	{
		$type = self::$parser->parse('float|Intersection&Union|int');
		self::assertInstanceOf(Union::class, $type);
		$types = $type->getTypes();
		self::assertCount(3, $types);
		$type = reset($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('float', $type->getName());
		$type = end($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('int', $type->getName());
		$types = prev($types);
		self::assertInstanceOf(Intersection::class, $types);
		$types = $types->getTypes();
		self::assertCount(2, $types);
		$type = reset($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Intersection', $type);
		$type = next($types);
		self::assertInstanceOf(Named::class, $type);
		self::assertEquals('Union', $type);
	}

	//------------------------------------------------------------------------------- testUnknownType
	public function testUnknownType() : void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(Exception::UNKNOWN_TYPE);
		$this->expectExceptionMessage(
			'Unknown type [\\\\ITRocks\Reflect\Tests\Type\PHPStan_Parser_Test]'
			. ' into [\\\\ITRocks\Reflect\Tests\Type\PHPStan_Parser_Test] position 0'
		);
		self::$parser->parse('\\\\' . __CLASS__);
	}

}
