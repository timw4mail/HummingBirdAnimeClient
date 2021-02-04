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

namespace Aviat\AnimeClient\Tests\Helper;

use Aviat\AnimeClient\Helper\Picture as PictureHelper;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

class PictureHelperTest extends AnimeClientTestCase {
	/**
	 * @dataProvider dataPictureCase
	 */
	public function testPictureHelper($params, $expected = NULL)
	{
		$helper = new PictureHelper();
		$helper->setContainer($this->container);

		$actual = $helper(...$params);

		if ($expected === NULL)
		{
			$this->assertMatchesSnapshot($actual);
		}
		else
		{
			$this->assertEquals($expected, $actual);
		}
	}

	/**
	 * @dataProvider dataSimpleImageCase
	 */
	public function testSimpleImage(string $ext, bool $isSimple)
	{
		$helper = new PictureHelper();
		$helper->setContainer($this->container);

		$url = "https://example.com/image.{$ext}";
		$actual = $helper($url);

		$actuallySimple = strpos($actual, '<picture') === FALSE;

		$this->assertEquals($isSimple, $actuallySimple);
	}

	public function testSimpleImageByFallback()
	{
		$helper = new PictureHelper();
		$helper->setContainer($this->container);

		$actual = $helper("foo.svg", 'svg');

		$this->assertTrue(strpos($actual, '<picture') === FALSE);
	}

	public function dataPictureCase()
	{
		return [
			'Full webp URL' => [
				'params' => [
					'https://www.example.com/image.webp',
				],
			],
			'Partial webp URL' => [
				'params' => [
					'images/anime/15424.webp'
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
				]
			],
			'png placeholder image' => [
				'params' => [
					'images/placeholder.png',
				]
			],
			'jpeg2000' => [
				'params' => [
					'images/foo.jpf',
				]
			],
			'svg with png fallback and lots of attributes' => [
				'params' => [
					'images/example.svg',
					'png',
					[ 'width' => 200, 'height' => 300 ],
					[ 'alt' => 'Example text' ]
				]
			],
			'simple image with attributes' => [
				'params' => [
					'images/foo.jpg',
					'jpg',
					[ 'x' => 1, 'y' => 1 ],
					['width' => 200, 'height' => 200, 'alt' => 'should exist'],
				]
			]
		];
	}

	public function dataSimpleImageCase()
	{
		return [
			'apng' => [
				'ext' => 'apng',
				'isSimple' => FALSE,
			],
			'gif' => [
				'ext' => 'gif',
				'isSimple' => TRUE,
			],
			'jpg' => [
				'ext' => 'jpg',
				'isSimple' => TRUE,
			],
			'jpeg' => [
				'ext' => 'jpeg',
				'isSimple' => TRUE,
			],
			'png' => [
				'ext' => 'png',
				'isSimple' => TRUE,
			],
			'webp' => [
				'ext' => 'webp',
				'isSimple' => FALSE,
			],
		];
	}
}