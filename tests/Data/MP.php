<?php
namespace ITRocks\Reflect\Tests\Data;

abstract class MP
{
	use MPT;

	//-------------------------------------------------------------- privateAbstractParentTraitMethod
	/**
	 * @noinspection PhpUnusedPrivateMethodInspection For testing purpose
	 * @phpstan-ignore-next-line Unused: For testing purpose
	 */
	private function privateAbstractParentTraitMethod() : void
	{}

	//--------------------------------------------------------------------------- privateParentMethod
	/**
	 * @noinspection PhpUnusedPrivateMethodInspection For testing purpose
	 * @phpstan-ignore-next-line Unused: For testing purpose
	 */
	private function privateParentMethod() : void
	{}

	//------------------------------------------------------------------------- protectedParentMethod
	abstract protected function protectedParentMethod() : void;

	//---------------------------------------------------------------------------- publicParentMethod
	abstract public function publicParentMethod() : void;

}
