<?php
namespace ITRocks\Reflect\Tests\Data;

/** P:DC */
abstract class P extends R implements I, PI
{
	use PT;

	//--------------------------------------------------------------------------- privateParentMethod
	/**
	 * @noinspection PhpUnusedPrivateMethodInspection For testing purpose
	 * @phpstan-ignore-next-line unused, but we don't care
	 */
	private function privateParentMethod() : void
	{}

	//------------------------------------------------------------------- publicOverridePrivateMethod
	public function publicOverridePrivateMethod() : void
	{}

	//------------------------------------------------------ publicOverridePrivateMethodWithPrototype
	public function publicOverridePrivateMethodWithPrototype() : void
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
