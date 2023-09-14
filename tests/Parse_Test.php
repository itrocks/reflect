<?php
namespace ITRocks\Reflect\Tests;

use ITRocks\Reflect\Parse;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class Parse_Test extends TestCase
{

	//--------------------------------------------------------------------------------- testClassName
	public function testClassName() : void
	{
		$tokens = static::tokensTo('<?php class Class_Name', T_CLASS, 'class');
		$tokens_param = $tokens;
		self::assertEquals('Class_Name', Parse::className($tokens_param, ''));
		$tokens_param = $tokens;
		self::assertEquals('A\\Namespace\\Class_Name', Parse::className($tokens_param, 'A\\Namespace'));
	}

	//----------------------------------------------------------------------------- testNamespaceName
	#[TestWith(['Short_Namespace;', 'Short_Namespace'])]
	#[TestWith(['Long\\Namespace;', 'Long\Namespace'])]
	#[TestWith(['Short_Namespace {', 'Short_Namespace'])]
	#[TestWith(['Long\\Namespace {', 'Long\Namespace'])]
	public function testNamespaceName(string $code, string $expected) : void
	{
		$tokens = static::tokensTo('<?php namespace ' . $code, T_NAMESPACE, 'namespace');
		self::assertEquals($expected, Parse::namespaceName($tokens));
	}

	//------------------------------------------------------------------------------ testNamespaceUse
	/** @param array<string,string> $expected */
	#[TestWith(['\Absolute\Long\Clause', ['Clause' => 'Absolute\Long\Clause']])]
	#[TestWith(['Long\Clause', ['Clause' => 'Long\Clause']])]
	#[TestWith(['Short', ['Short' => 'Short']])]
	#[TestWith(['Short as Something_Else', ['Something_Else' => 'Short']])]
	#[TestWith(['Short, Long\Clause as Something, \Absolute\Long\Clause', ['Short' => 'Short', 'Something' => 'Long\Clause', 'Clause' => 'Absolute\Long\Clause']])]
	#[TestWith(['Long\\{ Clause as Thing, Other }, Short', ['Thing' => 'Long\Clause', 'Other' => 'Long\Other', 'Short' => 'Short']])]
	public function testNamespaceUse(string $code, array $expected) : void
	{
		$tokens = static::tokensTo('<?php use ' . $code . ';', T_USE, 'use');
		self::assertEquals($expected, Parse::namespaceUse($tokens));
	}

	//------------------------------------------------------------------------ testReferenceClassName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param array<string,string> $namespace_use
	 * @param list<string>         $expected
	 */
	#[TestWith([0,  'C', [], '', ['C']])]
	#[TestWith([1,  'C', [], 'N', ['N\C']])]
	#[TestWith([2,  'C', ['C' => 'A'], '', ['A']])]
	#[TestWith([3,  'C', ['B' => 'R', 'C' => 'A'], 'N', ['A']])]
	#[TestWith([4,  '\F\Q\N', [], '', ['F\Q\N']])]
	#[TestWith([5,  '\F\Q\N', [], 'M', ['F\Q\N']])]
	#[TestWith([6,  '\F\Q\N', ['C' => 'A'], '', ['F\Q\N']])]
	#[TestWith([7,  '\F\Q\N', ['F' => 'R', 'C' => 'A'], 'M', ['F\Q\N']])]
	#[TestWith([8,  'C\F', [], '', ['C\F']])]
	#[TestWith([9,  'C\F', [], 'N', ['N\C\F']])]
	#[TestWith([10, 'C\F', ['C' => 'O\A'], '', ['O\A\F']])]
	#[TestWith([11, 'C\F', ['F' => 'R', 'C' => 'A'], 'N', ['A\F']])]
	#[TestWith([12, 'C, C\F, \F\Q\N, T', ['F' => 'R', 'C' => 'O\A'], 'M', ['O\A', 'O\A\F', 'F\Q\N', 'M\T']])]
	#[TestWith([13, 'namespace\A\C', ['A' => 'Z'], 'N', ['N\A\C']])]
	public function testReferenceClassName(
		int $key, string $code, array $namespace_use, string $namespace, array $expected
	) : void
	{
		$actual = [];
		$tokens = static::tokensTo('<?php class C implements ' . $code, T_IMPLEMENTS, 'implements');
		$token  = current($tokens);
		while ($token !== false) {
			if (in_array(
				$token[0], [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_NAME_RELATIVE, T_STRING], true
			)) {
				/** @noinspection PhpUnhandledExceptionInspection Valid $token */
				$actual[] = Parse::referenceClassName($token, $namespace_use, $namespace);
			}
			$token = next($tokens);
		}
		self::assertEquals($expected, $actual, "data set #$key");
	}

	//------------------------------------------------------------------- testReferenceClassNameError
	public function testReferenceClassNameError() : void
	{
		$this->expectException(ReflectionException::class);
		$this->expectExceptionMessage(
			'Called ' . Parse::class . '::referenceClassName with an invalid token'
		);
		$error_reporting = error_reporting(0);
		Parse::referenceClassName([T_CLASS, 'class', 1], [], '');
		error_reporting($error_reporting);
	}

	//-------------------------------------------------------------------------------------- tokensTo
	/** @return list<array{int,string,int}|string> */
	protected function tokensTo(string $code, int $token_id, string $string) : array
	{
		$tokens = token_get_all($code);
		$token  = reset($tokens);
		while (($token !== false) && ($token[0] !== $token_id)) {
			$token = next($tokens);
		}
		self::assertEquals([$token_id, $string, 1], $token);
		return $tokens;
	}

}
