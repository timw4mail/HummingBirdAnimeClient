<?php

use AnimeClient\Router;
use AnimeClient\Config;
use Aura\Web\WebFactory;
use Aura\Router\RouterFactory;

class RouterTest extends AnimeClient_TestCase {

	protected $aura_router;
	protected $request;
	protected $response;
	protected $router;

	public function testRouterSanity()
	{
		parent::setUp();

		$router_factory = new RouterFactory();
		$this->aura_router = $router_factory->newInstance();

		// Create Request/Response Objects
		$web_factory = new WebFactory([
			'_GET' => [],
			'_POST' => [],
			'_COOKIE' => [],
			'_SERVER' => $_SERVER,
			'_FILES' => []
		]);
		$this->request = $web_factory->newRequest();
		$this->response = $web_factory->newResponse();
		$this->router = new Router($this->config, $this->aura_router, $this->request, $this->response);

		$this->assertTrue(is_object($this->router));
	}

	protected function _set_up($config, $uri, $host)
	{
		$this->config = new Config($config);

		// Set up the environment
		$_SERVER = array_merge($_SERVER, [
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => $uri,
			'HTTP_HOST' => $host,
			'SERVER_NAME' => $host
		]);

		$router_factory = new RouterFactory();
		$this->aura_router = $router_factory->newInstance();

		// Create Request/Response Objects
		$web_factory = new WebFactory([
			'_GET' => [],
			'_POST' => [],
			'_COOKIE' => [],
			'_SERVER' => $_SERVER,
			'_FILES' => []
		]);
		$this->request = $web_factory->newRequest();
		$this->response = $web_factory->newResponse();
		$this->router = new Router($this->config, $this->aura_router, $this->request, $this->response);
	}

	public function RouteTestProvider()
	{
		return [
			'manga_path_routing' => array(
				'config' => array(
					'config' => [
						'anime_host' => '',
						'manga_host' => '',
						'anime_path' => 'anime',
						'manga_path' => 'manga',
						'route_by' => 'path',
						'default_list' => 'anime'
					],
					'base_config' => []
				),
				'type' => 'manga',
				'host' => "localhost",
				'uri' => "/manga/plan_to_read",
				'check_var' => 'manga_path'
			),
			'manga_host_routing' => array(
				'config' => array(
					'config' => [
						'anime_host' => 'anime.host.me',
						'manga_host' => 'manga.host.me',
						'anime_path' => '',
						'manga_path' => '',
						'route_by' => 'host',
						'default_list' => 'anime'
					],
					'base_config' => []
				),
				'type' => 'manga',
				'host' => 'manga.host.me',
				'uri' => '/plan_to_read',
				'check_var' => 'manga_host'
			),
			'anime_path_routing' => array(
				'config' => array(
					'config' => [
						'anime_host' => '',
						'manga_host' => '',
						'anime_path' => 'anime',
						'manga_path' => 'manga',
						'route_by' => 'path',
						'default_list' => 'manga'
					],
					'base_config' => [
						'routes' => []
					]
				),
				'type' => 'anime',
				'host' => "localhost",
				'uri' => "/anime/watching",
				'check_var' => 'anime_path'
			),
			'anime_host_routing' => array(
				'config' => array(
					'config' => [
						'anime_host' => 'anime.host.me',
						'manga_host' => 'manga.host.me',
						'anime_path' => '',
						'manga_path' => '',
						'route_by' => 'host',
						'default_list' => 'manga'
					],
					'base_config' => []
				),
				'type' => 'anime',
				'host' => 'anime.host.me',
				'uri' => '/watching',
				'check_var' => 'anime_host'
			),
		];
	}

	/**
	 * @dataProvider RouteTestProvider
	 */
	public function testRoute($config, $type, $host, $uri, $check_var)
	{
		$config['base_config']['routes'] = [
			'common' => [
				'login_form' => [
					'path' => '/login',
					'action' => ['login'],
					'verb' => 'get'
				],
			],
			'anime' => [
				'watching' => [
					'path' => '/watching{/view}',
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
					'path' => '/plan_to_read{/view}',
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
		];

		$this->_set_up($config, $uri, $host);

		// Check route setup
		$this->assertEquals($config['base_config']['routes'], $this->config->routes);
		$this->assertTrue(is_array($this->router->get_output_routes()));

		// Check environment variables
		$this->assertEquals($uri, $this->request->server->get('REQUEST_URI'));
		$this->assertEquals($host, $this->request->server->get('HTTP_HOST'));

		// Make sure the route is an anime type
		$this->assertTrue($this->aura_router->count() > 0, "More than 0 routes");
		$this->assertTrue($this->config->$check_var !== '', "Check variable is not empty");
		$this->assertEquals($type, $this->router->get_route_type(), "Correct Route type");

		// Make sure the route matches, by checking that it is actually an object
		$route = $this->router->get_route();
		$this->assertInstanceOf('Aura\\Router\\Route', $route, "Route is valid, and matched");
	}

	/*public function testDefaultRoute()
	{
		$config = [
			'config' => [
				'anime_host' => '',
				'manga_host' => '',
				'anime_path' => 'anime',
				'manga_path' => 'manga',
				'route_by' => 'host',
				'default_list' => 'manga'
			],
			'base_config' => [
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
			]
		];

		$this->_set_up($config, "/", "localhost");
		$this->assertEquals($this->config->full_url('', 'manga'), $this->response->headers->get('location'));
	}*/
}