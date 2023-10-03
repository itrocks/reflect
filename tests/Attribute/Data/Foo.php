<?php
/** @noinspection PhpUndefinedClassInspection */
/** phpcs:ignoreFile */
namespace ITRocks\Reflect\Tests\Attribute\Data;

use Attribute;
use ITRocks\Reflect\Attribute\Has_Default;
use ITRocks\Reflect\Attribute\Inheritable;
use ITRocks\Reflect\Attribute\Override;
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
#[Attribute(Attribute::TARGET_PROPERTY)]
class Inheritable_Property_Child extends Inheritable_Property {}

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL), Inheritable]
class Inheritable_Repeatable {
	function __construct(public string $class, public string $interface_trait = '') {
		if ($this->interface_trait === '') {
			$this->interface_trait = $class;
		}
	}
}

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Repeatable_Class { use Repeatable; function __construct(public string $id) {} }
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Repeatable_Method { function __construct(public string $id) {} }
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class Repeatable_Property { function __construct(public string $id) {} }

#[Inheritable_Repeatable('CII'), Repeatable_Class('CII')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OC', 'OCII'))]
interface CII {}

#[Inheritable_Repeatable('CIIB'), Repeatable_Class('CIIB')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OC', 'OCIIB'))]
interface CIIB {}

#[Inheritable_Repeatable('CI'), Repeatable_Class('CI')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OC', 'OCI'))]
interface CI extends CII, CIIB {}

#[Inheritable_Repeatable('CIB'), Repeatable_Class('CIB')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OC', 'OCIB'))]
interface CIB {}

#[Inheritable_Repeatable('PI'), Repeatable_Class('PI')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OP', 'OPI'))]
#[Override('inheritable_with_break', new Inheritable_Repeatable('OP', 'OPI'))]
interface PI {}

#[Inheritable_Class, Inheritable_Repeatable('PTT'), Repeatable_Class('PTT')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OP', 'OPTT'))]
trait PTT {

	#[Inheritable_Method]
	public function ptt() : void { }

	#[All_Targets, Inheritable_Property]
	public int $inheritable = 0;

	#[Inheritable_Property]
	#[Inheritable_Repeatable('P', 'PTT'), Repeatable_Property('PTT')]
	public int $inheritable_repeatable = 0;

	#[Inheritable_Property]
	public int $ptt = 0;

}

#[Inheritable_Repeatable('PT'), Repeatable_Class('PT')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OP', 'OPT'))]
#[Override('inheritable_with_break', new Inheritable_Repeatable('OP', 'OPT'))]
trait PT {
	use PTT;

	#[Inheritable_Property]
	public int $inheritable = 0;

	#[Inheritable_Repeatable('P', 'PT'), Repeatable_Property('PT')]
	public int $inheritable_repeatable = 0;

	#[Inheritable_Repeatable('P', 'PT')]
	public int $inheritable_with_break = 0;

	public int $not_same_attribute_count = 0;

	#[Inheritable_Property]
	public int $not_same_attribute_name = 0;

	#[All_Targets, Simple_Property(1)]
	public int $same_attributes = 0;

}

#[Override('inheritable_repeatable', new Inheritable_Repeatable('OR'))]
#[Override('inheritable_with_break', new Inheritable_Repeatable('broken'))]
class R {

	/** @noinspection PhpUnusedPrivateFieldInspection */
	#[Inheritable_Repeatable('R'), Repeatable_Property('R')]
	/** @phpstan-ignore-next-line for testing purpose */
	private int $inheritable_repeatable = 0;

}

/** @phpstan-ignore-next-line with_no_class */
#[Inheritable_Repeatable('P'), Repeatable_Class('P'), with_no_class(5)]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OP'))]
#[Override('inheritable_with_break', new Inheritable_Repeatable('OP'))]
class P extends R implements PI {
	use PT;

	/** @phpstan-ignore-next-line with_no_class */
	#[Inheritable_Repeatable('P'), Repeatable_Property('P'), with_no_class(15)]
	public int $inheritable_repeatable = 0;

	#[All_Targets]
	public int $not_same_attribute_count = 0;

	#[All_Targets]
	public int $not_same_attribute_name = 0;

	#[Inheritable_Property]
	public int $override_instance_of = 0;

	public int $override_name = 0;

	#[All_Targets, Simple_Property(1)]
	public int $same_attributes = 0;

}

#[Inheritable_Repeatable('CT')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OC', 'OCT'))]
trait CT {

	#[Inheritable_Repeatable('C', 'CT')]
	public int $inheritable_repeatable = 0;

}

#[All_Targets]
#[Inheritable_Repeatable('C1'), Inheritable_Repeatable('C2')]
#[Override('inheritable_repeatable', new Inheritable_Repeatable('OC1'), new Inheritable_Repeatable('OC2'))]
#[Override('override_name', Inheritable_Property::class)]
#[Override('override_instance_of', Inheritable_Property_Child::class)]
#[Repeatable_Class('C1'), Repeatable_Class('C2'), Simple_Class(12)]
class C extends P implements CI, CIB {
	use CT;

	#[Inheritable_Property]
	public int $alone = 0;

	public int $inheritable = 0;

	#[All_Targets]
	#[Inheritable_Repeatable('C1'), Inheritable_Repeatable('C2')]
	#[Repeatable_Property('C1'), Repeatable_Property('C2'), Simple_Property(12)]
	public int $inheritable_repeatable = 0;

	#[Inheritable_Repeatable('C')]
	public int $inheritable_with_break = 0;

}
