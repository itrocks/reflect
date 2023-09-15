<?php
namespace ITRocks\Reflect;

use ReflectionException;

abstract class Parse
{

	//---------------------------------------------------------------------------------- CLASS_TOKENS
	public const CLASS_TOKENS = [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_NAME_RELATIVE, T_STRING];

	//------------------------------------------------------------------------------------- className
	/**
	 * @param list<array{int,string,int}|string> $tokens
	 * @param-out list<array{int,string,int}|string> $tokens
	 */
	public static function className(array &$tokens, string $namespace) : string
	{
		do {
			$token = next($tokens);
			if ($token === false) return '';
		} while ($token[0] !== T_STRING);
		return ($namespace === '') ? $token[1] : ($namespace . '\\' . $token[1]);
	}

	//--------------------------------------------------------------------------------- namespaceName
	/**
	 * @param     list<array{int,string,int}|string> $tokens
	 * @param-out list<array{int,string,int}|string> $tokens
	 */
	public static function namespaceName(array &$tokens) : string
	{
		do {
			$token = next($tokens);
			if ($token === false) return '';
		} while (!in_array($token[0], [T_NAME_QUALIFIED, T_STRING, '{', ';'], true));
		return is_array($token) ? $token[1] : '';
	}

	//---------------------------------------------------------------------------------- namespaceUse
	/**
	 * @param     list<array{int,string,int}|string> $tokens
	 * @param-out list<array{int,string,int}|string> $tokens
	 * @return array<string,string>
	 */
	public static function namespaceUse(array &$tokens) : array
	{
		$namespace_use = [];
		$prefix = $use = '';
		do {
			$token = next($tokens);
			if ($token === false) return [];
			if (in_array($token[0], self::CLASS_TOKENS, true)) {
				$use = ltrim($token[1], '\\');
			}
			else switch ($token[0]) {
				case T_AS:
					do {
						$token = next($tokens);
						if ($token === false) return [];
					} while ($token[0] !== T_STRING);
					$namespace_use[$token[1]] = $prefix . $use;
					$use = '';
					break;
				case T_NS_SEPARATOR:
					$use .= $token[1];
					break;
				case '{':
					$prefix = $use;
					break;
				case '}':
				case ',':
				case ';':
					if ($use !== '') {
						$key = (($i = strrpos($use, '\\')) !== false) ? substr($use, $i + 1) : $use;
						$namespace_use[$key] = $prefix . $use;
					}
					if ($token[0] === '}') {
						$prefix = $use = '';
					}
					break;
			}
		} while ($token !== ';');
		return $namespace_use;
	}

	//---------------------------------------------------------------------------- referenceClassName
	/**
	 * @param array{int,string,int} $token
	 * @param array<string,string>  $namespace_use
	 * @return class-string
	 * @throws ReflectionException
	 */
	public static function referenceClassName(array $token, array $namespace_use, string $namespace)
		: string
	{
		switch ($token[0]) {
			case T_NAME_FULLY_QUALIFIED:
				$name = ltrim($token[1], '\\');
				break;
			case T_NAME_QUALIFIED:
				$slash = intval(strpos($token[1], '\\'));
				$use   = $namespace_use[substr($token[1], 0, $slash)] ?? null;
				$name  = isset($use)
					? ($use . substr($token[1], $slash))
					: ltrim($namespace . '\\' . $token[1], '\\');
				break;
			case T_NAME_RELATIVE:
				$name = ltrim($namespace . substr($token[1], 9), '\\');
				break;
			case T_STRING:
				$name = $namespace_use[$token[1]] ?? false;
				if ($name === false) {
					$name = ltrim($namespace . '\\' . $token[1], '\\');
				}
				break;
			default:
				throw new ReflectionException('Called ' . __METHOD__ . ' with an invalid token');
		}
		/** @var class-string $name */
		return $name;
	}

}
