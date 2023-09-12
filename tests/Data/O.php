<?php
namespace ITRocks\Reflect\Tests\Data;

class O extends P implements I, OI
{
	use T, OT;

	//------------------------------------------------------------------------- publicInterfaceMethod
	/** O::publicInterfaceMethod */
	public function publicInterfaceMethod() : void
	{}

	//------------------------------------------------------------------- publicParentInterfaceMethod
	/** O::publicParentInterfaceMethod */
	public function publicParentInterfaceMethod() : void
	{
		parent::publicParentInterfaceMethod();
	}

}
