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

	//------------------------------------------------------------------------------------ COLLECTION
	/** @var non-empty-list<string>  */
	protected const COLLECTION = [
		'array', 'class-string', 'iterable', 'key-of', 'list', 'non-empty-array', 'non-empty-list',
		'value-of'
	];

	//---------------------------------------------------------------------------------------- DEPTHS
	/** @var array<string,int> */
	protected const DEPTHS = ['<' => 0, '(' => 0, '{' => 0];

	//------------------------------------------------------------------------------------- KEY_CHARS
	/** @var string */
	protected const KEY_CHARS = '?|&:,<({})> ';

	//----------------------------------------------------------------------------------------- MATCH
	/** @var array<string,string> */
	protected const MATCH = ['>' => '<', ')' => '(', '}' => '{', ':' => '?'];

	//---------------------------------------------------------------------------------------- OPENER
	protected const OPENER = 0;

	//------------------------------------------------------------------------------------- SEPARATOR
	protected const SEPARATOR = 1;

	//------------------------------------------------------------------------------------ SEPARATORS
	/** @var non-empty-list<string> */
	protected const SEPARATORS = ['|', '&', ':', ','];

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
		$depth = 0;
		/** @var non-empty-list<array{""|value-of<self::MATCH>,""|value-of<self::SEPARATORS>}> $depths */
		$depths   = [0 => ['', '']];
		$length   = strlen($source);
		$position = 0;
		$type     = '';
		/** @var non-empty-list<list<Reflection_Type|bool|string>> $types */
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
				if (!str_contains(self::KEY_CHARS, $char)) {
					throw new Exception(
						"Bad character [$char] after string literal [$type] into [$source] position "
							. $position,
						Exception::BAD_CHARACTER_IN_STRING_LITERAL
					);
				}
				$type = '"' . $type;
			}
			while (!str_contains(self::KEY_CHARS, $char)) {
				$type .= $char;
				$position ++;
				if ($position === $length) {
					$type = trim($type);
					break 2;
				}
				$char = $source[$position];
			}
			if ($char === ' ') {
				if (substr($source, $position, 4) !== ' is ') {
					$type .= ' ';
					$position ++;
					continue;
				}
				$depths[$depth][self::OPENER]    = '?';
				$depths[$depth][self::SEPARATOR] = ':';
				$types[$depth][]                 = $type;
				if (substr($source, $position + 3, 5) === ' not ') {
					$types[$depth][] = true;
					$position += 8;
				}
				else {
					$types[$depth][] = false;
					$position += 4;
				}
				$type = '';
				continue;
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
			if (str_contains('?:', $separator) && ($depths[$depth] === ['?', ':'])) {
				if ($type !== '') {
					$types[$depth][] = $type;
					$type = '';
				}
				$position ++;
				continue;
			}
			if (($separator === ':') && ($depths[$depth][self::OPENER] === '{')) {
				if (str_starts_with($type, '"')) {
					$type = substr($type, 1);
				}
				$type .= $separator;
				$position ++;
				continue;
			}
			if (str_contains('|&:,', $separator)) {
				$opener_depth = $depth;
				while (($opener_depth > 0) && ($depths[$opener_depth][self::OPENER] === '')) {
					$opener_depth --;
				}
				$separator_levels = ($depths[$opener_depth][self::OPENER] === '?') ? ':|&,' : '|&:,';
				$separator_level  = strpos($separator_levels, $separator);
				$previous_separator = $depths[$depth][self::SEPARATOR];
				if ($separator !== $previous_separator) {
					if ($previous_separator !== '') {
						$previous_separator_level = strpos($separator_levels, $previous_separator);
						if ($previous_separator_level !== false) {
							// higher priority : enter next depth
							if ($separator_level > $previous_separator_level) {
								$depth ++;
								/** @var non-empty-list<array{""|value-of<self::MATCH>,""|value-of<self::SEPARATORS>}> $depths */
								$depths[$depth] = ['', $separator];
								$types[$depth]  = [];
							}
							// lower priority
							else {
								// back to previous depth
								if (($depth > 0) && ($separator === $depths[$depth - 1][self::SEPARATOR])) {
									$type = $this->parseType(
										array_pop($depths), array_pop($types), $type, $source, $position
									);
									$depth --;
									$types[$depth][] = $type;
								}
								// lower priority : current depth goes next and closes
								else {
									$opener_type = (
										($depths[$depth][self::OPENER] === '')
										|| (count($types[$depth]) < 2)
									)
										? null
										: $types[$depth][0];
									$types[$depth] = [
										$this->parseType($depths[$depth], $types[$depth], $type, $source, $position)
									];
									if (isset($opener_type)) {
										array_unshift($types[$depth], $opener_type);
									}
								}
								/** @var non-empty-list<array{""|value-of<self::MATCH>,""|value-of<self::SEPARATORS>}> $depths */
								$depths[$depth][self::SEPARATOR] = $separator;
								$position ++;
								$type = '';
								continue;
							}
						}
					}
					/** @var non-empty-list<array{""|value-of<self::MATCH>,""|value-of<self::SEPARATORS>}> $depths */
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
				/** @var non-empty-list<array{""|value-of<self::MATCH>,""|value-of<self::SEPARATORS>}> $depths */
				$depths[$depth] = [$separator, ''];
				$types[$depth]  = [$type];
				$position ++;
				$type = '';
				continue;
			}
			if (str_contains('>)}', $separator)) {
				$opener = self::MATCH[$separator];
				while (($depth > 1) && ($depths[$depth][self::OPENER] !== $opener)) {
					$depth --;
					/** @var non-empty-list<array{""|value-of<self::MATCH>,""|value-of<self::SEPARATORS>}> $depths */
					/** @var non-empty-list<list<Reflection_Type|bool|string>> $types */
					$type = $this->parseType(
						array_pop($depths), array_pop($types), $type, $source, $position
					);
				}
				if ($depth === 0) {
					throw new Exception(
						"Bad closing character [$separator] into [$source] position " . $position,
						Exception::BAD_CLOSING_CHARACTER
					);
				}
				$depth --;
				$types[$depth][] = $this->parseType(
					array_pop($depths), array_pop($types), $type, $source, $position
				);
				$position ++;
				$type = '';
				continue;
			}
			$position ++;
		}
		$type = trim($type);
		while ($depths !== []) {
			/** @var non-empty-list<list<Reflection_Type|string>> $types */
			if (($type === '') && (count(end($types)) === 1)) {
				array_pop($depths);
				$type = array_pop($types)[0];
			}
			else {
				$type = $this->parseType(array_pop($depths), array_pop($types), $type, $source, $position);
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
		elseif (($pos = strpos($type, ':')) !== false) {
			$parameter = [
				false,
				false,
				substr($type, 0, $pos),
				false
			];
			$type = $this->parseSingleType(substr($type, $pos + 1), $source, $position);
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
		elseif (str_ends_with($type, '[]')) {
			$dimensions = 0;
			do {
				$dimensions ++;
				$type = substr($type, 0, -2);
			}
			while (str_ends_with($type, '[]'));
			$type = $this->parseSingleType($type, $source, $position - $dimensions * 2);
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
		elseif (str_starts_with($type, '$')) {
			$type = new Named($type, $this->reflection, $this->allows_null);
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
	/**
	 * @param-out int<0,max> $position
	 * @throws Exception
	 */
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
	 * @param array{""|value-of<self::MATCH>,""|value-of<self::SEPARATORS>} $depth
	 * @param list<Reflection_Type|bool|string>                             $types
	 * @param non-negative-int                                              $position
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
		$opener_type = (($opener !== '') && str_contains('?<{(', $opener))
			? array_shift($types)
			: '';
		foreach ($types as $key => $sub_type) {
			if (is_string($sub_type)) {
				$types[$key] = $this->parseSingleType($sub_type, $source, $position);
			}
		}
		/** @var non-empty-list<bool|Reflection_Type> $types */
		if (isset($single_type)) {
			$types[] = $single_type;
		}
		if ($separator === ':') {
			if ($opener === '?') {
				/** @var array{Reflection_Type,bool,Reflection_Type,Reflection_Type,Reflection_Type} $types */
				return new Condition(
					$types[0], $types[1], $types[2], $types[3], $types[4],
					$this->reflection, $this->allows_null
				);
			}
			else {
				/** @var non-empty-list<Reflection_Type> $types */
				$return = array_pop($types);
				while ($types !== []) {
					$type = array_pop($types);
					if ($type instanceof Call) {
						$type->return = $return;
					}
					$return = $type;
				}
				return $return;
			}
		}
		/** @var non-empty-list<Reflection_Type> $types */
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
			if ($opener_type === 'int-mask') {
				if (!in_array($separator, ['', ','], true)) {
					$types = [$this->parseType(['', $separator], $types, '', $source, $position)];
				}
				foreach ($types as $value) {
					if (!(
						($value instanceof Class_Constant)
						|| ($value instanceof Int_Literal)
						|| ($value instanceof Union)
					)) {
						throw new Exception(
							"Invalid integer mask value [$value] into [$source] position " . $position,
							Exception::INVALID_VALUE
						);
					}
				}
				/** @var list<Class_Constant|Int_Literal|Union> $types */
				return new Int_Mask($opener_type, $types, $this->reflection, $this->allows_null);
			}
			if ($opener_type === 'int-mask-of') {
				foreach ($types as $value) {
					if (!(($value instanceof Class_Constant) || ($value instanceof Int_Literal))) {
						throw new Exception(
							"Invalid integer mask value [$value] into [$source] position " . $position,
							Exception::INVALID_VALUE
						);
					}
				}
				/** @var list<Class_Constant|Int_Literal> $types */
				return new Int_Mask($opener_type, $types, $this->reflection, $this->allows_null);
			}
			if (in_array($opener_type, static::COLLECTION, true)) {
				if (!in_array($separator, ['', ','], true)) {
					$types = [$this->parseType(['', $separator], $types, '', $source, $position)];
				}
				$key  = (count($types) > 1) ? reset($types) : null;
				$type = end($types);
				return in_array($opener_type, ['class-string', 'key-of', 'value-of'], true)
					? new Of($opener_type, $type, $this->reflection, $this->allows_null)
					: Collection::ofName($opener_type, $type, $this->reflection, $this->allows_null, $key);
			}
			if (is_string($opener_type)) {
				return new Shape($opener_type, $types, $this->reflection, $this->allows_null);
			}
		}
		elseif (($opener === '{') && is_string($opener_type)) {
			$shape_types = [];
			foreach ($types as $shape_type) {
				if ($shape_type instanceof Parameter) {
					$shape_key = is_numeric($shape_type->name)
						? (int)$shape_type->name
						: $shape_type->name;
					$shape_types[$shape_key] = $shape_type->type;
				}
				else {
					$shape_types[] = $shape_type;
				}
			}
			return new Shape($opener_type, $shape_types, $this->reflection, $this->allows_null);
		}
		return $this->parseSingleType($type, $source, $position);
	}

}
