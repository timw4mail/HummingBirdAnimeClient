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

namespace Aviat\Ion\Tests;

use Aviat\Ion\Config;
use PHPUnit\Framework\Attributes\{DataProvider, IgnoreMethodForCodeCoverage};

/**
 * @internal
 */
#[IgnoreMethodForCodeCoverage(Config::class, 'set')]
final class ConfigTest extends IonTestCase
{
	protected Config $config;

	protected function setUp(): void
	{
		$this->config = new Config([
			'foo' => 'bar',
			'asset_path' => '/assets',
			'bar' => 'baz',
			'a' => [
				'b' => [
					'c' => TRUE,
				],
			],
		]);
	}

	public function testConfigHas(): void
	{
		$this->assertTrue($this->config->has('foo'));
		$this->assertTrue($this->config->has(['a', 'b', 'c']));

		$this->assertFalse($this->config->has('baz'));
		$this->assertFalse($this->config->has(['c', 'b', 'a']));
	}

	public function testConfigGet(): void
	{
		$this->assertSame('bar', $this->config->get('foo'));
		$this->assertSame('baz', $this->config->get('bar'));
		$this->assertNull($this->config->get('baz'));
		$this->assertNull($this->config->get(['apple', 'sauce', 'is']));
	}

	public function testConfigSet(): void
	{
		$ret = $this->config->set('foo', 'foobar');
		$this->assertInstanceOf(Config::class, $ret);
		$this->assertSame('foobar', $this->config->get('foo'));

		$this->config->set(['apple', 'sauce', 'is'], 'great');
		$apple = $this->config->get('apple');
		$this->assertSame('great', $apple['sauce']['is'], 'Config value not set correctly');

		$this->assertSame('great', $this->config->get(['apple', 'sauce', 'is']), 'Array argument get for config failed.');
	}

	public static function dataConfigDelete(): array
	{
		return [
			'top level delete' => [
				'key' => 'apple',
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL,
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => NULL,
					],
					[
						'path' => 'apple',
						'expected' => NULL,
					],
				],
			],
			'mid level delete' => [
				'key' => ['apple', 'sauce'],
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL,
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => NULL,
					],
					[
						'path' => 'apple',
						'expected' => [
							'sauce' => NULL,
						],
					],
				],
			],
			'deep delete' => [
				'key' => ['apple', 'sauce', 'is'],
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL,
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => [
							'is' => NULL,
						],
					],
				],
			],
		];
	}

	#[DataProvider('dataConfigDelete')]
	public function testConfigDelete(string|array $key, array $assertKeys): void
	{
		$config = new Config([]);
		$config->set(['apple', 'sauce', 'is'], 'great');
		$config->delete($key);

		foreach ($assertKeys as $pair)
		{
			$this->assertSame($pair['expected'], $config->get($pair['path']));
		}
	}

	public function testGetNonExistentConfigItem(): void
	{
		$this->assertNull($this->config->get('foobar'));
	}
}
