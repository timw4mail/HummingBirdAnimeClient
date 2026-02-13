<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Enum;

use Aviat\AnimeClient\Enum\MediaType;
use PHPUnit\Framework\TestCase;

final class MediaTypeTest extends TestCase
{
	public function testEnumValues(): void
	{
		$this->assertEquals('anime', MediaType::ANIME->value);
		$this->assertEquals('drama', MediaType::DRAMA->value);
		$this->assertEquals('manga', MediaType::MANGA->value);
	}

	public function testGetConstList(): void
	{
		$expected = ['ANIME', 'DRAMA', 'MANGA'];
		$this->assertEquals($expected, MediaType::getConstList());
	}

	public function testIsValid(): void
	{
		$this->assertTrue(MediaType::isValid('ANIME'));
		$this->assertFalse(MediaType::isValid('foo'));
	}

	public function testInvoke(): void
	{
		$anime = MediaType::ANIME;
		$this->assertEquals('anime', $anime());
	}
}
