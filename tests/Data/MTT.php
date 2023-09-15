<?php
namespace ITRocks\Reflect\Tests\Data;

trait MTT
{

	//----------------------------------------------------------------- $private_trait_trait_property
	private mixed $private_trait_trait_property = null;

	//--------------------------------------------------------------- $protected_trait_trait_property
	protected mixed $protected_trait_trait_property = null;

	//------------------------------------------------------------------ $public_trait_trait_property
	public mixed $public_trait_trait_property = null;

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
