<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\MenuGenerator;
use Aviat\Ion\Friend;

class MenuGeneratorTest extends AnimeClientTestCase {

	protected $generator;
	protected $friend;

	public function setUp()
	{
		parent::setUp();
		$this->generator = new MenuGenerator($this->container);
	}

	public function testSanity()
	{
		$generator = new MenuGenerator($this->container);
		$this->assertInstanceOf('Aviat\AnimeClient\MenuGenerator', $generator);
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
					'all' => '/all'
				]
			],
		];
		$expected = [
			'anime_list' => [
				'Watching' => '/anime/watching',
				'Plan To Watch' => '/anime/plan_to_watch',
				'On Hold' => '/anime/on_hold',
				'Dropped' => '/anime/dropped',
				'Completed' => '/anime/completed',
				'All' => '/anime/all'
			]
		];
		$this->assertEquals($expected, $friend->parseConfig($menus));
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
					'all' => '/all'
				]
			],
		];
		$config = $this->container->get('config');
		$config->set('menus', $menus);
		$this->container->setInstance('config', $config);
		$expected = '';

		$this->assertEquals($expected, $this->generator->generate('manga_list'));
	}
}