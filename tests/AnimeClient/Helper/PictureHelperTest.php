<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\Helper;

use Aviat\AnimeClient\Helper\Picture as PictureHelper;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
final class PictureHelperTest extends AnimeClientTestCase
{
	#[DataProvider('dataPictureCase')]
	public function testPictureHelper(array $params): void
	{
		$helper = new PictureHelper();
		$helper->setContainer($this->container);

		$actual = $helper(...$params);

		$this->assertMatchesSnapshot($actual);
	}

	#[DataProvider('dataSimpleImageCase')]
	public function testSimpleImage(string $ext, bool $isSimple, string|null $fallbackExt): void
	{
		$fallbackExt ??= 'jpg';

		$helper = new PictureHelper();
		$helper->setContainer($this->container);

		$url = "https://example.com/image.{$ext}";
		$actual = $helper($url, $fallbackExt);

		$actuallySimple = ! str_contains($actual, '<picture');

		$this->assertSame($isSimple, $actuallySimple);
	}

	public function testSimpleImageByFallback(): void
	{
		$helper = new PictureHelper();
		$helper->setContainer($this->container);

		$actual = $helper('foo.svg', 'svg');

		$this->assertTrue(! str_contains($actual, '<picture'));
	}

	public static function dataPictureCase(): array
	{
		return [
			'Full AVIF URL' => [
				'params' => [
					'https://www.example.com/image.avif',
				],
			],
			'Full webp URL' => [
				'params' => [
					'https://www.example.com/image.webp',
				],
			],
			'Partial webp URL' => [
				'params' => [
					'images/anime/15424.webp',
				],
			],
			'bmp with gif fallback' => [
				'params' => [
					'images/avatar/25.bmp',
					'gif',
				],
			],
			'webp placeholder image' => [
				'params' => [
					'images/placeholder.webp',
				],
			],
			'png placeholder image' => [
				'params' => [
					'images/placeholder.png',
				],
			],
			'jpeg2000' => [
				'params' => [
					'images/foo.jpf',
				],
			],
			'svg with png fallback and lots of attributes' => [
				'params' => [
					'images/example.svg',
					'png',
					['width' => 200, 'height' => 300],
					['alt' => 'Example text'],
				],
			],
			'simple image with attributes' => [
				'params' => [
					'images/foo.jpg',
					'jpg',
					[],
					['width' => 200, 'height' => 200, 'alt' => 'should exist'],
				],
			],
		];
	}

	public static function dataSimpleImageCase(): array
	{
		return [
			'avif' => [
				'ext' => 'avif',
				'isSimple' => false,
				'fallbackExt' => 'jpf',
			],
			'apng' => [
				'ext' => 'apng',
				'isSimple' => false,
				'fallbackExt' => null,
			],
			'gif' => [
				'ext' => 'gif',
				'isSimple' => true,
				'fallbackExt' => null,
			],
			'jpg' => [
				'ext' => 'jpg',
				'isSimple' => true,
				'fallbackExt' => null,
			],
			'jpeg' => [
				'ext' => 'jpeg',
				'isSimple' => true,
				'fallbackExt' => null,
			],
			'png' => [
				'ext' => 'png',
				'isSimple' => true,
				'fallbackExt' => null,
			],
			'webp' => [
				'ext' => 'webp',
				'isSimple' => false,
				'fallbackExt' => null,
			],
		];
	}
}
