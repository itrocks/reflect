<?php
namespace ITRocks\Reflect\Tests\Data;

trait MPT
{

	//-------------------------------------------------------------- privateAbstractParentTraitMethod
	/** @noinspection PhpUnusedPrivateMethodInspection For testing purpose */
	abstract private function privateAbstractParentTraitMethod() : void;

	//---------------------------------------------------------------------- privateParentTraitMethod
	/** @noinspection PhpUnusedPrivateMethodInspection For testing purpose */
	private function privateParentTraitMethod() : void
	{}

	//-------------------------------------------------------------------- protectedParentTraitMethod
	abstract protected function protectedParentTraitMethod() : void;

	//----------------------------------------------------------------------- publicParentTraitMethod
	abstract public function publicParentTraitMethod() : void;

}
