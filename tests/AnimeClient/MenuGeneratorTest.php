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

use Aviat\AnimeClient\MenuGenerator;
use Aviat\Ion\Friend;

/**
 * @internal
 */
final class MenuGeneratorTest extends AnimeClientTestCase
{
	protected $generator;
	protected $friend;

	protected function setUp(): void
	{
		parent::setUp();
		$this->generator = MenuGenerator::new($this->container);
	}

	public function testSanity()
	{
		$generator = MenuGenerator::new($this->container);
		$this->assertInstanceOf(MenuGenerator::class, $generator);
	}

	public function testParseConfig()
	{
		$friend = new Friend($this->generator);
		$menus = [
			'anime_list' => [
				'route_prefix' => '/anime',
				'items' => [
					'watching' => '/watching',
					'plan_to_watch' => '/plan_to_watch',
					'on_hold' => '/on_hold',
					'dropped' => '/dropped',
					'completed' => '/completed',
					'all' => '/all',
				],
			],
		];
		$expected = [
			'anime_list' => [
				'Watching' => '/anime/watching',
				'Plan To Watch' => '/anime/plan_to_watch',
				'On Hold' => '/anime/on_hold',
				'Dropped' => '/anime/dropped',
				'Completed' => '/anime/completed',
				'All' => '/anime/all',
			],
		];
		$this->assertSame($expected, $friend->parseConfig($menus));
	}

	public function testBadConfig()
	{
		$menus = [
			'anime_list' => [
				'route_prefix' => '/anime',
				'items' => [
					'watching' => '/watching',
					'plan_to_watch' => '/plan_to_watch',
					'on_hold' => '/on_hold',
					'dropped' => '/dropped',
					'completed' => '/completed',
					'all' => '/all',
				],
			],
		];
		$config = $this->container->get('config');
		$config->set('menus', $menus);

		$this->container->setInstance('config', $config);
		$expected = '';

		$this->assertSame($expected, $this->generator->generate('manga_list'));
	}
}
