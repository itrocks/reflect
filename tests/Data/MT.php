<?php
namespace ITRocks\Reflect\Tests\Data;

trait MT
{
	use MTT;

	//-------------------------------------------------------------------- privateAbstractTraitMethod
	/** @noinspection PhpUnusedPrivateMethodInspection For testing purpose */
	abstract private function privateAbstractTraitMethod() : void;

	//---------------------------------------------------------------------------- privateTraitMethod
	/** @noinspection PhpUnusedPrivateMethodInspection For testing purpose */
	private function privateTraitMethod() : void
	{}

	//-------------------------------------------------------------------------- protectedTraitMethod
	abstract protected function protectedTraitMethod() : void;

	//----------------------------------------------------------------------------- publicTraitMethod
	abstract public function publicTraitMethod() : void;

}
