<?php declare(strict_types=1);

namespace Aviat\Ion\Tests;

use Aviat\Ion\ImageBuilder;

final class ImageBuilderTest extends IonTestCase
{
	public function testImageBuilder(): void
	{
		$path = __DIR__ . '/test_data/test_image';
		$img = ImageBuilder::new(100, 100)
			->enableAlphaBlending(true)
			->addBackgroundColor(255, 255, 255)
			->addCenteredText('Test', 64, 64, 64);

		$this->assertTrue($img->savePng($path . '.png'));
		// $this->assertTrue($img->saveWebp($path . '.webp'));
		$this->assertTrue($img->saveJpg($path . '.jpg'));
		$this->assertTrue($img->saveGif($path . '.gif'));

		$this->assertFileExists($path . '.png');
		// $this->assertFileExists($path . '.webp');
		$this->assertFileExists($path . '.jpg');
		$this->assertFileExists($path . '.gif');

		unlink($path . '.png');
		// unlink($path . '.webp');
		unlink($path . '.jpg');
		unlink($path . '.gif');
	}

	public function testAddBackgroundColor(): void
	{
		$img = ImageBuilder::new(10, 10)
			->addBackgroundColor(100, 100, 100, 127);
		$this->assertIsObject($img);
	}

	public function testAddCenteredText(): void
	{
		$img = ImageBuilder::new(100, 100)
			->setFontSize(5)
			->addCenteredText('Foo', 0, 0, 0, 50);
		$this->assertIsObject($img);
	}
}
