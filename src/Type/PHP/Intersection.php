<?php
namespace ITRocks\Reflect\Type\PHP;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Interface;
use ITRocks\Reflect\Type\Interface\Single;

class Intersection implements Interface\Intersection
{
	use Multiple;

	//------------------------------------------------------------------------------------- SEPARATOR
	/** @var string */
	protected const SEPARATOR = '&';

	//----------------------------------------------------------------------------------- __construct
	/** @param non-empty-list<Intersection|Single|Union> $types */
	public function __construct(array $types, Reflection $reflection, bool $allows_null)
	{
		$this->allows_null = $allows_null;
		$this->types       = $types;
		$this->reflection  = $reflection;
	}

}
