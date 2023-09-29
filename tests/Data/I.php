<?php
namespace ITRocks\Reflect\Tests\Data;

/** I:DC */
interface I extends II
{

	//------------------------------------------------------------------------- publicInterfaceMethod
	/** I::publicInterfaceMethod */
	public function publicInterfaceMethod() : void;

	//------------------------------------------------------ publicOverridePrivateMethodWithPrototype
	public function publicOverridePrivateMethodWithPrototype() : void;

	//----------------------------------------------------------------------------- withoutDocComment
	public function withoutDocComment() : void;

}
