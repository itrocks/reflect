<?php
namespace ITRocks\Reflect\Tests\Data;

/** R:DC */
class R
{

	//------------------------------------------------------------------- publicOverridePrivateMethod
	/**
	 * @noinspection PhpUnusedPrivateMethodInspection For testing purpose
	 * @phpstan-ignore-next-line unused, but we don't care
	 */
	private function publicOverridePrivateMethod() : void
	{}

	//------------------------------------------------------ publicOverridePrivateMethodWithPrototype
	/**
	 * @noinspection PhpUnusedPrivateMethodInspection For testing purpose
	 * @phpstan-ignore-next-line unused, but we don't care
	 */
	private function publicOverridePrivateMethodWithPrototype() : void
	{}

	//------------------------------------------------------------------------------ publicRootMethod
	public function publicRootMethod() : void
	{}

	//-------------------------------------------------------------------- publicRootOverriddenMethod
	public function publicRootOverriddenMethod() : void
	{}

}
