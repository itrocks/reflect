<?php
/** @noinspection PhpUndefinedClassInspection */
/** phpcs:ignoreFile */
namespace ITRocks\Reflect\Tests\Attribute\Data;

use Attribute;
use ITRocks\Reflect\Attribute\Has_Default;
use ITRocks\Reflect\Attribute\Inheritable;
use ITRocks\Reflect\Attribute\Repeatable;
use ITRocks\Reflect\Attribute\Single;
use with_no_class;

#[Attribute]
class Foo
{

	//------------------------------------------------------------------------------------ $arguments
	/** @var array{string,int} */
	protected array $arguments;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $argument1, int $argument2)
	{
		$this->arguments = [$argument1, $argument2];
	}

}

#[Attribute]
class All_Targets { }

#[Attribute(Attribute::TARGET_CLASS)]
class Simple_Class { use Single; function __construct(public int $number) {} }
#[Attribute(Attribute::TARGET_METHOD)]
class Simple_Method { function __construct(public int $number) {} }
#[Attribute(Attribute::TARGET_PROPERTY)]
class Simple_Property { function __construct(public int $number) {} }

#[Attribute(Attribute::TARGET_CLASS), Has_Default('default')]
class Has_Default_Class { public function __construct(public string $value) {} }
#[Attribute(Attribute::TARGET_METHOD), Has_Default('default')]
class Has_Default_Method { public function __construct(public string $value) {} }
#[Attribute(Attribute::TARGET_PROPERTY), Has_Default('default')]
class Has_Default_Property { public function __construct(public string $value) {} }

#[Attribute(Attribute::TARGET_CLASS), Inheritable]
class Inheritable_Class { use Single; }
#[Attribute(Attribute::TARGET_METHOD), Inheritable]
class Inheritable_Method { use Single; }
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Inheritable_Property { use Single; }

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS), Inheritable]
class Inheritable_Repeatable_Class { function __construct(public string $id) {} }
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD), Inheritable]
class Inheritable_Repeatable_Method { function __construct(public string $id) {} }
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY), Inheritable]
class Inheritable_Repeatable_Property { function __construct(public string $id) {} }

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Repeatable_Class { use Repeatable; function __construct(public string $id) {} }
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Repeatable_Method { function __construct(public string $id) {} }
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class Repeatable_Property { function __construct(public string $id) {} }

#[Inheritable_Repeatable_Class('CII'), Repeatable_Class('CII')]
interface CII {}

#[Inheritable_Repeatable_Class('CII2'), Repeatable_Class('CII2')]
interface CII2 {}

#[Inheritable_Repeatable_Class('CI'), Repeatable_Class('CI')]
interface CI extends CII, CII2 {}

#[Inheritable_Repeatable_Class('CI2'), Repeatable_Class('CI2')]
interface CI2 {}

#[Inheritable_Repeatable_Class('PI'), Repeatable_Class('PI')]
interface PI {}

#[Inheritable_Class, Inheritable_Repeatable_Class('PTT'), Repeatable_Class('PTT')]
trait PTT {

	#[Inheritable_Method]
	public function ptt() : void { }

	#[Inheritable_Property, Inheritable_Repeatable_Property('PTT'), Repeatable_Property('PTT')]
	public int $inheritable_repeatable = 0;

	#[Inheritable_Property]
	public int $ptt = 0;

}

#[Inheritable_Repeatable_Class('PT'), Repeatable_Class('PT')]
trait PT {
	use PTT;

	#[Inheritable_Repeatable_Property('PT'), Repeatable_Property('PT')]
	public int $inheritable_repeatable = 0;

	public int $not_same_attribute_count = 0;

	#[Inheritable_Property]
	public int $not_same_attribute_name = 0;

	#[All_Targets, Simple_Property(1)]
	public int $same_attributes = 0;

}

class R {

	/** @noinspection PhpUnusedPrivateFieldInspection */
	#[Inheritable_Repeatable_Property('R'), Repeatable_Property('R')]
	/** @phpstan-ignore-next-line for testing purpose */
	private int $inheritable_repeatable = 0;

}

/** @phpstan-ignore-next-line with_no_class */
#[Inheritable_Repeatable_Class('P'), Repeatable_Class('P'), with_no_class(5)]
class P extends R implements PI {
	use PT;

	/** @phpstan-ignore-next-line with_no_class */
	#[Inheritable_Repeatable_Property('P'), Repeatable_Property('P'), with_no_class(15)]
	public int $inheritable_repeatable = 0;

	#[All_Targets]
	public int $not_same_attribute_count = 0;

	#[All_Targets]
	public int $not_same_attribute_name = 0;

	#[All_Targets, Simple_Property(1)]
	public int $same_attributes = 0;

}

#[Inheritable_Repeatable_Class('CT')]
trait CT {

	#[Inheritable_Repeatable_Property('CT')]
	public int $inheritable_repeatable = 0;

}

#[All_Targets]
#[Inheritable_Repeatable_Class('C1'), Inheritable_Repeatable_Class('C2')]
#[Repeatable_Class('C1'), Repeatable_Class('C2'), Simple_Class(12)]
class C extends P implements CI, CI2 {
	use CT;

	#[All_Targets]
	#[Inheritable_Repeatable_Property('C1'), Inheritable_Repeatable_Property('C2')]
	#[Repeatable_Property('C1'), Repeatable_Property('C2'), Simple_Property(12)]
	public int $inheritable_repeatable = 0;

}
