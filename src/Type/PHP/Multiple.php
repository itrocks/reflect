<?php
namespace ITRocks\Reflect\Type\PHP;

use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\Interface;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Interface\Single;

trait Multiple
{
	use Allows_Null;
	use Common;

	//---------------------------------------------------------------------------------------- $types
	/** @var non-empty-list<Reflection_Type> $types */
	protected array $types;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$types = [];
		foreach ($this->types as $type) {
			$types[] = ($type instanceof Interface\Multiple)
				? ('(' . $type . ')')
				: $type;
		}
		return join(static::SEPARATOR, $types);
	}

	//----------------------------------------------------------------------------------- getAllTypes
	/** @return non-empty-list<Single> */
	public function getAllTypes() : array
	{
		$all_types  = [];
		$next_types = $this->types;
		do {
			$types      = $next_types;
			$next_types = [];
			foreach ($types as $position => $type) {
				if ($type instanceof Interface\Multiple) {
					$next_types = array_merge($type->getTypes(), array_slice($types, $position + 1));
					continue 2;
				}
				$all_types[] = $type;
			}
		}
		while ($next_types !== []);
		/** @phpstan-ignore-next-line Will always return non-empty-list: types contains Single */
		return $all_types;
	}

	//-------------------------------------------------------------------------------- getElementType
	public function getElementType() : Single
	{
		$type = $this->types[0];
		while ($type instanceof Interface\Multiple) {
			$type = $type->getTypes()[0];
		}
		/** @var Single $type All elements must be Multiple or Single */
		return $type;
	}

	//-------------------------------------------------------------------------------------- getTypes
	/** @return non-empty-list<Reflection_Type> */
	public function getTypes() : array
	{
		return $this->types;
	}

}
