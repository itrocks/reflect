<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ReflectionException;

class Exception extends ReflectionException
{

	/** @var int */ const BAD_CHARACTER_IN_ARRAY_DEFINITION = 101;
	/** @var int */ const BAD_CHARACTER_IN_FLOAT_LITERAL    = 102;
	/** @var int */ const BAD_CHARACTER_IN_INT_LITERAL      = 103;
	/** @var int */ const BAD_CHARACTER_IN_STRING_LITERAL   = 104;
	/** @var int */ const BAD_CLOSING_CHARACTER             = 105;
	/** @var int */ const BAD_START_SEPARATOR               = 106;
	/** @var int */ const MISSING_TYPE                      = 107;
	/** @var int */ const UNEXPECTED_PARAMETER              = 108;
	/** @var int */ const UNKNOWN_TYPE                      = 109;

	//----------------------------------------------------------------------------- badNumericLiteral
	public static function badNumericLiteral(string $type_string, string $source, int $type_position)
		: self
	{
		if (str_contains($type_string, '.')) {
			$code     = static::BAD_CHARACTER_IN_FLOAT_LITERAL;
			$message  = "Bad character in float literal";
			$position = strlen($type_string);
			if (substr_count($type_string, '.') > 1) {
				$message  = "Too many dots [.] in float literal [$type_string]";
				$position = (int)strpos($type_string, '.', (int)strpos($type_string, '.') + 1);
			}
			elseif (str_ends_with($type_string, '.')) {
				$message = "Missing digit after dot [.] in float literal [$type_string]";
			}
			if (
				(bool)preg_match('/^-?[0-9\\\\.]*([^0-9\\\\.])/', $type_string, $matches, PREG_OFFSET_CAPTURE)
				&& ($matches[1][1] < $position)
			) {
				$message  = "Bad character [{$matches[1][0]}] in float literal [$type_string]";
				$position = $matches[1][1];
			}
		}
		else {
			preg_match('/^-?[0-9]+([^0-9])/', $type_string, $matches, PREG_OFFSET_CAPTURE);
			$code     = static::BAD_CHARACTER_IN_INT_LITERAL;
			$message  = "Bad character [{$matches[1][0]}] in int literal [$type_string]";
			$position = $matches[1][1];
		}
		$position += $type_position;
		return new self("$message into [$source] position " . $position, $code);
	}

}
