<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Dispatcher;
use Aviat\AnimeClient\UrlGenerator;
use Aviat\Ion\Config;
use Monolog\Handler\TestHandler;
use Monolog\Logger;


class DispatcherTest extends AnimeClientTestCase {

	protected $container;
	protected $router;
	protected $config;
	protected $urlGenerator;

	protected function doSetUp($config, $uri, $host)
	{
		// Set up the environment
		$_SERVER = array_merge($_SERVER, [
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => $uri,
			'PATH_INFO' => $uri,
			'HTTP_HOST' => $host,
			'SERVER_NAME' => $host
		]);

		$this->setSuperGlobals([
			'_SERVER' => $_SERVER
		]);

		$logger = new Logger('test_logger');
		$logger->pushHandler(new TestHandler(Logger::DEBUG));

		$this->container->setLogger($logger, 'default');

		if ( ! empty($config))
		{
			$config = new Config($config);
			$this->container->setInstance('config', $config);
		}

		$this->router = new Dispatcher($this->container);
		$this->config = $this->container->get('config');
		$this->urlGenerator = new UrlGenerator($this->container);
		$this->container->setInstance('url-generator', $this->urlGenerator);
	}

	public function testRouterSanity()
	{
		$this->doSetUp([], '/', 'localhost');
		$this->assertInternalType('object', $this->router);
	}

	public function dataRoute()
	{
		$defaultConfig = [
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
			'config' => [
				'anime_path' => 'anime',
				'manga_path' => 'manga',
				'default_list' => 'anime'
			]
		];

		$data = [
			'anime_default_routing_manga' => [
				'config' => $defaultConfig,
				'controller' => 'manga',
				'host' => 'localhost',
				'uri' => '/manga/plan_to_read',
			],
			'manga_default_routing_anime' => [
				'config' => $defaultConfig,
				'controller' => 'anime',
				'host' => 'localhost',
				'uri' => '/anime/watching',
			],
			'anime_default_routing_anime' => [
				'config' => $defaultConfig,
				'controller' => 'anime',
				'host' => 'localhost',
				'uri' => '/anime/watching',
			],
			'manga_default_routing_manga' => [
				'config' => $defaultConfig,
				'controller' => 'manga',
				'host' => 'localhost',
				'uri' => '/manga/plan_to_read'
			]
		];

		$data['manga_default_routing_anime']['config']['default_list'] = 'manga';
		$data['manga_default_routing_manga']['config']['default_list'] = 'manga';

		return $data;
	}

	/**
	 * @dataProvider dataRoute
	 */
	public function testRoute($config, $controller, $host, $uri)
	{
		$this->doSetUp($config, $uri, $host);

		$request = $this->container->get('request');

		// Check route setup
		$this->assertEquals($config['routes'], $this->config->get('routes'), 'Incorrect route path');
		$this->assertInternalType('array', $this->router->getOutputRoutes());

		// Check environment variables
		$this->assertEquals($uri, $request->getServerParams()['REQUEST_URI']);
		$this->assertEquals($host, $request->getServerParams()['HTTP_HOST']);

		// Make sure the route is an anime type
		//$this->assertTrue($matcher->count() > 0, '0 routes');
		$this->assertEquals($controller, $this->router->getController(), 'Incorrect Route type');

		// Make sure the route matches, by checking that it is actually an object
		$route = $this->router->getRoute();
		$this->assertInstanceOf(\Aura\Router\Route::class, $route, 'Route is invalid, not matched');
	}

	public function testDefaultRoute()
	{
		$config = [
			'config' => [
				'anime_path' => 'anime',
				'manga_path' => 'manga',
				'default_anime_list_path' => 'watching',
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
						'code' => '301',
						'type' => 'manga'
					]
				]
			]
		];

		$this->expectException(\InvalidArgumentException::class);

		$this->doSetUp($config, '/', 'localhost');
		$this->assertEquals('//localhost/manga/all', $this->urlGenerator->defaultUrl('manga'), 'Incorrect default url');
		$this->assertEquals('//localhost/anime/watching', $this->urlGenerator->defaultUrl('anime'), 'Incorrect default url');

		$this->urlGenerator->defaultUrl('foo');
	}

	public function dataGetControllerList()
	{
		$expectedList = [
			'anime' => Controller\Anime::class,
			'anime-collection' => Controller\AnimeCollection::class,
			'character' => Controller\Character::class,
			'misc' => Controller\Misc::class,
			'manga' => Controller\Manga::class,
			'manga-collection' => Controller\MangaCollection::class,
			'people' => Controller\People::class,
			'settings' => Controller\Settings::class,
			'user' => Controller\User::class,
			'images' => Controller\Images::class,
		];

		return [
			'controller_list_sanity_check' => [
				'config' => [
					'anime_path' => 'anime',
					'manga_path' => 'manga',
					'default_anime_list_path' => 'watching',
					'default_manga_list_path' => 'all',
					'default_list' => 'manga',
					'routes' => [],
				],
				'expected' => $expectedList,
			],
			'empty_controller_list' => [
				'config' => [
					'anime_path' => 'anime',
					'manga_path' => 'manga',
					'default_anime_path' => '/anime/watching',
					'default_manga_path' => '/manga/all',
					'default_list' => 'manga',
					'routes' => [],
				],
				'expected' => $expectedList
			]
		];
	}

	/**
	 * @dataProvider dataGetControllerList
	 */
	public function testGetControllerList($config, $expected)
	{
		$this->doSetUp($config, '/', 'localhost');
		$this->assertEquals($expected, $this->router->getControllerList());
	}
}