<?php
namespace ITRocks\Reflect\Tests\Data;

class C extends P implements I
{
	use T;

	//----------------------------------------------------------------------------- publicClassMethod
	public function publicClassMethod() : void
	{}

	//------------------------------------------------------------------------- publicInterfaceMethod
	public function publicInterfaceMethod() : void
	{}

}
