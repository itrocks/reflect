<?php /** phpcs:ignoreFile For tests */
namespace ITRocks\Reflect\Tests\Data;

trait Trait_3
{

	/** 2 */
	public mixed $property = 'default';

}

trait Trait_2
{
	use Trait_3;

	/** 1 */
	public mixed $property = 'default';

}

trait Trait_1
{
	use Trait_2;

	/** 2 */
	public mixed $property = 'default';

}

class Trait_Property_Override
{
	use Trait_1;

	/** 1 */
	public mixed $property = 'default';

}

class Limited
{
	use Trait_3;

	/** 2 */
	public mixed $property = 'default';

	/**
	 * A
	 * @phpstan-ignore-next-line For testing
	 */
	private mixed $private = 'default';

	/** @phpstan-ignore-next-line For testing */
	private mixed $private2 = 'default';

}

class More extends Limited
{

	/**
	 * B
	 * @noinspection PhpUnusedPrivateFieldInspection
	 * @phpstan-ignore-next-line For testing
	 */
	private mixed $private = 'default';

	public mixed $private2 = 'default';

}
