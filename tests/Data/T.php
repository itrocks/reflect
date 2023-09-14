<?php
namespace ITRocks\Reflect\Tests\Data;

/** T:DC */
trait T
{
	use TT;

	//----------------------------------------------------------------------------- publicTraitMethod
	/** T::publicTraitMethod */
	public function publicTraitMethod() : void
	{}

	//------------------------------------------------------------------- publicTraitOverriddenMethod
	/** T::publicTraitOverriddenMethod */
	public function publicTraitOverriddenMethod() : void
	{}

}
