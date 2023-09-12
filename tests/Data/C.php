<?php
namespace ITRocks\Reflect\Tests\Data;

class C extends P implements I
{
	use T { publicTraitOverriddenMethod as publicRenamedTraitOverriddenMethod; }

	//----------------------------------------------------------------------------- publicClassMethod
	/** C::publicClassMethod */
	public function publicClassMethod() : void
	{}

	//------------------------------------------------------------------------- publicInterfaceMethod
	/** C::publicInterfaceMethod */
	public function publicInterfaceMethod() : void
	{}

	//------------------------------------------------------------- publicParentTraitOverriddenMethod
	public function publicParentTraitOverriddenMethod() : void
	{}

	//------------------------------------------------------------------- publicTraitOverriddenMethod
	/** C::publicTraitOverriddenMethod */
	public function publicTraitOverriddenMethod() : void
	{}

}
