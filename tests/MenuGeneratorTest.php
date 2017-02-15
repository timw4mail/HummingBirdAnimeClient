<?php

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\MenuGenerator;
use Aviat\Ion\Friend;

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