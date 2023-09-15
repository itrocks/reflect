<?php
namespace ITRocks\Reflect\Tests\Data;

abstract class MP
{
	use MPT;

	//---------------------------------------------------------------------- $private_parent_property
	/** @phpstan-ignore-next-line Never read: For testing purpose */
	private mixed $private_parent_property = null;

	//---------------------------------------------------------------- $private_parent_trait_property
	/** @phpstan-ignore-next-line Never read: For testing purpose */
	private mixed $private_parent_trait_property = null;

	//-------------------------------------------------------------------- $protected_parent_property
	protected mixed $protected_parent_property = null;

	//------------------------------------------------------------ $public_parent_overridden_property
	/** MP:parent_overridden_property */
	public mixed $public_parent_overridden_property = null;

	//----------------------------------------------------------------------- $public_parent_property
	/** MP:parent_property */
	public mixed $public_parent_property = null;

	//-------------------------------------------------------------------------- overrideParentMethod
	public function overrideParentMethod() : int
	{
		return 1;
	}

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
