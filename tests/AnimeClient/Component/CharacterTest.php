<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Component;

use Aviat\AnimeClient\Component\Character;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

final class CharacterTest extends AnimeClientTestCase
{
	public function testInvoke(): void
	{
		$component = new Character();
		$component->setContainer($this->container);

		$output = $component('Test Character', '/character/test', 'test.jpg');
		$this->assertStringContainsString('Test Character', $output);
		$this->assertStringContainsString('/character/test', $output);
		$this->assertStringContainsString('test.jpg', $output);
	}
}
