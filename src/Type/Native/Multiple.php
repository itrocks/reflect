<?php
namespace ITRocks\Reflect\Type\Native;

use ITRocks\Reflect\Type\Common;
use ReflectionIntersectionType;
use ReflectionNamedType;

trait Multiple
{
	use Allows_Null;
	use Common;

	//----------------------------------------------------------------------------------- getAllTypes
	/** @return non-empty-list<Named> */
	public function getAllTypes() : array
	{
		$all_types = [];
		/** @var non-empty-list<ReflectionIntersectionType|ReflectionNamedType> $next_types */
		$next_types = $this->type->getTypes();
		do {
			$types      = $next_types;
			$next_types = [];
			foreach ($types as $position => $type) {
				if ($type instanceof ReflectionIntersectionType) {
					/** @var non-empty-list<ReflectionIntersectionType|ReflectionNamedType> $next_types */
					$next_types = array_merge($type->getTypes(), array_slice($types, $position + 1));
					continue 2;
				}
				$all_types[] = new Named($type, $this->reflection);
			}
		}
		while ($next_types !== []);
		/** @phpstan-ignore-next-line Will always return non-empty-list: native types contains Named */
		return $all_types;
	}

	//-------------------------------------------------------------------------------- getElementType
	public function getElementType() : Named
	{
		/** @var ReflectionIntersectionType|ReflectionNamedType $type */
		$type = $this->type->getTypes()[0];
		while ($type instanceof ReflectionIntersectionType) {
			/** @var ReflectionIntersectionType|ReflectionNamedType $type */
			$type = $type->getTypes()[0];
		}
		/** @phpstan-ignore-next-line phpstan mistake: ReflectionIntersectionType|ReflectionNamedType */
		return new Named($type, $this->reflection);
	}

	//-------------------------------------------------------------------------------------- getTypes
	/** @return non-empty-list<Intersection|Named> */
	public function getTypes() : array
	{
		/** @var non-empty-list<ReflectionIntersectionType|ReflectionNamedType> $native_types */
		$native_types = $this->type->getTypes();
		$types        = [];
		foreach ($native_types as $type) {
			$types[] = ($type instanceof ReflectionIntersectionType)
				? new Intersection($type, $this->reflection)
				: new Named($type, $this->reflection);
		}
		return $types;
	}

}
