<?php
namespace ITRocks\Reflect\Tests\Data;

trait MPT
{

	//---------------------------------------------------------------- $private_parent_trait_property
	private mixed $private_parent_trait_property = null;

	//-------------------------------------------------------------- $protected_parent_trait_property
	protected mixed $protected_parent_trait_property = null;

	//----------------------------------------------------------------- $public_parent_trait_property
	/** MPT:parent_trait_property */
	protected mixed $public_parent_trait_property = null;

	//------------------------------------------------------ $public_parent_trait_overridden_property
	/** MPT:parent_trait_overridden_property */
	public mixed $public_parent_trait_overridden_property = null;
	
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
