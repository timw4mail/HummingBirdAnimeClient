<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\UrlGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
final class UrlGeneratorTest extends AnimeClientTestCase
{
	public static function assetUrlProvider(): array
	{
		return [
			'single argument' => [
				'args' => [
					'images',
				],
				'expected' => 'https://localhost/assets/images',
			],
			'multiple arguments' => [
				'args' => [
					'images', 'anime', 'foo.png',
				],
				'expected' => 'https://localhost/assets/images/anime/foo.png',
			],
		];
	}

	#[DataProvider('assetUrlProvider')]
	public function testAssetUrl(mixed $args, string $expected): void
	{
		$urlGenerator = new UrlGenerator($this->container);

		$result = $urlGenerator->assetUrl(...$args);
		$this->assertSame($expected, $result);
	}

	public function testDefaultUrlInvalidType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid default type: 'foo'");

		$urlGenerator = new UrlGenerator($this->container);
		$url = $urlGenerator->defaultUrl('foo');
	}
}
