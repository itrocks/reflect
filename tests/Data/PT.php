<?php
namespace ITRocks\Reflect\Tests\Data;

/** PT:DC */
trait PT
{
	use TO;

	//----------------------------------------------------------------------- publicParentTraitMethod
	/** PT::publicParentTraitMethod */
	public function publicParentTraitMethod() : void
	{}

	//------------------------------------------------------------- publicParentTraitOverriddenMethod
	/** PT::publicParentTraitOverriddenMethod */
	public function publicParentTraitOverriddenMethod() : void
	{}

}
