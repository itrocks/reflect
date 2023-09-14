<?php
namespace ITRocks\Reflect\Tests\Data;

trait MTT
{

	//--------------------------------------------------------------- privateAbstractTraitTraitMethod
	/** @noinspection PhpUnusedPrivateMethodInspection For testing purpose */
	abstract private function privateAbstractTraitTraitMethod() : void;

	//----------------------------------------------------------------------- privateTraitTraitMethod
	/** @noinspection PhpUnusedPrivateMethodInspection For testing purpose */
	private function privateTraitTraitMethod() : void
	{}

	//--------------------------------------------------------------------- protectedTraitTraitMethod
	abstract protected function protectedTraitTraitMethod() : void;

	//------------------------------------------------------------------------ publicTraitTraitMethod
	abstract public function publicTraitTraitMethod() : void;

}
