<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Enum;

use Aviat\AnimeClient\Enum\API;
use PHPUnit\Framework\TestCase;

final class APITest extends TestCase
{
	public function testEnumValues(): void
	{
		$this->assertEquals('anilist', API::ANILIST->value);
		$this->assertEquals('kitsu', API::KITSU->value);
	}

	public function testGetConstList(): void
	{
		$expected = ['ANILIST', 'KITSU'];
		$this->assertEquals($expected, API::getConstList());
	}
}
