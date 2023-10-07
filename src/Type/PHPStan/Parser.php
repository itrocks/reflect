<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\Undefined;

class Parser // phpcs:ignore
{

	//---------------------------------------------------------------------------------------- BOTTOM
	protected const BOTTOM = ['never', 'never-return', 'never-returns', 'no-return', 'void'];

	//---------------------------------------------------------------------------------------- DEPTHS
	/** @var array<string,int> */
	protected const DEPTHS = ['?' => 0, '&' => 0, '|' => 0, '<' => 0, '(' => 0, '{' => 0, '[' => 0];

	//----------------------------------------------------------------------------------------- MATCH
	/** @var array<string,string> */
	protected const MATCH = [
		'?' => ':', '&' => '&', '|' => '|', '<' => '>', '(' => ')', '{' => '}', '[' => ']'
	];

	//---------------------------------------------------------------------------------------- SINGLE
	/** @var non-empty-list<string> */
	protected const SINGLE = [
		'$this', 'array-key', 'array', 'bool', 'boolean', 'callable-string', 'callable', 'class-string',
		'double', 'false', 'float', 'int', 'integer', 'iterable', 'iterable-string', 'mixed',
		'negative-int', 'non-empty-string', 'non-falsy-string', 'non-negative-int', 'non-positive-int',
		'non-zero-int', 'null', 'numeric-string', 'object', 'parent', 'positive-int', 'resource',
		'scalar', 'self', 'static', 'string', 'true', 'truthy-string'
	];

	//---------------------------------------------------------------------------------- $allows_null
	protected bool $allows_null = false;

	//--------------------------------------------------------------------------------------- $length
	protected int $length;

	//------------------------------------------------------------------------------------- $position
	protected int $position = 0;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(protected Reflection $reflection, protected string $source)
	{
		$this->length = strlen($source);
	}

	//----------------------------------------------------------------------------------- isClassName
	protected function isClassName(string $type_string) : bool
	{
		return (bool)preg_match(
			'/^\\\\?[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*(?:\\\\[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)*$/',
			$type_string
		);
	}

	//----------------------------------------------------------------------------------------- parse
	public function parse() : Reflection_Type
	{
		$depths      = static::DEPTHS;
		$length      = $this->length;
		$position    = 0;
		$separators  = '?&|<({[';
		$source      = $this->source;
		$type        = null;
		$type_string = '';
		while ($position < $length) {
			$char = $source[$position];
			if (str_contains('\'"', $char)) {
				$type = $this->parseStringLiteral($source, $position, $length, $char);
				continue;
			}
			$this->allows_null = false;
			while (!str_contains($separators, $char)) {
				$type_string .= $char;
				$position ++;
				if ($position > $length) {
					// todo last value of $type_string is to be stored
					break 2;
				}
				$char = $source[$position];
			}
			$separator = $char;
			if (($type_string === '') && !isset($type)) {
				if ($separator === '?') {
					$this->allows_null = true;
					$position ++;
					continue;
				}
				if ($separator === '(') {
					$depth =& $depths['('];
					if ($depth === 0) {
						$separators .= ')';
					}
					$depth ++;
					$position ++;
					continue;
				}
				trigger_error(
					"Type cannot start with separator $separator into position " . $position. " of [$source]",
					E_USER_ERROR
				);
			}
			if ($separator === '[') {
				$brackets = '[]';
				$from     = $position;
				$parity   = (($position % 2) === 0) ? 0 : 1;
				$position ++;
				while (($position < $length) && ($char !== $brackets[($position + $parity) % 2])) {
					$char = $source[$position++];
				}
				/** @var positive-int $dimensions */
				$dimensions = (int)ceil(($position - $from) / 2);
				if (is_null($type)) {
					$type = $this->parseSingleType($type_string);
				}
				$type = Collection::ofDimensions($type, $dimensions, $this->reflection, $this->allows_null);
				continue;
			}
			if (str_contains('0123456789.', $type_string[0])) {
				$type = $this->parseNumberLiteral($source, $position, $length, $char);
				continue;
			}
			$type_string = '';
			$position ++;
		}
		return $type ?? new Undefined($this->reflection);
	}

	//---------------------------------------------------------------------------- parseNumberLiteral
	protected function parseNumberLiteral(string $source, int& $position, int $length, string $char)
		: Float_Literal|Int_Literal
	{
		$position ++;
		$number = $char;
		while (($position < $length) && str_contains('0123456789', $char)) {
			$number .= $char;
			$char = $source[$position++];
		}
		return str_contains($number, '.')
			? new Float_Literal((float)$number, $this->allows_null)
			: new Int_Literal((int)$number, $this->allows_null);
	}

	//------------------------------------------------------------------------------- parseSingleType
	protected function parseSingleType(string $type_string) : Single
	{
		if (in_array($type_string, static::SINGLE, true) || $this->isClassName($type_string)) {
			return new Named($type_string, $this->reflection, $this->allows_null);
		}
		return new Undefined($this->reflection);
	}

	//---------------------------------------------------------------------------- parseStringLiteral
	protected function parseStringLiteral(string $source, int& $position, int $length, string $char)
		: String_Literal
	{
		$position ++;
		if ($position === $length) {
			return new String_Literal($char, $this->allows_null);
		}
		$string = '';
		$quote  = $char;
		do {
			$char = $source[$position];
			if ($char === '\\') {
				$position ++;
				if ($position === $length) {
					return new String_Literal($string . $char, $this->allows_null);
				}
				$string .= $source[$position++];
				continue;
			}
			if ($char === $quote) {
				break;
			}
			$string .= $char;
			$position ++;
		}
		while ($position < $length);
		return new String_Literal($string, $this->allows_null);
	}

}
