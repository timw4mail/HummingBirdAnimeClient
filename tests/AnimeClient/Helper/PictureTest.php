<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Helper;

use Aviat\AnimeClient\Helper\Picture;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

/**
 * @internal
 */
final class PictureTest extends AnimeClientTestCase
{
	public function testPicture(): void
	{
		$picture = new Picture();
		$picture->setContainer($this->container);

		$html = $picture('test.webp', 'jpg');
		$this->assertStringContainsString('<picture', $html);
		$this->assertStringContainsString('test.webp', $html);
		$this->assertStringContainsString('test.jpg', $html);
	}

	public function testSimpleImg(): void
	{
		$picture = new Picture();
		$picture->setContainer($this->container);

		$html = $picture('test.jpg', 'jpg');
		$this->assertStringNotContainsString('<picture', $html);
		$this->assertStringContainsString('<img', $html);
	}

	public function testNonSimpleImg(): void
	{
		$picture = new Picture();
		$picture->setContainer($this->container);

		$html = $picture('test.tiff', 'webp');
		$this->assertStringContainsString('<picture', $html);
	}

	public function testMimeTypes(): void
	{
		$picture = new Picture();
		$picture->setContainer($this->container);

		$simpleTypes = [
			'gif',
			'jpeg',
			'jpg',
			'png',
		];

		foreach ($simpleTypes as $ext)
		{
			$html = $picture("test.{$ext}", 'jpg');
			$this->assertStringNotContainsString('<picture', $html);
			$this->assertStringContainsString('<img', $html);
		}

		$complexTypes = [
			'avif' => 'image/avif',
			'apng' => 'image/vnd.mozilla.apng',
			'bmp' => 'image/bmp',
			'ico' => 'image/x-icon',
			'svg' => 'image/svg+xml',
			'webp' => 'image/webp',
		];

		foreach ($complexTypes as $ext => $mime)
		{
			$html = $picture("test.{$ext}", 'jpg');
			$this->assertStringContainsString('<picture', $html);
			$this->assertStringContainsString($mime, $html);
		}
	}

	public function testPlaceholder(): void
	{
		$picture = new Picture();
		$picture->setContainer($this->container);

		$html = $picture('placeholder.webp', 'jpg');
		$this->assertStringContainsString('image/png', $html);
	}
}
