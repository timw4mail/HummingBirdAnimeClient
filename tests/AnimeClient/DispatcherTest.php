<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aura\Router\Route;
use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Dispatcher;
use Aviat\AnimeClient\UrlGenerator;
use Aviat\Ion\Config;
use Aviat\Ion\Di\ContainerInterface;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class DispatcherTest extends AnimeClientTestCase
{
	protected ContainerInterface $container;

	protected $router;

	protected $config;

	protected $urlGenerator;

	protected function doSetUp($config, $uri, $host): void
	{
		// Set up the environment
		$GLOBALS['_SERVER'] = array_merge($GLOBALS['_SERVER'], [
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => $uri,
			'PATH_INFO' => $uri,
			'HTTP_HOST' => $host,
			'SERVER_NAME' => $host,
		]);

		$this->setSuperGlobals([
			'_SERVER' => $GLOBALS['_SERVER'],
		]);

		$logger = new Logger('test_logger');
		$logger->pushHandler(new TestHandler(Logger::DEBUG));

		$this->container->setLogger($logger, 'default');

		if (! empty($config))
		{
			if (! array_key_exists('routes', $config))
			{
				$config['routes'] = [];
			}

			if (! array_key_exists('cache', $config))
			{
				$config['cache'] = [
					'driver' => 'null',
				];
			}

			if (! array_key_exists('asset_path', $config))
			{
				$config['asset_path'] = '/assets';
			}

			if (! array_key_exists('default_list', $config))
			{
				$config['default_list'] = 'anime';
			}

			if (! array_key_exists('default_anime_list_path', $config))
			{
				$config['default_anime_list_path'] = 'watching';
			}

			if (! array_key_exists('root', $config))
			{
				$config['root'] = self::ROOT_DIR;
			}

			if (! array_key_exists('view_path', $config))
			{
				$config['view_path'] = self::TEST_VIEW_DIR;
			}

			$config = new Config($config);
			$this->container->setInstance('config', $config);
		}

		$this->router = new Dispatcher($this->container);
		$this->config = $this->container->get('config');
		$this->urlGenerator = new UrlGenerator($this->container);
		$this->container->setInstance('url-generator', $this->urlGenerator);
	}

	public function testRouterSanity(): void
	{
		$this->doSetUp([], '/', 'localhost');
		$this->assertIsObject($this->router);
	}

	public static function dataRoute(): array
	{
		$defaultConfig = [
			'routes' => [
				'login_form' => [
					'path' => '/login',
					'action' => 'login',
					'verb' => 'get',
				],
				'watching' => [
					'path' => '/anime/watching{/view}',
					'action' => 'anime_list',
					'params' => [
						'type' => 'currently-watching',
					],
					'tokens' => [
						'view' => '[a-z_]+',
					],
				],
				'plan_to_read' => [
					'path' => '/manga/plan_to_read{/view}',
					'action' => 'manga_list',
					'params' => [
						'type' => 'Plan to Read',
					],
					'tokens' => [
						'view' => '[a-z_]+',
					],
				],
			],
			'config' => [
				'anime_path' => 'anime',
				'manga_path' => 'manga',
				'default_list' => 'anime',
			],
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
				'uri' => '/manga/plan_to_read',
			],
		];

		$data['manga_default_routing_anime']['config']['default_list'] = 'manga';
		$data['manga_default_routing_manga']['config']['default_list'] = 'manga';

		return $data;
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('dataRoute')]
	public function testRoute(mixed $config, mixed $controller, mixed $host, mixed $uri): void
	{
		$this->doSetUp($config, $uri, $host);

		$request = $this->container->get('request');

		// Check route setup
		$this->assertSame($config['routes'], $this->config->get('routes'), 'Incorrect route path');
		$this->assertIsArray($this->router->getOutputRoutes());

		// Check environment variables
		$this->assertSame($uri, $request->getServerParams()['REQUEST_URI']);
		$this->assertSame($host, $request->getServerParams()['HTTP_HOST']);

		// Make sure the route is an anime type
		//$this->assertTrue($matcher->count() > 0, '0 routes');
		$this->assertSame($controller, $this->router->getController(), 'Incorrect Route type');

		// Make sure the route matches, by checking that it is actually an object
		$route = $this->router->getRoute();
		$this->assertInstanceOf(Route::class, $route, 'Route is invalid, not matched');
	}

	public function testDefaultRoute(): void
	{
		$config = [
			'config' => [
				'anime_path' => 'anime',
				'manga_path' => 'manga',
				'default_anime_list_path' => 'watching',
				'default_manga_list_path' => 'all',
				'default_list' => 'manga',
			],
			'routes' => [
				'login_form' => [
					'path' => '/login',
					'action' => ['login'],
					'verb' => 'get',
				],
				'index' => [
					'path' => '/',
					'action' => ['redirect'],
					'params' => [
						'url' => '', // Determined by config
						'code' => '301',
						'type' => 'manga',
					],
				],
			],
		];

		$this->expectException(InvalidArgumentException::class);

		$this->doSetUp($config, '/', 'localhost');
		$this->assertSame(
			'https://localhost/manga/all',
			$this->urlGenerator->defaultUrl('manga'),
			'Incorrect default url',
		);
		$this->assertSame(
			'https://localhost/anime/watching',
			$this->urlGenerator->defaultUrl('anime'),
			'Incorrect default url',
		);

		$this->urlGenerator->defaultUrl('foo');
	}

	#[ArrayShape(['controller_list_sanity_check' => 'array', 'empty_controller_list' => 'array'])]
	public static function dataGetControllerList(): array
	{
		$expectedList = [
			'anime' => Controller\Anime::class,
			'anime-collection' => Controller\AnimeCollection::class,
			'character' => Controller\Character::class,
			'misc' => Controller\Misc::class,
			'manga' => Controller\Manga::class,
			'people' => Controller\People::class,
			'settings' => Controller\Settings::class,
			'user' => Controller\User::class,
			'images' => Controller\Images::class,
			'history' => Controller\History::class,
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
				'expected' => $expectedList,
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('dataGetControllerList')]
	public function testGetControllerList(array $config, array $expected): void
	{
		$this->doSetUp($config, '/', 'localhost');
		$this->assertEquals($expected, $this->router->getControllerList());
	}

	public function testSetupRoutesWithExplicitMissingController(): void
	{
		$config = [
			'routes' => [
				'test' => [
					'path' => '/test',
					'controller' => 'missing-controller',
					'action' => 'index',
				],
			],
			'config' => [
				'default_list' => 'anime',
			],
		];
		$this->doSetUp($config, '/test', 'localhost');

		$friend = new \Aviat\Ion\Friend($this->router);

		// The loop in setupRoutes iterates over $this->routes
		// We need to ensure it's set correctly
		$friend->routes = $config['routes'];
		$routes = $friend->setupRoutes();

		$this->assertEquals(\Aviat\AnimeClient\DEFAULT_CONTROLLER, $routes[0]->defaults['controller']);
	}

	public function testInvokeNoRoute(): void
	{
		$config = [
			'config' => [
				'default_list' => 'anime',
				'default_anime_list_path' => 'watching',
			],
		];
		$this->doSetUp($config, '/not-found', 'localhost');

		$mockController = new \Aviat\AnimeClient\Tests\MockInvokeController($this->container);
		$this->container->setSimple(
			\Aviat\AnimeClient\Controller\Misc::class,
			\Aviat\AnimeClient\Controller\Misc::class,
		);
		$this->container->setInstance(\Aviat\AnimeClient\Controller\Misc::class, $mockController);
		$this->container->setSimple('misc', \Aviat\AnimeClient\Controller\Misc::class);
		$this->container->setInstance('misc', $mockController);

		$matcher = $this->createMock(\Aura\Router\Matcher::class);
		$matcher->method('match')->willReturn(false);
		$matcher->method('getFailedRoute')->willReturn(null);

		$dispatcherFriend = new \Aviat\Ion\Friend($this->router);
		$dispatcherFriend->matcher = $matcher;
		$dispatcherFriend->container = $this->container;

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Bypass exit');

		$this->router->__invoke();
	}

	public function testGetErrorParamsNotFound(): void
	{
		$config = [
			'routes' => [],
			'config' => [
				'default_list' => 'anime',
			],
		];
		$this->doSetUp($config, '/not-found', 'localhost');

		$friend = new \Aviat\Ion\Friend($this->router);
		$errorParams = $friend->getErrorParams();

		$this->assertEquals(\Aviat\AnimeClient\NOT_FOUND_METHOD, $errorParams['action_method']);
	}

	public function testCallNotFound(): void
	{
		$this->doSetUp([], '/not-found', 'localhost');
		$friend = new \Aviat\Ion\Friend($this->router);

		$model = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Model::class);
		$this->container->setInstance('kitsu-model', $model);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Bypass exit');

		$friend->call(\Aviat\AnimeClient\Tests\MockInvokeController::class, 'notFound', []);
	}

	public function testGetRoute(): void
	{
		$this->doSetUp([], '/anime/watching', 'localhost');
		$route = $this->router->getRoute();
		$this->assertNotFalse($route);
	}

	public function testCallFailedResponseException(): void
	{
		$this->doSetUp([], '/', 'localhost');

		$friend = new \Aviat\Ion\Friend($this->router);
		$friend->container = $this->container;

		$this->container->setSimple(
			\Aviat\AnimeClient\Tests\MockErrorController::class,
			\Aviat\AnimeClient\Tests\MockErrorController::class,
		);

		ob_start();
		$friend->call(\Aviat\AnimeClient\Tests\MockErrorController::class, 'throwError', []);
		ob_get_clean();

		$this->assertTrue(true);
	}

	public function testGetErrorParams405(): void
	{
		$this->doSetUp([], '/', 'localhost');

		$matcher = $this->createMock(\Aura\Router\Matcher::class);

		// Use real Route object instead of mock to correctly test failedRule
		$route = new \Aura\Router\Route();
		$routeFriend = new \Aviat\Ion\Friend($route);
		$routeFriend->failedRule = \Aura\Router\Rule\Allows::class;

		$matcher->method('getFailedRoute')->willReturn($route);

		$friend = new \Aviat\Ion\Friend($this->router);
		$friend->matcher = $matcher;

		$errorParams = $friend->getErrorParams();
		$this->assertEquals(405, $errorParams['params']['http_code']);
		$this->assertEquals('errorPage', $errorParams['action_method']);
	}

	public function testGetErrorParams406(): void
	{
		$this->doSetUp([], '/', 'localhost');

		$matcher = $this->createMock(\Aura\Router\Matcher::class);

		// Use real Route object instead of mock to correctly test failedRule
		$route = new \Aura\Router\Route();
		$routeFriend = new \Aviat\Ion\Friend($route);
		$routeFriend->failedRule = \Aura\Router\Rule\Accepts::class;

		$matcher->method('getFailedRoute')->willReturn($route);

		$friend = new \Aviat\Ion\Friend($this->router);
		$friend->matcher = $matcher;

		$errorParams = $friend->getErrorParams();
		$this->assertEquals(406, $errorParams['params']['http_code']);
		$this->assertEquals('errorPage', $errorParams['action_method']);
	}

	public function testGetOutputRoutes(): void
	{
		$this->doSetUp(
			$this->dataRoute()['anime_default_routing_anime']['config'],
			'/anime/watching',
			'localhost',
		);
		$routes = $this->router->getOutputRoutes();
		$this->assertNotEmpty($routes);
	}

	public function testGetControllerAnime(): void
	{
		$this->doSetUp(['config' => ['default_list' => 'anime']], '/anime/watching', 'localhost');
		$this->assertEquals('anime', $this->router->getController());
	}

	public function testGetControllerManga(): void
	{
		$this->doSetUp(['config' => ['default_list' => 'manga']], '/manga/reading', 'localhost');
		$this->assertEquals('manga', $this->router->getController());
	}

	public function testGetControllerDefault(): void
	{
		$this->doSetUp(['config' => ['default_list' => 'anime']], '/', 'localhost');
		$this->assertEquals('anime', $this->router->getController());
	}

	public function testProcessRouteWithTokens(): void
	{
		$config = [
			'routes' => [
				'test' => [
					'path' => '/test/{id}',
					'controller' => 'anime',
					'action' => 'details',
					'tokens' => [
						'id' => '[0-9]+',
					],
				],
			],
			'config' => [
				'default_list' => 'anime',
			],
		];
		$this->doSetUp($config, '/test/123', 'localhost');

		$route = $this->router->getRoute();
		$this->assertNotFalse($route);

		$friend = new \Aviat\Ion\Friend($this->router);
		$parsed = $friend->processRoute(new \Aviat\Ion\Friend($route));

		$this->assertEquals('123', $parsed['params']['id']);
	}

	public function testProcessRouteMissingController(): void
	{
		$this->expectException(\LogicException::class);
		$this->doSetUp([], '/', 'localhost');
		$friend = new \Aviat\Ion\Friend($this->router);
		$route = $this->createMock(Route::class);
		$routeFriend = new \Aviat\Ion\Friend($route);
		$routeFriend->attributes = [];
		$friend->processRoute($routeFriend);
	}
}

/**
 * Mock controller for testing FailedResponseException
 */
class MockErrorController extends \Aviat\AnimeClient\Controller\Misc
{
	public function throwError(): void
	{
		throw new \Aviat\AnimeClient\API\FailedResponseException();
	}

	public function notFound(
		string $title = 'Sorry, page not found',
		string $message = 'Page Not Found',
	): never {
		throw new \RuntimeException('Bypass exit');
	}
}

class MockInvokeController extends \Aviat\AnimeClient\Controller\Misc
{
	public bool $redirected = false;

	#[\Override]
	public function redirectToDefaultRoute(): void
	{
		$this->redirected = true;
	}

	#[\Override]
	public function notFound(
		string $title = 'Sorry, page not found',
		string $message = 'Page Not Found',
	): never {
		throw new \RuntimeException('Bypass exit');
	}
}
