<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\UrlGenerator;
use Aviat\Ion\Config;
use Aviat\Ion\Exception\DoubleRenderException;

class UrlGeneratorTest extends AnimeClientTestCase {

	public function assetUrlProvider()
	{
		return [
			'single argument' => [
				'args' => [
					'images'
				],
				'expected' => 'https://localhost/assets/images',
			],
			'multiple arguments' => [
				'args' => [
					'images', 'anime', 'foo.png'
				],
				'expected' => 'https://localhost/assets/images/anime/foo.png'
			]
		];
	}

	/**
	 * @dataProvider assetUrlProvider
	 */
	public function testAssetUrl($args, $expected)
	{
		$urlGenerator = new UrlGenerator($this->container);

		$result = $urlGenerator->assetUrl(...$args);
		$this->assertEquals($expected, $result);
	}

	public function testDefaultUrlInvalidType(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid default type: 'foo'");

		$urlGenerator = new UrlGenerator($this->container);
		$url = $urlGenerator->defaultUrl('foo');

	}
}