<?php

use Aura\Html\HelperLocatorFactory;

use Aviat\Ion\Friend;
use Aviat\Ion\Di\Container;
use Aviat\AnimeClient\Helper;
use Aviat\AnimeClient\Config;
use Aviat\AnimeClient\MenuGenerator;

class MenuGeneratorTest extends AnimeClient_TestCase {

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
		$this->assertEquals($expected, $friend->parse_config($menus));
	}
}