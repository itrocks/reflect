<?php
namespace ITRocks\Reflect\Tests\Data;

class C extends P implements I
{
	use T { publicTraitOverriddenMethod as publicRenamedTraitOverriddenMethod; }

	//--------------------------------------------------------------------------- privateParentMethod
	public function privateParentMethod() : void
	{}

	//----------------------------------------------------------------------------- publicClassMethod
	/** C::publicClassMethod */
	public function publicClassMethod() : void
	{}

	//------------------------------------------------------------------------- publicInterfaceMethod
	/** C::publicInterfaceMethod */
	public function publicInterfaceMethod() : void
	{}

	//------------------------------------------------------------- publicParentTraitOverriddenMethod
	/** C::publicParentTraitOverriddenMethod */
	public function publicParentTraitOverriddenMethod() : void
	{}

	//-------------------------------------------------------------------- publicRootOverriddenMethod
	public function publicRootOverriddenMethod() : void
	{}

	//------------------------------------------------------------------- publicTraitOverriddenMethod
	/** C::publicTraitOverriddenMethod */
	public function publicTraitOverriddenMethod() : void
	{}

	//--------------------------------------------------------------------------------- withParameter
	public function withParameter(string $parameter) : string
	{
		return $parameter;
	}

}
