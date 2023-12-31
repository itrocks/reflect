<?php
namespace ITRocks\Reflect\Tests\Data;

/** C:DC */
class C extends P implements I
{
	use T { publicTraitOverriddenMethod as publicRenamedTraitOverriddenMethod; }
	use TO;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{}

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
	public function withParameter(string &$parameter = 'default') : string
	{
		$parameter .= '-';
		return $parameter;
	}

}
