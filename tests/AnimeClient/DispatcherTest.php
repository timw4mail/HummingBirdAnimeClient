<?php

use Aura\Web\WebFactory;
use Aura\Router\RouterFactory;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

use Aviat\Ion\Di\Container;
use Aviat\AnimeClient\Dispatcher;
use Aviat\AnimeClient\Config;
use Aviat\AnimeClient\UrlGenerator;


class DispatcherTest extends AnimeClient_TestCase {

	protected $container;
	protected $router;
	protected $config;

	protected function _set_up($config, $uri, $host)
	{
		// Set up the environment
		$_SERVER = array_merge($_SERVER, [
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => $uri,
			'PATH_INFO' => $uri,
			'HTTP_HOST' => $host,
			'SERVER_NAME' => $host
		]);

		$router_factory = new RouterFactory();
		$web_factory = new WebFactory([
			'_GET' => [],
			'_POST' => [],
			'_COOKIE' => [],
			'_SERVER' => $_SERVER,
			'_FILES' => []
		]);

		$old_config = $this->container->get('config');

		$logger = new Logger('test_logger');
		$logger->pushHandler(new TestHandler(Logger::DEBUG));

		// Add the appropriate objects to the container
		$this->container = new Container([
			'config' => $old_config,
			'request' => $web_factory->newRequest(),
			'response' => $web_factory->newResponse(),
			'aura-router' => $router_factory->newInstance()
		]);

		$this->container->setLogger($logger, 'default');

		if ( ! empty($config))
		{
			$config = new Config($config);
			$this->container->set('config', $config);
		}

		$this->router = new Dispatcher($this->container);
		$this->config = $this->container->get('config');
		$this->urlGenerator = new UrlGenerator($this->container);
		$this->container->set('url-generator', $this->urlGenerator);
	}

	public function testRouterSanity()
	{
		$this->_set_up([], '/', 'localhost');
		$this->assertTrue(is_object($this->router));
	}

	public function dataRoute()
	{
		$default_config = array(
			'routes' => [
				'routes' => [
					'login_form' => [
						'path' => '/login',
						'action' => 'login',
						'verb' => 'get'
					],
					'watching' => [
						'path' => '/anime/watching{/view}',
						'action' => 'anime_list',
						'params' => [
							'type' => 'currently-watching',
						],
						'tokens' => [
							'view' => '[a-z_]+'
						]
					],
					'plan_to_read' => [
						'path' => '/manga/plan_to_read{/view}',
						'action' => 'manga_list',
						'params' => [
							'type' => 'Plan to Read',
						],
						'tokens' => [
							'view' => '[a-z_]+'
						]
					],
				],
				'route_config' => [
					'anime_path' => 'anime',
					'manga_path' => 'manga',
					'default_list' => 'anime'
				]
			],
		);

		$data = [
			'anime_default_routing_manga' => array(
				'config' => $default_config,
				'controller' => 'manga',
				'host' => "localhost",
				'uri' => "/manga/plan_to_read",
			),
			'manga_default_routing_anime' => array(
				'config' => $default_config,
				'controller' => 'anime',
				'host' => "localhost",
				'uri' => "/anime/watching",
			),
			'anime_default_routing_anime' => array(
				'config' => $default_config,
				'controller' => 'anime',
				'host' => 'localhost',
				'uri' => '/anime/watching',
			),
			'manga_default_routing_manga' => array(
				'config' => $default_config,
				'controller' => 'manga',
				'host' => 'localhost',
				'uri' => '/manga/plan_to_read'
			)
		];

		$data['manga_default_routing_anime']['config']['routes']['route_config']['default_list'] = 'manga';
		$data['manga_default_routing_manga']['config']['routes']['route_config']['default_list'] = 'manga';

		return $data;
	}

	/**
	 * @dataProvider dataRoute
	 */
	public function testRoute($config, $controller, $host, $uri)
	{
		$this->_set_up($config, $uri, $host);

		$request = $this->container->get('request');
		$aura_router = $this->container->get('aura-router');


		// Check route setup
		$this->assertEquals($config['routes'], $this->config->get('routes'), "Incorrect route path");
		$this->assertTrue(is_array($this->router->get_output_routes()));

		// Check environment variables
		$this->assertEquals($uri, $request->server->get('REQUEST_URI'));
		$this->assertEquals($host, $request->server->get('HTTP_HOST'));

		// Make sure the route is an anime type
		$this->assertTrue($aura_router->count() > 0, "0 routes");
		$this->assertEquals($controller, $this->router->get_controller(), "Incorrect Route type");

		// Make sure the route matches, by checking that it is actually an object
		$route = $this->router->get_route();
		$this->assertInstanceOf('Aura\\Router\\Route', $route, "Route is invalid, not matched");
	}

	public function testDefaultRoute()
	{
		$config = [
			'routes' => [
				'route_config' => [
					'anime_path' => 'anime',
					'manga_path' => 'manga',
					'default_anime_list_path' => "watching",
					'default_manga_list_path' => 'all',
					'default_list' => 'manga'
				],
				'routes' => [
					'login_form' => [
						'path' => '/login',
						'action' => ['login'],
						'verb' => 'get'
					],
					'index' => [
						'path' => '/',
						'action' => ['redirect'],
						'params' => [
							'url' => '', // Determined by config
							'code' => '301'
						]
					],
					'index' => [
						'path' => '/',
						'action' => ['redirect'],
						'params' => [
							'url' => '', // Determined by config
							'code' => '301',
							'type' => 'manga'
						]
					]
				]
			]
		];

		$this->_set_up($config, "/", "localhost");
		$this->assertEquals('//localhost/manga/all', $this->urlGenerator->default_url('manga'), "Incorrect default url");
		$this->assertEquals('//localhost/anime/watching', $this->urlGenerator->default_url('anime'), "Incorrect default url");

		$this->setExpectedException('\InvalidArgumentException');
		$this->urlGenerator->default_url('foo');
	}

	public function dataGetControllerList()
	{
		return array(
			'controller_list_sanity_check' => [
				'config' => [
					'routes' => [
						'routes' => [

						],
						'route_config' => [
							'anime_path' => 'anime',
							'manga_path' => 'manga',
							'default_anime_list_path' => "watching",
							'default_manga_list_path' => 'all',
							'default_list' => 'manga'
						],
					]
				],
				'expected' => [
					'anime' => 'Aviat\AnimeClient\Controller\Anime',
					'manga' => 'Aviat\AnimeClient\Controller\Manga',
					'collection' => 'Aviat\AnimeClient\Controller\Collection',
				]
			],
			'empty_controller_list' => [
				'config' => [
					'routes' => [
						'routes' => [

						],
						'route_config' => [
							'anime_path' => 'anime',
							'manga_path' => 'manga',
							'default_anime_path' => "/anime/watching",
							'default_manga_path' => '/manga/all',
							'default_list' => 'manga'
						],
					]
				],
				'expected' => [
					'anime' => 'Aviat\AnimeClient\Controller\Anime',
					'manga' => 'Aviat\AnimeClient\Controller\Manga',
					'collection' => 'Aviat\AnimeClient\Controller\Collection',
				]
			]
		);
	}

	/**
	 * @dataProvider dataGetControllerList
	 */
	public function testGetControllerList($config, $expected)
	{
		$this->_set_up($config, '/', 'localhost');
		$this->assertEquals($expected, $this->router->get_controller_list());
	}
}