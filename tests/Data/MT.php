<?php
namespace ITRocks\Reflect\Tests\Data;

trait MT
{
	use MTT;

	//----------------------------------------------------------------------- $private_trait_property
	private mixed $private_trait_property = null;

	//--------------------------------------------------------------------- $protected_trait_property
	protected mixed $protected_trait_property = null;

	//------------------------------------------------------------------------ $public_trait_property
	public mixed $public_trait_property = null;

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
