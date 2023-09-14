<?php
namespace ITRocks\Reflect\Tests\Data;

abstract class MC extends MP implements MI
{
	use MT;

	//-------------------------------------------------------------------- privateAbstractTraitMethod
	/**
	 * @noinspection PhpUnusedPrivateMethodInspection For testing purpose
	 * @phpstan-ignore-next-line Unused: For testing purpose
	 */
	private function privateAbstractTraitMethod() : void
	{}

	//--------------------------------------------------------------- privateAbstractTraitTraitMethod
	/**
	 * @noinspection PhpUnusedPrivateMethodInspection For testing purpose
	 * @phpstan-ignore-next-line Unused: For testing purpose
	 */
	private function privateAbstractTraitTraitMethod() : void
	{}

	//---------------------------------------------------------------------------- privateClassMethod
	/**
	 * @noinspection PhpUnusedPrivateMethodInspection For testing purpose
	 * @phpstan-ignore-next-line Unused: For testing purpose
	 */
	private function privateClassMethod() : void
	{}

	//-------------------------------------------------------------------------- protectedClassMethod
	abstract protected function protectedClassMethod() : void;

	//----------------------------------------------------------------------------- publicClassMethod
	abstract public function publicClassMethod() : void;

}
