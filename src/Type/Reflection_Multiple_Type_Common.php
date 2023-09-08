<?php
namespace ITRocks\Reflect\Type;

use ITRocks\Reflect\Type;
use ReflectionIntersectionType;
use ReflectionNamedType;

trait Reflection_Multiple_Type_Common
{
	use Reflection_Defined_Type_Common;

	//----------------------------------------------------------------------------------- getAllTypes
	/** @return list<Reflection_Named_Type> */
	public function getAllTypes() : array
	{
		/** @var list<ReflectionIntersectionType|ReflectionNamedType> $types */
		$types = $this->type->getTypes();
		return $this->getAllTypesInternal($types);
	}

	//--------------------------------------------------------------------------- getAllTypesInternal
	/**
	 * @param list<ReflectionIntersectionType|ReflectionNamedType> $types
	 * @return list<Reflection_Named_Type>
	 */
	protected function getAllTypesInternal(array $types) : array
	{
		$all_types = [];
		foreach ($types as $type) {
			if ($type instanceof ReflectionNamedType) {
				$all_types[] = new Reflection_Named_Type($type, $this->reflection);
				continue;
			}
			/** @var list<ReflectionNamedType> $sub_types */
			$sub_types = $type->getTypes();
			$all_types = array_merge($all_types, $this->getAllTypesInternal($sub_types));
		}
		return $all_types;
	}

	//-------------------------------------------------------------------------------------- getTypes
	/** @return list<Reflection_Intersection_Type|Reflection_Named_Type> */
	public function getTypes() : array
	{
		$types = [];
		/** @var list<ReflectionIntersectionType|ReflectionNamedType> $native_types */
		$native_types = $this->type->getTypes();
		foreach ($native_types as $native_type) {
			/** @var Reflection_Intersection_Type|Reflection_Named_Type $type */
			$type    = Type::of($native_type, $this->reflection);
			$types[] = $type;
		}
		return $types;
	}

}
