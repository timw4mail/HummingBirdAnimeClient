<?php

use AnimeClient\Base\Router;
use AnimeClient\Base\Config;
use AnimeClient\Base\Container;
use AnimeClient\Base\UrlGenerator;
use Aura\Web\WebFactory;
use Aura\Router\RouterFactory;

class RouterTest extends AnimeClient_TestCase {

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

		// Add the appropriate objects to the container
		$this->container = new Container([
			'config' => new Config($config),
			'request' => $web_factory->newRequest(),
			'response' => $web_factory->newResponse(),
			'aura-router' => $router_factory->newInstance(),
			'error-handler' => new MockErrorHandler()
		]);

		$this->router = new Router($this->container);
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
				'common' => [
					'login_form' => [
						'path' => '/login',
						'action' => ['login'],
						'verb' => 'get'
					],
				],
				'anime' => [
					'watching' => [
						'path' => '/anime/watching{/view}',
						'action' => ['anime_list'],
						'params' => [
							'type' => 'currently-watching',
							'title' => WHOSE . " Anime List &middot; Watching"
						],
						'tokens' => [
							'view' => '[a-z_]+'
						]
					],
				],
				'manga' => [
					'plan_to_read' => [
						'path' => '/manga/plan_to_read{/view}',
						'action' => ['manga_list'],
						'params' => [
							'type' => 'Plan to Read',
							'title' => WHOSE . " Manga List &middot; Plan to Read"
						],
						'tokens' => [
							'view' => '[a-z_]+'
						]
					],
				]
			],
			'routing' => [
				'anime_path' => 'anime',
				'manga_path' => 'manga',
				'default_list' => 'anime'
			]
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

		$data['manga_default_routing_anime']['config']['routing']['default_list'] = 'manga';
		$data['manga_default_routing_manga']['config']['routing']['default_list'] = 'manga';

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
		$this->assertEquals($config['routes'], $this->config->routes, "Incorrect route path");
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
			'routing' => [
				'anime_path' => 'anime',
				'manga_path' => 'manga',
				'default_anime_path' => "/anime/watching",
				'default_manga_path' => '/manga/all',
				'default_list' => 'manga'
			],
			'routes' => [
				'common' => [
					'login_form' => [
						'path' => '/login',
						'action' => ['login'],
						'verb' => 'get'
					],
				],
				'anime' => [
					'index' => [
						'path' => '/',
						'action' => ['redirect'],
						'params' => [
							'url' => '', // Determined by config
							'code' => '301'
						]
					],
				],
				'manga' => [
					'index' => [
						'path' => '/',
						'action' => ['redirect'],
						'params' => [
							'url' => '', // Determined by config
							'code' => '301',
							'type' => 'manga'
						]
					],
				]
			]
		];

		$this->_set_up($config, "/", "localhost");
		$this->assertEquals('//localhost/manga/all', $this->urlGenerator->default_url('manga'), "Incorrect default url");
		$this->assertEquals('//localhost/anime/watching', $this->urlGenerator->default_url('anime'), "Incorrect default url");
		$this->assertEquals('', $this->urlGenerator->default_url('foo'), "Incorrect default url");
	}
}