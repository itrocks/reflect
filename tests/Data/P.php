<?php
namespace ITRocks\Reflect\Tests\Data;

abstract class P extends R implements I, PI
{
	use PT;

	//------------------------------------------------------------------- publicParentInterfaceMethod
	/** P::publicParentInterfaceMethod */
	public function publicParentInterfaceMethod() : void
	{}

	//---------------------------------------------------------------------------- publicParentMethod
	/** P::publicParentMethod */
	public function publicParentMethod() : void
	{}

}
