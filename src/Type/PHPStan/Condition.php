<?php
namespace ITRocks\Reflect\Type\PHPStan;

use ITRocks\Reflect\Interface\Reflection;
use ITRocks\Reflect\Type\Common;
use ITRocks\Reflect\Type\Interface\Reflection_Type;
use ITRocks\Reflect\Type\Interface\Single;
use ITRocks\Reflect\Type\PHP\Allows_Null;

class Condition implements Single
{
	use Allows_Null;
	use Common;

	//------------------------------------------------------------------------------- $condition_left
	public Reflection_Type $condition_left;

	//------------------------------------------------------------------------------ $condition_right
	public Reflection_Type $condition_right;

	//----------------------------------------------------------------------------------- $false_type
	public Reflection_Type $false_type;

	//--------------------------------------------------------------------------------------- $is_not
	public bool $is_not;

	//------------------------------------------------------------------------------------ $true_type
	public Reflection_Type $true_type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		Reflection_Type $condition_left, bool $is_not, Reflection_Type $condition_right,
		Reflection_Type $true_type, Reflection_Type $false_type, Reflection $reflection,
		bool $allows_null
	) {
		$this->allows_null     = $allows_null;
		$this->condition_left  = $condition_left;
		$this->condition_right = $condition_right;
		$this->false_type      = $false_type;
		$this->is_not          = $is_not;
		$this->reflection      = $reflection;
		$this->true_type       = $true_type;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return '('
			. $this->condition_left . ' is ' . ($this->is_not ? 'not ' : '') . $this->condition_right
			. ' ? ' . $this->true_type
			. ' : ' . $this->false_type
			. ')';
	}

}
