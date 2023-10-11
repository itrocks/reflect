<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Intersection;
use ITRocks\Reflect\Type\PHP\Named;
use ITRocks\Reflect\Type\PHP\Union;

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

	//---------------------------------------------------------------------------------------- OPENER
	protected const OPENER = 0;

	//------------------------------------------------------------------------------------- SEPARATOR
	protected const SEPARATOR = 1;

	//------------------------------------------------------------------------------------ SEPARATORS
	/** @var string */
	protected const SEPARATORS = '?|&:,<({})>';

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
		$depth    = 0;
		/** @var non-empty-list<array{""|key-of<self::MATCH>,""|"|"|"&"|":"|","}> $depths */
		$depths   = [0 => ['', '']];
		$length   = strlen($source);
		$position = 0;
		$type     = '';
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
				if (!str_contains(self::SEPARATORS, $char)) {
					throw new Exception(
						"Bad character [$char] after string literal [$type] into [$source] position " . $position,
						Exception::BAD_CHARACTER_IN_STRING_LITERAL
					);
				}
				$type = '"' . $type;
			}
			while (!str_contains(self::SEPARATORS, $char)) {
				$type .= $char;
				$position ++;
				if ($position === $length) {
					$type = trim($type);
					break 2;
				}
				$char = $source[$position];
			}
			if (($char === ':') && ($position + 1 < $length) && ($source[$position + 1] === ':')) {
				$type .= '::';
				$position += 2;
				continue;
			}
			elseif (($char === '&') && str_ends_with($type, ' ')) {
				$type .= $char;
				$position ++;
				continue;
			}
			$separator = $char;
			$type      = trim($type);
			if (($type === '') && ($types[$depth] === [])) {
				if ($separator === '?') {
					$this->allows_null = true;
					$position ++;
					continue;
				}
				if ($separator !== '(') {
					throw new Exception(
						"Missing type into [$source] position " . $position, Exception::MISSING_TYPE
					);
				}
			}
			$separator_level = strpos('|&:,', $separator);
			if ($separator_level !== false) {
				$previous_separator = $depths[$depth][self::SEPARATOR];
				if ($separator !== $previous_separator) {
					if ($previous_separator !== '') {
						$previous_separator_level = strpos('|&:,', $previous_separator);
						if ($previous_separator_level !== false) {
							// higher priority : enter next depth
							if ($separator_level > $previous_separator_level) {
								$depth ++;
								/** @var non-empty-list<array{""|key-of<self::MATCH>,""|"|"|"&"|":"|","}> $depths */
								$depths[$depth] = ['', $separator];
								$types[$depth]  = [];
							}
							// lower priority
							else {
								// back to previous depth
								if (($depth > 0) && ($separator === $depths[$depth - 1][self::SEPARATOR])) {
									$type = $this->parseType(
										array_pop($depths), array_pop($types), $type, $source,
										$position // @phpstan-ignore-line $position is int
									);
									$depth --;
									$types[$depth][] = $type;
								}
								// lower priority : current depth goes next and closes
								else {
									$opener_type = (($depths[$depth][self::OPENER] === '') || (count($types[$depth]) < 2))
										? null
										: $types[$depth][0];
									$types[$depth] = [
										$this->parseType(
											$depths[$depth], $types[$depth], $type, $source,
											$position // @phpstan-ignore-line $position is int
										)
									];
									if (isset($opener_type)) {
										array_unshift($types[$depth], $opener_type);
									}
								}
								/** @var non-empty-list<array{""|key-of<self::MATCH>,""|"|"|"&"|":"|","}> $depths */
								$depths[$depth][self::SEPARATOR] = $separator;
								$position ++;
								$type = '';
								continue;
							}
						}
					}
					/** @var non-empty-list<array{""|key-of<self::MATCH>,""|"|"|"&"|":"|","}> $depths */
					$depths[$depth][self::SEPARATOR] = $separator;
				}
				if ($type !== '') {
					$types[$depth][] = $type;
				}
				$position ++;
				$type = '';
				continue;
			}
			if (str_contains('<({', $separator)) {
				$depth ++;
				/** @var non-empty-list<array{""|key-of<self::MATCH>,""|"|"|"&"|":"|","}> $depths */
				$depths[$depth] = [$separator, ''];
				$types[$depth]  = [$type];
				$position ++;
				$type = '';
				continue;
			}
			if (str_contains('>)}', $separator)) {
				if ($depth === 0) {
					throw new Exception(
						"Bad closing character [$separator] into [$source] position " . $position,
						Exception::BAD_CLOSING_CHARACTER
					);
				}
				$depth --;
				$types[$depth][] = $this->parseType(
					array_pop($depths), array_pop($types), $type, $source,
					$position // @phpstan-ignore-line $position is int
				);
				$position ++;
				$type = '';
				continue;
			}
			$position ++;
		}
		while ($depths !== []) {
			/** @var non-empty-list<list<Reflection_Type|string>> $types */
			if (($type === '') && (count(end($types)) === 1)) {
				array_pop($depths);
				$type = array_pop($types)[0];
			}
			else {
				$type = $this->parseType(
					array_pop($depths), array_pop($types), $type, $source,
					$position // @phpstan-ignore-line $position is int
				);
			}
		}

		/** @var Reflection_Type $type */
		return $type;
	}

	//------------------------------------------------------------------------------- parseSingleType
	/** @throws Exception */
	protected function parseSingleType(string $type, string $source, int $position) : Parameter|Single
	{
		$is_variadic  = strpos($type, '...');
		$is_reference = strpos($type, '&');
		$has_label    = strpos($type, '$');
		$is_optional  = str_ends_with($type, '=');
		if ($is_optional || (bool)$is_reference || (bool)$is_variadic || (bool)$has_label) {
			$parameter = [
				(bool)$is_variadic,
				(bool)$is_reference,
				(bool)$has_label ? rtrim(substr($type, (int)$has_label + 1), ' =') : '',
				$is_optional
			];
			$type = rtrim(match(true) {
				(bool)$is_variadic  => substr($type, 0, (int)$is_variadic),
				(bool)$is_reference => substr($type, 0, (int)$is_reference),
				(bool)$has_label    => substr($type, 0, (int)$has_label),
				default             => substr($type, 0, -1)
			});
		}
		else {
			$parameter = null;
		}
		if (str_contains($type, '::')) {
			/** @var class-string $class */
			[$class, $constant] = explode('::', $type, 2);
			$type = new Class_Constant($class, $constant, $this->reflection, $this->allows_null);
		}
		elseif (
			in_array($type, static::BOTTOM, true)
			|| in_array($type, static::SINGLE, true)
			|| $this->isClassName($type)
		) {
			$type = new Named($type, $this->reflection, $this->allows_null);
		}
		elseif (str_starts_with($type, '"')) {
			$type = new String_Literal(substr($type, 1), $this->reflection, $this->allows_null);
		}
		elseif (str_contains($type, '[')) {
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
			$type = $this->parseSingleType($type, $source, $position - $dimensions * 2);
			if ($type instanceof Parameter) {
				throw new Exception(
					"Unexpected parameter [$type] into [$source] position " . $position,
					Exception::UNEXPECTED_PARAMETER
				);
			}
			$type = Collection::ofDimensions($type, $dimensions, $this->reflection, $this->allows_null);
		}
		elseif (($type !== '') && str_contains('0123456789.-', $type[0])) {
			if (!(bool)preg_match('/^-?([0-9]*[\\\\.])?[0-9]+$/', $type)) {
				throw Exception::badNumericLiteral($type, $source, $position - strlen($type));
			}
			$type = str_contains($type, '.')
				? new Float_Literal((float)$type, $this->reflection, $this->allows_null)
				: new Int_Literal((int)$type, $this->reflection, $this->allows_null);
		}
		if (is_string($type)) {
			throw new Exception(
				"Unknown type [$type] into [$source] position " . ($position - strlen($type)),
				Exception::UNKNOWN_TYPE
			);
		}
		if (isset($parameter)) {
			return new Parameter($type, $parameter[0], $parameter[1], $parameter[2], $parameter[3]);
		}
		return $type;
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
	 * @param array{''|key-of<self::MATCH>,""|"|"|"&"|":"|","} $depth
	 * @param list<Reflection_Type|string>                     $types
	 * @param non-negative-int                                 $position
	 * @throws Exception
	 */
	protected function parseType(
		array $depth, array $types, Reflection_Type|string $type, string $source, int $position
	) : Reflection_Type
	{
		$single_type = is_string($type)
			? (($type === '') ? null : $this->parseSingleType($type, $source, $position))
			: $type;
		if (($types === []) && isset($single_type)) {
			return $single_type;
		}
		[$opener, $separator] = $depth;
		$opener_type = (($opener !== '') && str_contains('<{(', $opener))
			? array_shift($types)
			: '';
		foreach ($types as $key => $sub_type) {
			if (is_string($sub_type)) {
				$types[$key] = $this->parseSingleType($sub_type, $source, $position);
			}
		}
		/** @var non-empty-list<Reflection_Type> $types */
		if (isset($single_type)) {
			$types[] = $single_type;
		}
		if ($separator === ':') {
			$return = array_pop($types);
			while ($types !== []) {
				$type = array_pop($types);
				if ($type instanceof Call) {
					$type->return = $return;
				}
				else {
					throw new Exception(
						"Unexpected separator [:] into [$source] position " . $position,
						Exception::UNEXPECTED_SEPARATOR
					);
				}
				$return = $type;
			}
			return $return;
		}
		if ($opener === '') {
			if ($separator === '|') {
				return new Union($types, $this->reflection, $this->allows_null);
			}
			elseif ($separator === '&') {
				return new Intersection($types, $this->reflection, $this->allows_null);
			}
		}
		elseif ($opener === '(') {
			if ($opener_type === '') {
				if (count($types) === 1) {
					return reset($types);
				}
				if ($separator === '|') {
					return new Union($types, $this->reflection, $this->allows_null);
				}
				if ($separator === '&') {
					return new Intersection($types, $this->reflection, $this->allows_null);
				}
			}
			elseif (in_array($opener_type, ['callable', 'Closure', '\Closure'], true)) {
				foreach ($types as $key => $parameter_type) {
					if (!($parameter_type instanceof Parameter)) {
						$types[$key] = new Parameter($parameter_type, false, false, '', false);
					}
				}
				/** @var non-empty-list<Parameter> $types */
				return new Call($opener_type, $types, $this->reflection, $this->allows_null);
			}
		}
		elseif ($opener === '<') {
			if ($opener_type === 'int') {
				$value0 = ($types[0] instanceof Int_Literal)
					? $types[0]->value
					: strval($types[0]);
				$value1 = ($types[1] instanceof Int_Literal)
					? $types[1]->value
					: strval($types[1]);
				if (($value0 !== 'min') && !is_int($value0)) {
					throw new Exception(
						"Invalid integer range min limit [$value0] into [$source] position " . $position,
						Exception::INVALID_LIMIT
					);
				}
				if (($value1 !== 'max') && !is_int($value1)) {
					throw new Exception(
						"Invalid integer range max limit [$value1] into [$source] position " . $position,
						Exception::INVALID_LIMIT
					);
				}
				return new Int_Range($value0, $value1, $this->reflection, $this->allows_null);
			}
			elseif ($opener_type === 'int-mask-of') {
				foreach ($types as $value) {
					if (!(($value instanceof Class_Constant) || ($value instanceof Int_Literal))) {
						throw new Exception(
							"Invalid integer mask value [$value] into [$source] position " . $position,
							Exception::INVALID_VALUE
						);
					}
				}
				/** @var list<Class_Constant|Int_Literal> $types */
				return new Int_Mask_Of($types, $this->reflection, $this->allows_null);
			}
		}
		throw new Exception('Bad type');
	}

}
