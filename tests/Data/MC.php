<?php
namespace ITRocks\Reflect\Tests\Data;

abstract class MC extends MP implements MI
{
	use MT;

	//----------------------------------------------------------------------- $private_class_property
	/** @phpstan-ignore-next-line Never read: For testing purpose */
	private mixed $private_class_property = null;

	//----------------------------------------------------------------- $private_trait_trait_property
	/** @phpstan-ignore-next-line Never read: For testing purpose */
	private mixed $private_trait_trait_property = null;

	//--------------------------------------------------------------------- $protected_class_property
	protected mixed $protected_class_property = null;

	//------------------------------------------------------------------------ $public_class_property
	/** MC:class_property */
	public mixed $public_class_property = null;

	//------------------------------------------------------------ $public_parent_overridden_property
	/** MC:parent_overridden_property */
	public mixed $public_parent_overridden_property = null;

	//------------------------------------------------------ $public_parent_trait_overridden_property
	/** MC:parent_trait_overridden_property */
	public mixed $public_parent_trait_overridden_property = null;

	//------------------------------------------------------------- $public_trait_overridden_property
	/** MC:trait_overridden_property */
	public mixed $public_trait_overridden_property = null;

	//------------------------------------------------------- $public_trait_trait_overridden_property
	/** MC:trait_trait_overridden_property */
	public mixed $public_trait_trait_overridden_property = null;

	//-------------------------------------------------------------------------- overrideParentMethod
	public function overrideParentMethod() : int
	{
		return parent::overrideParentMethod() + 1;
	}

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
