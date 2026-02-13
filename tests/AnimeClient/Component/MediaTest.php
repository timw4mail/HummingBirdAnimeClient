<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Component;

use Aviat\AnimeClient\Component\Media;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

final class MediaTest extends AnimeClientTestCase
{
	public function testInvoke(): void
	{
		$component = new Media();
		$component->setContainer($this->container);

		$output = $component(['Title 1', 'Title 2'], '/media/test', 'test.jpg');
		$this->assertStringContainsString('Title 1', $output);
		$this->assertStringContainsString('Title 2', $output);
		$this->assertStringContainsString('/media/test', $output);
		$this->assertStringContainsString('test.jpg', $output);
	}
}
