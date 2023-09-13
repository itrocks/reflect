<?php
namespace ITRocks\Reflect\Tests\Data;

abstract class P extends R implements I, PI
{
	use PT;

	//--------------------------------------------------------------------------- privateParentMethod
	/** @phpstan-ignore-next-line unused, but we don't care */
	private function privateParentMethod() : void
	{}

	//------------------------------------------------------------------- publicParentInterfaceMethod
	/** P::publicParentInterfaceMethod */
	public function publicParentInterfaceMethod() : void
	{}

	//---------------------------------------------------------------------------- publicParentMethod
	/** P::publicParentMethod */
	public function publicParentMethod() : void
	{}

	//----------------------------------------------------------------------------- withoutDocComment
	public function withoutDocComment() : void
	{}

}
