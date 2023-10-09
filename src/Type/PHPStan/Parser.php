<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Intersection;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\PHP\Union;
use ITRocks\Reflect\Type\Undefined;

class Parser // phpcs:ignore
{

	//---------------------------------------------------------------------------------------- BOTTOM
	/** @var non-empty-list<string> */
	protected const BOTTOM = ['never', 'never-return', 'never-returns', 'no-return', 'void'];

	//---------------------------------------------------------------------------------------- DEPTHS
	/** @var array<string,int> */
	protected const DEPTHS = ['<' => 0, '(' => 0, '{' => 0];

	//----------------------------------------------------------------------------------------- MATCH
	/** @var array<string,string> */
	protected const MATCH = ['<' => '>', '(' => ')', '{' => '}'];

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

	//----------------------------------------------------------------------------------- $reflection
	protected Reflection $reflection;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Reflection $reflection)
	{
		$this->reflection = $reflection;
	}

	//----------------------------------------------------------------------------------- isClassName
	protected function isClassName(string $type) : bool
	{
		return (bool)preg_match(
			'/^\\\\?[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*(\\\\[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)*$/',
			$type
		);
	}

	//----------------------------------------------------------------------------------------- parse
	/** @throws Exception */
	public function parse(string $source) : Reflection_Type
	{
		$depth      = 0;
		$depths     = static::DEPTHS;
		$length     = strlen($source);
		$openers    = [$depth => ''];
		$position   = 0;
		$separators = '?|&,<({})>';
		$type       = '';
		/** @var non-empty-list<list<Reflection_Type|string>> $types */
		$types = [$depth => []];
		$this->allows_null = false;
		while ($position < $length) {
			$char = $source[$position];
			if (str_contains('\'"', $char)) {
				$type = $this->parseStringLiteral($source, $position, $length, $char);
				if ($position === $length) {
					$type = '"' . $type;
					break;
				}
				$char = $source[$position];
				if (!str_contains($separators, $char)) {
					throw new Exception(
						"Bad character [$char] after string literal [$type] into [$source] position " . $position,
						Exception::BAD_CHARACTER_IN_STRING_LITERAL
					);
				}
				$type = '"' . $type;
			}
			while (!str_contains($separators, $char)) {
				$type .= $char;
				$position ++;
				if ($position === $length) {
					break 2;
				}
				$char = $source[$position];
			}
			$separator = $char;
			if ($type === '') {
				if ($separator === '?') {
					$this->allows_null = true;
					$position ++;
					continue;
				}
				if ($separator !== '(') {
					throw new Exception(
						"Type cannot start with separator [$separator] into [$source] position " . $position,
						Exception::BAD_START_SEPARATOR
					);
				}
			}
			if (str_contains('|&,', $separator)) {
				if ($separator !== $openers[$depth]) {
					if (($openers[$depth] !== '') && str_contains('|&,', $openers[$depth])) {
						// higher priority : enter next depth
						if (strpos('|&,', $separator) > strpos('|&,', $openers[$depth])) {
							$depth ++;
							$types[$depth] = [];
						}
						// lower priority
						else {
							// back to previous depth
							if (($depth > 0) && ($separator === $openers[$depth - 1])) {
								$type = $this->parseType(
									$type,
									$types,
									$openers,
									$source,
									$position // @phpstan-ignore-line $position is int
								);
								array_pop($openers);
								array_pop($types);
								$depth --;
								$types[$depth][] = $type;
							}
							// lower priority : current depth goes next and closes
							else {
								$types[$depth] = [$this->parseType(
									$type,
									$types,
									$openers,
									$source,
									$position // @phpstan-ignore-line $position is int
								)];
							}
							$openers[$depth] = $separator;
							$type = '';
							$position ++;
							continue;
						}
					}
					$openers[$depth] = $separator;
				}
				$types[$depth][] = $type;
				$type = '';
				$position ++;
				continue;
			}
			if (str_contains('<({', $separator)) {
				$depths[$separator] ++;
				$type .= $separator;
				$depth ++;
				$openers[$depth] = $separator;
				$types[$depth]   = [$type];
				$position ++;
				$type = '';
				continue;
			}
			/** @var non-empty-list<list<Reflection_Type|string>> $types */
			/** @var non-empty-string $type */
			if (str_contains('>)}', $separator)) {
				$depths[$separator] --;
				$depth --;
				if (($depth < 0) || ($depths[$separator] < 0)) {
					throw new Exception(
						"Bad closing character [$separator] into [$source] position " . $position,
						Exception::BAD_CLOSING_CHARACTER
					);
				}
				$position ++;
				$types[] = [$this->parseComplexType(
					$type . $openers[$depth + 1],
					$types[$depth + 1],
					$source,
					$position // @phpstan-ignore-line $position is int
				)];
				array_pop($openers);
				array_pop($types);
				continue;
			}
			$position ++;
		}
		do {
			$type = $this->parseType(
				$type,
				$types,
				$openers,
				$source,
				$position // @phpstan-ignore-line $position is int
			);
			array_pop($openers);
			array_pop($types);
		}
		while ($types !== []);

		return $type;
	}

	//------------------------------------------------------------------------------ parseComplexType
	/**
	 * @param non-empty-string $type
	 * @param list<Reflection_Type|string> $types
	 * @throws Exception
	 */
	protected function parseComplexType(string $type, array $types, string $source, int $position)
		: Reflection_Type
	{
		foreach ($types as $key => $sub_type) {
			if (is_string($sub_type)) {
				$types[$key] = $this->parseSingleType($sub_type, $source, $position);
			}
		}
		return new Undefined($this->reflection);
	}

	//------------------------------------------------------------------------------- parseSingleType
	/** @throws Exception */
	protected function parseSingleType(string $type, string $source, int $position) : Single
	{
		if ($type === '') {
			throw new Exception(
				"Missing type into [$source] position " . $position,
				Exception::MISSING_TYPE
			);
		}
		if (
			in_array($type, static::BOTTOM, true)
			|| in_array($type, static::SINGLE, true)
			|| $this->isClassName($type)
		) {
			return new Named($type, $this->reflection, $this->allows_null);
		}
		if (str_starts_with($type, '"')) {
			return new String_Literal(substr($type, 1), $this->allows_null);
		}
		if (str_contains($type, '[')) {
			$dimensions = 0;
			while (str_ends_with($type, '[]')) {
				$dimensions ++;
				$type = substr($type, 0, -2);
			}
			if ($dimensions === 0) {
				throw new Exception(
					"Missing character ] in array definition into [$source] position " . $position,
					Exception::BAD_CHARACTER_IN_ARRAY_DEFINITION
				);
			}
			$position1 = strpos('[', $type);
			$position2 = strpos(']', $type);
			if (($position1 !== false) && ($position2 !== false) && ($position1 < $position2)) {
				throw new Exception(
					"Bad character [[] in array definition into [$source] position " . $position,
					Exception::BAD_CHARACTER_IN_ARRAY_DEFINITION
				);
			}
			return Collection::ofDimensions(
				$this->parseSingleType($type, $source, $position - $dimensions * 2),
				$dimensions,
				$this->reflection,
				$this->allows_null
			);
		}
		if (str_contains('0123456789.-', $type[0])) {
			if (!(bool)preg_match('/^-?([0-9]*[\\\\.])?[0-9]+$/', $type)) {
				throw Exception::badNumericLiteral($type, $source, $position - strlen($type));
			}
			return str_contains($type, '.')
				? new Float_Literal((float)$type, $this->allows_null)
				: new Int_Literal((int)$type, $this->allows_null);
		}
		throw new Exception(
			"Unknown type [$type] into [$source] position " . ($position - strlen($type)),
			Exception::UNKNOWN_TYPE
		);
	}

	//---------------------------------------------------------------------------- parseStringLiteral
	/** @throws Exception */
	protected function parseStringLiteral(string $source, int& $position, int $length, string $char)
		: string
	{
		$position ++;
		if ($position === $length) {
			throw new Exception(
				"Unterminated string literal into [$source] position " . $position,
				Exception::BAD_CHARACTER_IN_STRING_LITERAL
			);
		}
		$string = '';
		$quote  = $char;
		do {
			$char = $source[$position];
			if ($char === '\\') {
				$position ++;
				if ($position === $length) {
					throw new Exception(
						"Unterminated string literal [$string$char] into [$source] position " . $position,
						Exception::BAD_CHARACTER_IN_STRING_LITERAL
					);
				}
				$string .= $source[$position++];
				continue;
			}
			if ($char === $quote) {
				$position ++;
				return $string;
			}
			$string .= $char;
			$position ++;
		}
		while ($position < $length);
		throw new Exception(
			"Unterminated string literal [$string] into [$source] position " . $position,
			Exception::BAD_CHARACTER_IN_STRING_LITERAL
		);
	}

	//------------------------------------------------------------------------------------- parseType
	/**
	 * @param array<non-negative-int,list<Reflection_Type|string>> $types
	 * @param array<non-negative-int,string>                       $openers
	 * @param non-negative-int                                     $position
	 * @throws Exception
	 */
	protected function parseType(
		Reflection_Type|string $type, array $types, array $openers, string $source, int $position
	) : Reflection_Type
	{
		$single_type = is_string($type)
			? $this->parseSingleType($type, $source, $position)
			: $type;
		$types = end($types);
		if ($types === false) {
			$types = [];
		}
		if ($types === []) {
			return $single_type;
		}
		foreach ($types as $key => $sub_type) {
			if (is_string($sub_type)) {
				$types[$key] = $this->parseSingleType($sub_type, $source, $position);
			}
		}
		/** @var non-empty-list<Reflection_Type> $types */
		$types[] = $single_type;
		return match(end($openers)) {
			'|' => new Union($types, $this->reflection, $this->allows_null),
			'&' => new Intersection($types, $this->reflection, $this->allows_null),
			default => throw new Exception('Bad type')
		};
	}

}
