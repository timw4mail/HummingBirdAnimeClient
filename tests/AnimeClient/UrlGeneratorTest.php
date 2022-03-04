<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\UrlGenerator;
use InvalidArgumentException;

/**
 * @internal
 */
final class UrlGeneratorTest extends AnimeClientTestCase
{
	public function assetUrlProvider()
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

	/**
	 * @dataProvider assetUrlProvider
	 * @param mixed $args
	 * @param mixed $expected
	 */
	public function testAssetUrl($args, $expected)
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
