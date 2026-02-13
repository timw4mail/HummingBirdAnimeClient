<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Enum;

use Aviat\AnimeClient\Enum\SyncAction;
use PHPUnit\Framework\TestCase;

final class SyncActionTest extends TestCase
{
	public function testEnumValues(): void
	{
		$this->assertEquals('create', SyncAction::CREATE->value);
		$this->assertEquals('update', SyncAction::UPDATE->value);
		$this->assertEquals('delete', SyncAction::DELETE->value);
	}

	public function testGetConstList(): void
	{
		$expected = ['CREATE', 'UPDATE', 'DELETE'];
		$this->assertEquals($expected, SyncAction::getConstList());
	}
}
