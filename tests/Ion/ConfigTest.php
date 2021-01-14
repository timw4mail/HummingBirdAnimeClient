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
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

use Aviat\Ion\Config;

class ConfigTest extends IonTestCase {

	protected $config;

	public function setUp(): void
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
		$this->assertEquals('bar', $this->config->get('foo'));
		$this->assertEquals('baz', $this->config->get('bar'));
		$this->assertNull($this->config->get('baz'));
		$this->assertNull($this->config->get(['apple', 'sauce', 'is']));
	}

	public function testConfigSet(): void
	{
		$ret = $this->config->set('foo', 'foobar');
		$this->assertInstanceOf(Config::class, $ret);
		$this->assertEquals('foobar', $this->config->get('foo'));

		$this->config->set(['apple', 'sauce', 'is'], 'great');
		$apple = $this->config->get('apple');
		$this->assertEquals('great', $apple['sauce']['is'], 'Config value not set correctly');

		$this->assertEquals('great', $this->config->get(['apple', 'sauce', 'is']), "Array argument get for config failed.");
	}

	public function testConfigBadSet(): void
	{
		$this->expectException('InvalidArgumentException');
		$this->config->set(NULL, FALSE);
	}

	public function dataConfigDelete(): array
	{
		return [
			'top level delete' => [
				'key' => 'apple',
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => NULL
					],
					[
						'path' => 'apple',
						'expected' => NULL
					]
				]
			],
			'mid level delete' => [
				'key' => ['apple', 'sauce'],
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => NULL
					],
					[
						'path' => 'apple',
						'expected' => [
							'sauce' => NULL
						]
					]
				]
			],
			'deep delete' => [
				'key' => ['apple', 'sauce', 'is'],
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => [
							'is' => NULL
						]
					]
				]
			]
		];
	}

	/**
	 * @dataProvider dataConfigDelete
	 */
	public function testConfigDelete($key, array $assertKeys): void
	{
		$config = new Config([]);
		$config->set(['apple', 'sauce', 'is'], 'great');
		$config->delete($key);

		foreach($assertKeys as $pair)
		{
			$this->assertEquals($pair['expected'], $config->get($pair['path']));
		}
	}

	public function testGetNonExistentConfigItem(): void
	{
		$this->assertNull($this->config->get('foobar'));
	}
}